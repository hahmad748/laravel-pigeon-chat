/**
 * Group Chat JavaScript functionality
 * Handles group creation, management, and real-time updates
 */

class GroupChat {
    constructor() {
        this.initializeEventListeners();
        this.selectedMembers = new Set();
    }

    initializeEventListeners() {
        console.log('Initializing event listeners...'); // Debug log
        
        // Check if jQuery is available
        if (typeof $ !== 'undefined') {
            console.log('jQuery is available'); // Debug log
            
            // Try jQuery event binding as well
            $('#createGroupForm').on('submit', (e) => {
                e.preventDefault();
                console.log('jQuery form submit event triggered!'); // Debug log
                this.createGroup();
            });
        } else {
            console.log('jQuery not available, using vanilla JS'); // Debug log
        }

        // Create group form submission - use event delegation for better reliability
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'createGroupForm') {
                e.preventDefault();
                console.log('Vanilla JS form submit event triggered!'); // Debug log
                this.createGroup();
            }
        });

        // Create group button click
        document.addEventListener('click', (e) => {
            if (e.target.closest('.create-group-btn')) {
                e.preventDefault();
                console.log('Create group button clicked!'); // Debug log
                this.showCreateGroupModal();
            }
        });

        // Group list item clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-contact^="group_"]')) {
                const groupId = e.target.closest('[data-contact]').getAttribute('data-contact').replace('group_', '');
                this.openGroupChat(groupId);
            }
        });

        // Member selection handling
        this.initializeMemberSelection();
        
        console.log('Event listeners initialized'); // Debug log
    }

    initializeMemberSelection() {
        const memberSelect = document.getElementById('groupMembers');
        if (memberSelect) {
            memberSelect.addEventListener('change', (e) => {
                this.updateSelectedMembers();
            });
        }
    }

    updateSelectedMembers() {
        const memberSelect = document.getElementById('groupMembers');
        const selectedMembersDiv = document.getElementById('selectedMembers');
        
        if (!memberSelect || !selectedMembersDiv) return;

        this.selectedMembers.clear();
        selectedMembersDiv.innerHTML = '';

        Array.from(memberSelect.selectedOptions).forEach(option => {
            this.selectedMembers.add({
                id: option.value,
                name: option.textContent
            });
        });

        // Display selected members as tags
        this.selectedMembers.forEach(member => {
            const tag = document.createElement('span');
            tag.className = 'member-tag';
            tag.innerHTML = `
                ${member.name}
                <span class="remove-member" onclick="removeGroupMember('${member.id}')">×</span>
            `;
            selectedMembersDiv.appendChild(tag);
        });
    }

    removeMember(memberId) {
        const memberSelect = document.getElementById('groupMembers');
        if (memberSelect) {
            Array.from(memberSelect.options).forEach(option => {
                if (option.value === memberId) {
                    option.selected = false;
                }
            });
            this.updateSelectedMembers();
        }
    }

    showCreateGroupModal() {
        console.log('showCreateGroupModal called'); // Debug log
        
        // Show the create group modal using the existing modal system
        if (typeof app_modal === 'function') {
            console.log('Using app_modal function'); // Debug log
            app_modal({
                show: true,
                name: 'createGroup'
            });
        } else {
            console.log('app_modal not found, using fallback'); // Debug log
            // Fallback to direct DOM manipulation
            const modal = document.querySelector('.app-modal[data-name="createGroup"]');
            if (modal) {
                modal.style.display = 'flex';
                console.log('Modal displayed via fallback'); // Debug log
            } else {
                console.error('Create group modal not found!'); // Debug log
            }
        }
    }

    hideModal(modalName) {
        // Hide modal using the existing modal system
        if (typeof app_modal === 'function') {
            app_modal({
                show: false,
                name: modalName
            });
        } else {
            // Fallback to direct DOM manipulation
            const modal = document.querySelector(`.app-modal[data-name="${modalName}"]`);
            if (modal) {
                modal.style.display = 'none';
            }
        }
    }

    async createGroup() {
        const form = document.getElementById('createGroupForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        console.log('Creating group...'); // Debug log
        
        // Validate form
        if (!this.validateForm()) {
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        const formData = new FormData(form);

        // Get CSRF token from meta tag or input field
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            csrfToken = document.querySelector('input[name="_token"]')?.value;
        }
        
        console.log('CSRF Token:', csrfToken); // Debug log
        
        if (!csrfToken) {
            this.showErrorMessage('CSRF token not found. Please refresh the page.');
            this.setLoadingState(false);
            return;
        }

        // Add CSRF token to form data
        formData.append('_token', csrfToken);
        
        // Log form data for debugging
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        try {
            console.log('Sending request to /devschat/createGroup...'); // Debug log
            
            const response = await fetch('/devschat/createGroup', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Response status:', response.status); // Debug log
            
            const result = await response.json();
            console.log('Response data:', result); // Debug log

            if (result.success) {
                // Show success message
                this.showSuccessMessage('Group created successfully!');
                
                // Close modal
                this.hideModal('createGroup');
                
                // Reset form
                form.reset();
                this.selectedMembers.clear();
                this.updateSelectedMembers();
                
                // Refresh the page to show the new group
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showErrorMessage(result.message || 'Failed to create group');
            }
        } catch (error) {
            console.error('Error creating group:', error);
            this.showErrorMessage('An error occurred while creating the group: ' + error.message);
        } finally {
            this.setLoadingState(false);
        }
    }

    validateForm() {
        const name = document.getElementById('groupName').value.trim();
        const members = Array.from(document.getElementById('groupMembers').selectedOptions);

        if (!name) {
            this.showErrorMessage('Please enter a group name');
            return false;
        }

        if (members.length === 0) {
            this.showErrorMessage('Please select at least one member');
            return false;
        }

        return true;
    }

    setLoadingState(loading) {
        const submitBtn = document.querySelector('.modern-btn-create');
        if (submitBtn) {
            if (loading) {
                submitBtn.disabled = true;
                submitBtn.parentElement.classList.add('loading');
            } else {
                submitBtn.disabled = false;
                submitBtn.parentElement.classList.remove('loading');
            }
        }
    }

    showSuccessMessage(message) {
        // Create success notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
            z-index: 10000;
            font-weight: 600;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.innerHTML = `<i class="fas fa-check-circle" style="margin-right: 8px;"></i>${message}`;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    showErrorMessage(message) {
        // Create error notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
            z-index: 10000;
            font-weight: 600;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.innerHTML = `<i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>${message}`;
        
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    async openGroupChat(groupId) {
        try {
            // Redirect to group chat page
            window.location.href = `/devschat/group/${groupId}`;
        } catch (error) {
            console.error('Error opening group chat:', error);
            this.showErrorMessage('An error occurred while opening the group chat');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    try {
        console.log('DOM loaded, initializing GroupChat...'); // Debug log
        window.groupChat = new GroupChat();
        console.log('GroupChat initialized successfully!'); // Debug log
        console.log('GroupChat instance:', window.groupChat); // Debug log
    } catch (error) {
        console.error('Error initializing GroupChat:', error);
        alert('Error initializing GroupChat: ' + error.message);
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
