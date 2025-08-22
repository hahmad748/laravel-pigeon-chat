<?php

namespace DevsFort\Pigeon\Chat\Examples;

use Illuminate\Database\Eloquent\Builder;

/**
 * Example Custom User Scope Class
 * 
 * This class demonstrates how to customize user filtering logic
 * for the chat system. Users can implement this to control
 * which users appear in the chat interface.
 * 
 * To use this class:
 * 1. Copy it to your app directory
 * 2. Modify the logic as needed
 * 3. Set DEVSFORT_USER_SCOPE in your .env file
 * 
 * Example .env configuration:
 * DEVSFORT_USER_SCOPE=App\Services\CustomUserScope
 */
class CustomUserScope
{
    /**
     * Apply scope to user query for general chat
     */
    public function apply(Builder $query)
    {
        // Example: Only show active users
        $query->where('active_status', 1);
        
        // Example: Exclude banned users
        $query->where('is_banned', 0);
        
        // Example: Only show users from same company/organization
        // $query->where('company_id', auth()->user()->company_id);
        
        // Example: Only show users with verified email
        // $query->whereNotNull('email_verified_at');
        
        // Example: Only show users with profile completed
        // $query->where('profile_completed', 1);
        
        // Example: Exclude specific user roles
        // $query->whereNotIn('role', ['admin', 'moderator']);
        
        // Example: Only show users in same timezone
        // $query->where('timezone', auth()->user()->timezone);
        
        // Example: Only show users who have been active recently
        // $query->where('last_seen_at', '>=', now()->subDays(30));
        
        return $query;
    }

    /**
     * Apply scope to user query for specific group
     */
    public function applyForGroup(Builder $query, $groupId)
    {
        // Apply general scope first
        $this->apply($query);
        
        // Example: Only show users who can join this specific group
        // $query->whereHas('group_permissions', function($q) use ($groupId) {
        //     $q->where('group_id', $groupId)->where('can_join', 1);
        // });
        
        // Example: Only show users with specific skills for project groups
        // $query->whereHas('skills', function($q) use ($groupId) {
        //     $q->whereIn('skill_id', $this->getRequiredSkills($groupId));
        // });
        
        // Example: Only show users from same department for department groups
        // $query->where('department_id', $this->getGroupDepartment($groupId));
        
        // Example: Only show users who have been invited
        // $query->whereHas('group_invitations', function($q) use ($groupId) {
        //     $q->where('group_id', $groupId)->where('status', 'pending');
        // });
        
        return $query;
    }

    /**
     * Get required skills for a group (example method)
     */
    protected function getRequiredSkills($groupId)
    {
        // This would typically query the database
        // For now, return empty array
        return [];
    }

    /**
     * Get department ID for a group (example method)
     */
    protected function getGroupDepartment($groupId)
    {
        // This would typically query the database
        // For now, return null
        return null;
    }
}
