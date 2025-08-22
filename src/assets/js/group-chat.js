/**
 * Group Chat JavaScript functionality
 * Handles group creation, management, and real-time updates
 */

class GroupChat {
    constructor() {
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Create group form submission
        document.getElementById('createGroupForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.createGroup();
        });

        // Create group button click
        document.querySelector('.create-group-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showCreateGroupModal();
        });

        // Group list item clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-contact^="group_"]')) {
                const groupId = e.target.closest('[data-contact]').getAttribute('data-contact').replace('group_', '');
                this.openGroupChat(groupId);
            }
        });
    }

    showCreateGroupModal() {
        // Show the create group modal using the existing modal system
        if (typeof showModal === 'function') {
            showModal('createGroup');
        } else {
            // Fallback to jQuery if available
            if (typeof $ !== 'undefined') {
                $('#createGroupModal').modal('show');
            }
        }
    }

    async createGroup() {
        const form = document.getElementById('createGroupForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('/devschat/createGroup', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                this.showAlert('Group created successfully!', 'success');
                
                // Close modal
                this.hideCreateGroupModal();
                
                // Refresh the page to show the new group
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showAlert(result.message || 'Failed to create group', 'error');
            }
        } catch (error) {
            console.error('Error creating group:', error);
            this.showAlert('An error occurred while creating the group', 'error');
        }
    }

    hideCreateGroupModal() {
        if (typeof hideModal === 'function') {
            hideModal('createGroup');
        } else if (typeof $ !== 'undefined') {
            $('#createGroupModal').modal('hide');
        }
    }

    async openGroupChat(groupId) {
        try {
            // Fetch group info
            const response = await fetch('/devschat/getGroupInfo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ group_id: groupId })
            });

            const result = await response.json();

            if (result.success) {
                // Update the chat interface to show group chat
                this.updateChatInterface(result.group);
                
                // Switch to messaging view
                this.switchToMessagingView();
                
                // Load group messages
                this.loadGroupMessages(groupId);
            } else {
                this.showAlert(result.message || 'Failed to load group info', 'error');
            }
        } catch (error) {
            console.error('Error opening group chat:', error);
            this.showAlert('An error occurred while opening the group chat', 'error');
        }
    }

    updateChatInterface(group) {
        // Update header with group info
        const headerAvatar = document.querySelector('.header-avatar');
        const userName = document.querySelector('.user-name');
        
        if (headerAvatar) {
            headerAvatar.style.backgroundImage = `url('${group.avatar_url}')`;
        }
        
        if (userName) {
            userName.textContent = group.name;
        }

        // Store group info for later use
        window.currentGroup = group;
    }

    switchToMessagingView() {
        // Hide list view and show messaging view
        const listView = document.querySelector('.messenger-listView');
        const messagingView = document.querySelector('.messenger-messagingView');
        
        if (listView && messagingView) {
            listView.classList.remove('show');
            messagingView.classList.add('show');
        }
    }

    async loadGroupMessages(groupId) {
        try {
            const response = await fetch('/devschat/fetchMessages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ 
                    id: groupId,
                    type: 'group'
                })
            });

            const result = await response.json();
            
            if (result.messages) {
                this.displayMessages(result.messages);
            }
        } catch (error) {
            console.error('Error loading group messages:', error);
        }
    }

    displayMessages(messages) {
        const messagesContainer = document.querySelector('.messages');
        if (!messagesContainer) return;

        messagesContainer.innerHTML = '';
        
        messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            messagesContainer.appendChild(messageElement);
        });

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-card ${message.from_id == window.currentUser?.id ? 'mc-sender' : 'mc-default'}`;
        
        const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.innerHTML = `
            <div class="message-card-content">
                <div class="message-card-content-text">
                    ${message.body}
                </div>
                <div class="message-card-content-time">
                    ${time}
                </div>
            </div>
        `;
        
        return messageDiv;
    }

    showAlert(message, type = 'info') {
        // Use existing alert system if available
        if (typeof showAlert === 'function') {
            showAlert(message, type);
        } else {
            // Fallback alert
            alert(message);
        }
    }
}

// Initialize group chat functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.groupChat = new GroupChat();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GroupChat;
}
