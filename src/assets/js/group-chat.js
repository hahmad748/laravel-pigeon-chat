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

        // Modal cancel buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('cancel')) {
                const modal = e.target.closest('.app-modal');
                if (modal) {
                    this.hideModal(modal.getAttribute('data-name'));
                }
            }
        });
    }

    showCreateGroupModal() {
        // Show the create group modal using the existing modal system
        const modal = document.querySelector('.app-modal[data-name="createGroup"]');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    hideModal(modalName) {
        const modal = document.querySelector(`.app-modal[data-name="${modalName}"]`);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    async createGroup() {
        const form = document.getElementById('createGroupForm');
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
                this.showAlert('Group created successfully!', 'success');
                
                // Close modal
                this.hideModal('createGroup');
                
                // Reset form
                form.reset();
                
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

    showAlert(message, type = 'info') {
        // Simple alert system - you can enhance this
        alert(`${type.toUpperCase()}: ${message}`);
    }

    async openGroupChat(groupId) {
        try {
            // Redirect to group chat page
            window.location.href = `/devschat/group/${groupId}`;
        } catch (error) {
            console.error('Error opening group chat:', error);
            this.showAlert('An error occurred while opening the group chat', 'error');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new GroupChat();
});
