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

        // Member selection handling
        this.initializeMemberSelection();
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
        // Show the create group modal using the existing modal system
        if (typeof app_modal === 'function') {
            app_modal({
                show: true,
                name: 'createGroup'
            });
        } else {
            // Fallback to direct DOM manipulation
            const modal = document.querySelector('.app-modal[data-name="createGroup"]');
            if (modal) {
                modal.style.display = 'flex';
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
        
        // Validate form
        if (!this.validateForm()) {
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        const formData = new FormData(form);

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        try {
            const response = await fetch('/devschat/createGroup', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

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
            this.showErrorMessage('An error occurred while creating the group');
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
    window.groupChat = new GroupChat();
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
