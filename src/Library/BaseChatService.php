<?php

namespace DevsFort\Pigeon\Chat\Library;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

abstract class BaseChatService
{
    /**
     * Get the user model class
     */
    protected function getUserModel()
    {
        return Config::get('devschat.customization.user_model', 'App\Models\User');
    }

    /**
     * Get the message model class
     */
    protected function getMessageModel()
    {
        return Config::get('devschat.customization.message_model', 'DevsFort\Pigeon\Chat\Models\Message');
    }

    /**
     * Get the group model class
     */
    protected function getGroupModel()
    {
        return Config::get('devschat.customization.group_model', 'DevsFort\Pigeon\Chat\Models\Group');
    }

    /**
     * Get the group member model class
     */
    protected function getGroupMemberModel()
    {
        return Config::get('devschat.customization.group_member_model', 'DevsFort\Pigeon\Chat\Models\GroupMember');
    }

    /**
     * Get users for chat - can be overridden
     */
    public function getUsersForChat($excludeCurrentUser = true)
    {
        $userModel = $this->getUserModel();
        $query = $userModel::query();

        if ($excludeCurrentUser) {
            $query->where('id', '<>', Auth::id());
        }

        // Apply custom user scope if configured
        $customScope = Config::get('devschat.customization.user_scope');
        if ($customScope && class_exists($customScope)) {
            $scopeInstance = new $customScope();
            $query = $scopeInstance->apply($query);
        }

        // Apply custom query builder if configured
        $customQueryBuilder = Config::get('devschat.customization.user_query_builder');
        if ($customQueryBuilder && class_exists($customQueryBuilder)) {
            $queryBuilder = new $customQueryBuilder();
            $query = $queryBuilder->build($query);
        }

        return $query->get();
    }

    /**
     * Get users for group - can be overridden
     */
    public function getUsersForGroup($groupId, $excludeCurrentUser = true)
    {
        $userModel = $this->getUserModel();
        $query = $userModel::query();

        if ($excludeCurrentUser) {
            $query->where('id', '<>', Auth::id());
        }

        // Apply custom logic for group user selection
        $customScope = Config::get('devschat.customization.user_scope');
        if ($customScope && class_exists($customScope)) {
            $scopeInstance = new $customScope();
            $query = $scopeInstance->applyForGroup($query, $groupId);
        }

        return $query->get();
    }

    /**
     * Check if user can message another user - can be overridden
     */
    public function canUserMessage($fromUserId, $toUserId)
    {
        // Default implementation - allow all authenticated users to message each other
        // Override this method in your custom service for custom logic
        
        $customService = Config::get('devschat.customization.services.user_service');
        if ($customService && class_exists($customService)) {
            $service = new $customService();
            if (method_exists($service, 'canUserMessage')) {
                return $service->canUserMessage($fromUserId, $toUserId);
            }
        }

        return true;
    }

    /**
     * Check if user can join group - can be overridden
     */
    public function canUserJoinGroup($userId, $groupId)
    {
        $customService = Config::get('devschat.customization.services.group_service');
        if ($customService && class_exists($customService)) {
            $service = new $customService();
            if (method_exists($service, 'canUserJoinGroup')) {
                return $service->canUserJoinGroup($userId, $groupId);
            }
        }

        // Default implementation
        $group = $this->getGroupModel()::find($groupId);
        if (!$group) {
            return false;
        }

        // Check if group is private
        if ($group->is_private) {
            // For private groups, only allow if user is invited or has special permission
            return $this->hasGroupInvitation($userId, $groupId);
        }

        return true;
    }

    /**
     * Check if user has group invitation - can be overridden
     */
    protected function hasGroupInvitation($userId, $groupId)
    {
        // Default implementation - check group_members table
        $groupMemberModel = $this->getGroupMemberModel();
        return $groupMemberModel::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get user's groups - can be overridden
     */
    public function getUserGroups($userId)
    {
        $customService = Config::get('devschat.customization.services.group_service');
        if ($customService && class_exists($customService)) {
            $service = new $customService();
            if (method_exists($service, 'getUserGroups')) {
                return $service->getUserGroups($userId);
            }
        }

        // Default implementation
        $groupModel = $this->getGroupModel();
        return $groupModel::whereHas('members', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    /**
     * Validate message - can be overridden
     */
    public function validateMessage($data)
    {
        $customValidator = Config::get('devschat.customization.message_validator');
        if ($customValidator && class_exists($customValidator)) {
            $validator = new $customValidator();
            return $validator->validate($data);
        }

        // Default validation
        return [
            'valid' => true,
            'errors' => []
        ];
    }

    /**
     * Filter messages - can be overridden
     */
    public function filterMessages($query, $filters = [])
    {
        $customFilter = Config::get('devschat.customization.message_filter');
        if ($customFilter && class_exists($customFilter)) {
            $filter = new $customFilter();
            return $filter->apply($query, $filters);
        }

        // Default filtering
        return $query;
    }

    /**
     * Get cache key - can be overridden
     */
    protected function getCacheKey($key)
    {
        $prefix = Config::get('devschat.customization.cache.prefix', 'devschat');
        return "{$prefix}:{$key}";
    }

    /**
     * Get cache TTL - can be overridden
     */
    protected function getCacheTTL()
    {
        return Config::get('devschat.customization.cache.ttl', 3600);
    }

    /**
     * Cache data - can be overridden
     */
    protected function cacheData($key, $data, $ttl = null)
    {
        if (!Config::get('devschat.customization.cache.enabled', true)) {
            return $data;
        }

        $cacheKey = $this->getCacheKey($key);
        $cacheTTL = $ttl ?: $this->getCacheTTL();

        Cache::put($cacheKey, $data, $cacheTTL);
        return $data;
    }

    /**
     * Get cached data - can be overridden
     */
    protected function getCachedData($key)
    {
        if (!Config::get('devschat.customization.cache.enabled', true)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($key);
        return Cache::get($cacheKey);
    }

    /**
     * Clear cache - can be overridden
     */
    protected function clearCache($key = null)
    {
        if (!Config::get('devschat.customization.cache.enabled', true)) {
            return;
        }

        if ($key) {
            $cacheKey = $this->getCacheKey($key);
            Cache::forget($cacheKey);
        } else {
            $prefix = Config::get('devschat.customization.cache.prefix', 'devschat');
            Cache::flush($prefix);
        }
    }

    /**
     * Get file upload configuration - can be overridden
     */
    protected function getFileUploadConfig()
    {
        return Config::get('devschat.customization.file_upload', [
            'disk' => 'public',
            'max_size' => 150000000,
            'allowed_types' => [
                'images' => 'png,jpg,jpeg,gif',
                'files' => 'zip,rar,txt,pdf'
            ]
        ]);
    }

    /**
     * Process file upload - can be overridden
     */
    public function processFileUpload($file, $type = 'attachment')
    {
        $customProcessor = Config::get('devschat.customization.file_upload.custom_processor');
        if ($customProcessor && class_exists($customProcessor)) {
            $processor = new $customProcessor();
            return $processor->process($file, $type);
        }

        // Default file processing
        return $this->defaultFileProcessor($file, $type);
    }

    /**
     * Default file processor
     */
    protected function defaultFileProcessor($file, $type)
    {
        $config = $this->getFileUploadConfig();
        $disk = $config['disk'];
        $maxSize = $config['max_size'];
        $allowedTypes = $config['allowed_types'];

        // Validate file size
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds maximum allowed size');
        }

        // Validate file type
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = array_merge(
            explode(',', $allowedTypes['images']),
            explode(',', $allowedTypes['files'])
        );

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('File type not allowed');
        }

        // Generate unique filename
        $filename = uniqid() . '.' . $extension;
        
        // Store file
        $path = $file->storeAs("public/{$type}s", $filename, $disk);

        return [
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $extension
        ];
    }

    /**
     * Get notification configuration - can be overridden
     */
    protected function getNotificationConfig()
    {
        return Config::get('devschat.customization.notifications', [
            'enabled' => true,
            'channels' => ['mail', 'database']
        ]);
    }

    /**
     * Send notification - can be overridden
     */
    public function sendNotification($user, $notification, $data = [])
    {
        $config = $this->getNotificationConfig();
        
        if (!$config['enabled']) {
            return;
        }

        $customNotificationClass = Config::get('devschat.customization.notifications.custom_notification_class');
        if ($customNotificationClass && class_exists($customNotificationClass)) {
            $notificationInstance = new $customNotificationClass();
            return $notificationInstance->send($user, $notification, $data);
        }

        // Default notification sending
        return $user->notify(new $notification($data));
    }

    /**
     * Check if user can create groups - can be overridden
     */
    public function canUserCreateGroups($userId)
    {
        $customService = Config::get('devschat.customization.services.group_service');
        if ($customService && class_exists($customService)) {
            $service = new $customService();
            if (method_exists($service, 'canUserCreateGroups')) {
                return $service->canUserCreateGroups($userId);
            }
        }

        // Default implementation - allow all authenticated users to create groups
        return true;
    }

    /**
     * Get API configuration - can be overridden
     */
    protected function getApiConfig()
    {
        return Config::get('devschat.customization.api', [
            'enabled' => true,
            'version' => 'v1',
            'rate_limiting' => 60
        ]);
    }

    /**
     * Format API response - can be overridden
     */
    public function formatApiResponse($data, $status = 'success', $message = '')
    {
        $customFormatter = Config::get('devschat.customization.api.custom_response_formatter');
        if ($customFormatter && class_exists($customFormatter)) {
            $formatter = new $customFormatter();
            return $formatter->format($data, $status, $message);
        }

        // Default response format
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
    }
}
