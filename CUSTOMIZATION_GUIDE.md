# Laravel Pigeon Chat - Customization Guide

## Overview

The Laravel Pigeon Chat package is designed to be highly customizable, allowing you to override default behavior, implement custom logic, and integrate with your existing application architecture.

> **📖 Back to Main Documentation**: [README.md](README.md) - Complete package overview and quick start guide

## Table of Contents

1. [Configuration Overrides](#configuration-overrides)
2. [Custom User Logic](#custom-user-logic)
3. [Custom Message Logic](#custom-message-logic)
4. [Custom Group Logic](#custom-group-logic)
5. [Custom Events](#custom-events)
6. [Custom Services](#custom-services)
7. [Custom Controllers](#custom-controllers)
8. [Custom Views](#custom-views)
9. [Custom Routes](#custom-routes)
10. [Custom Middleware](#custom-middleware)
11. [Custom Database](#custom-database)
12. [Custom File Handling](#custom-file-handling)
13. [Custom Notifications](#custom-notifications)
14. [Custom API Responses](#custom-api-responses)
15. [Examples](#examples)

## Configuration Overrides

### Environment Variables

All customization options can be set via environment variables in your `.env` file:

```bash
# User Model & Logic
DEVSFORT_USER_MODEL=App\Models\CustomUser
DEVSFORT_USER_SCOPE=App\Services\CustomUserScope
DEVSFORT_USER_SERVICE=App\Services\CustomUserService

# Message Logic
DEVSFORT_MESSAGE_MODEL=App\Models\CustomMessage
DEVSFORT_MESSAGE_VALIDATOR=App\Validators\CustomMessageValidator
DEVSFORT_MESSAGE_FILTER=App\Filters\CustomMessageFilter

# Group Logic
DEVSFORT_GROUP_MODEL=App\Models\CustomGroup
DEVSFORT_GROUP_VALIDATOR=App\Validators\CustomGroupValidator
DEVSFORT_GROUP_PERMISSION_HANDLER=App\Handlers\CustomGroupPermissionHandler

# Events
DEVSFORT_EVENT_MESSAGE_SENT=App\Events\CustomMessageEvent
DEVSFORT_EVENT_USER_STATUS=App\Events\CustomUserStatusEvent

# Services
DEVSFORT_CHAT_SERVICE=App\Services\CustomChatService
DEVSFORT_USER_SERVICE=App\Services\CustomUserService
DEVSFORT_GROUP_SERVICE=App\Services\CustomGroupService

# Controllers
DEVSFORT_MESSAGES_CONTROLLER=App\Http\Controllers\CustomMessagesController

# Views
DEVSFORT_VIEW_LAYOUT=layouts.custom-chat
DEVSFORT_VIEW_CHAT=pages.custom-chat
DEVSFORT_VIEW_MESSAGE_CARD=layouts.custom-message-card

# Routes
DEVSFORT_ROUTES_ENABLED=true
DEVSFORT_CUSTOM_ROUTES=routes/custom-chat.php

# Middleware
DEVSFORT_MIDDLEWARE_AUTH=auth
DEVSFORT_MIDDLEWARE_CUSTOM=["throttle:60,1","verified"]

# Database
DEVSFORT_DB_CONNECTION=mysql
DEVSFORT_DB_PREFIX=chat_
DEVSFORT_CUSTOM_MIGRATIONS=true

# Cache
DEVSFORT_CACHE_ENABLED=true
DEVSFORT_CACHE_DRIVER=redis
DEVSFORT_CACHE_PREFIX=devschat
DEVSFORT_CACHE_TTL=3600

# File Upload
DEVSFORT_FILE_DISK=public
DEVSFORT_FILE_MAX_SIZE=150000000
DEVSFORT_ALLOWED_IMAGES=png,jpg,jpeg,gif
DEVSFORT_ALLOWED_FILES=zip,rar,txt,pdf
DEVSFORT_FILE_PROCESSOR=App\Services\CustomFileProcessor

# Notifications
DEVSFORT_NOTIFICATIONS_ENABLED=true
DEVSFORT_NOTIFICATION_CHANNELS=mail,database,push
DEVSFORT_CUSTOM_NOTIFICATION=App\Notifications\CustomChatNotification

# API
DEVSFORT_API_ENABLED=true
DEVSFORT_API_VERSION=v1
DEVSFORT_API_RATE_LIMIT=60
DEVSFORT_API_RESPONSE_FORMATTER=App\Services\CustomResponseFormatter
```

## Custom User Logic

### 1. Custom User Scope

Create a custom user scope to control which users appear in the chat:

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class CustomUserScope
{
    public function apply(Builder $query)
    {
        // Only show active users
        $query->where('active_status', 1);
        
        // Only show users from same company
        $query->where('company_id', auth()->user()->company_id);
        
        // Exclude banned users
        $query->where('is_banned', 0);
        
        // Only show users with verified email
        $query->whereNotNull('email_verified_at');
        
        return $query;
    }

    public function applyForGroup(Builder $query, $groupId)
    {
        // Apply general scope
        $this->apply($query);
        
        // Only show users who can join this group
        $query->whereHas('group_permissions', function($q) use ($groupId) {
            $q->where('group_id', $groupId)->where('can_join', 1);
        });
        
        return $query;
    }
}
```

### 2. Custom User Service

Create a custom user service to control messaging permissions:

```php
<?php

namespace App\Services;

class CustomUserService
{
    public function canUserMessage($fromUserId, $toUserId)
    {
        // Check if users are friends
        if (!$this->areUsersFriends($fromUserId, $toUserId)) {
            return false;
        }
        
        // Check if user is not blocked
        if ($this->isUserBlocked($fromUserId, $toUserId)) {
            return false;
        }
        
        // Check if user has messaging permission
        if (!$this->hasMessagingPermission($fromUserId)) {
            return false;
        }
        
        return true;
    }

    protected function areUsersFriends($user1Id, $user2Id)
    {
        return DB::table('friendships')
            ->where(function($query) use ($user1Id, $user2Id) {
                $query->where('user_id', $user1Id)
                      ->where('friend_id', $user2Id)
                      ->where('status', 'accepted');
            })
            ->orWhere(function($query) use ($user1Id, $user2Id) {
                $query->where('user_id', $user2Id)
                      ->where('friend_id', $user1Id)
                      ->where('status', 'accepted');
            })
            ->exists();
    }

    protected function isUserBlocked($fromUserId, $toUserId)
    {
        return DB::table('user_blocks')
            ->where('blocker_id', $toUserId)
            ->where('blocked_id', $fromUserId)
            ->exists();
    }

    protected function hasMessagingPermission($userId)
    {
        $user = User::find($userId);
        return $user && $user->hasPermission('send_messages');
    }
}
```

## Custom Message Logic

### 1. Custom Message Validator

```php
<?php

namespace App\Validators;

class CustomMessageValidator
{
    public function validate($data)
    {
        $errors = [];
        
        // Check message length
        if (strlen($data['message']) > 1000) {
            $errors[] = 'Message too long';
        }
        
        // Check for inappropriate content
        if ($this->containsInappropriateContent($data['message'])) {
            $errors[] = 'Message contains inappropriate content';
        }
        
        // Check user's daily message limit
        if (!$this->checkDailyMessageLimit($data['from_id'])) {
            $errors[] = 'Daily message limit exceeded';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function containsInappropriateContent($message)
    {
        $inappropriateWords = ['spam', 'inappropriate'];
        foreach ($inappropriateWords as $word) {
            if (stripos($message, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function checkDailyMessageLimit($userId)
    {
        $dailyMessages = Message::where('from_id', $userId)
            ->whereDate('created_at', today())
            ->count();
        
        return $dailyMessages < 100; // Allow 100 messages per day
    }
}
```

### 2. Custom Message Filter

```php
<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CustomMessageFilter
{
    public function apply(Builder $query, $filters = [])
    {
        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        // Filter by message type
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        // Filter by attachment type
        if (isset($filters['has_attachment'])) {
            if ($filters['has_attachment']) {
                $query->whereNotNull('attachment');
            } else {
                $query->whereNull('attachment');
            }
        }
        
        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where(function($q) use ($filters) {
                $q->where('from_id', $filters['user_id'])
                  ->orWhere('to_id', $filters['user_id']);
            });
        }
        
        return $query;
    }
}
```

## Custom Group Logic

### 1. Custom Group Validator

```php
<?php

namespace App\Validators;

class CustomGroupValidator
{
    public function validate($data)
    {
        $errors = [];
        
        // Check group name length
        if (strlen($data['name']) > 255) {
            $errors[] = 'Group name too long';
        }
        
        // Check if group name is unique
        if ($this->groupNameExists($data['name'], $data['created_by'])) {
            $errors[] = 'Group name already exists';
        }
        
        // Check member limit
        if (count($data['members']) > 100) {
            $errors[] = 'Too many members';
        }
        
        // Check if all members can join groups
        foreach ($data['members'] as $memberId) {
            if (!$this->canUserJoinGroups($memberId)) {
                $errors[] = "User {$memberId} cannot join groups";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function groupNameExists($name, $createdBy)
    {
        return Group::where('name', $name)
            ->where('created_by', $createdBy)
            ->exists();
    }

    protected function canUserJoinGroups($userId)
    {
        $user = User::find($userId);
        return $user && $user->can_join_groups;
    }
}
```

### 2. Custom Group Permission Handler

```php
<?php

namespace App\Handlers;

class CustomGroupPermissionHandler
{
    public function canUserJoinGroup($userId, $groupId)
    {
        $user = User::find($userId);
        $group = Group::find($groupId);
        
        if (!$user || !$group) {
            return false;
        }
        
        // Check if group is private
        if ($group->is_private) {
            return $this->hasGroupInvitation($userId, $groupId);
        }
        
        // Check user's group joining permissions
        if (!$user->can_join_groups) {
            return false;
        }
        
        // Check if user is not banned from groups
        if ($user->banned_from_groups) {
            return false;
        }
        
        return true;
    }

    public function canUserManageGroup($userId, $groupId)
    {
        $user = User::find($userId);
        $group = Group::find($groupId);
        
        if (!$user || !$group) {
            return false;
        }
        
        // Group creator can always manage
        if ($group->created_by == $userId) {
            return true;
        }
        
        // Check if user is admin
        $member = GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();
        
        return $member && $member->role === 'admin';
    }

    public function canUserRemoveMember($userId, $groupId, $memberToRemoveId)
    {
        // Users can always remove themselves
        if ($userId == $memberToRemoveId) {
            return true;
        }
        
        // Check if user can manage the group
        if (!$this->canUserManageGroup($userId, $groupId)) {
            return false;
        }
        
        // Cannot remove group creator
        $group = Group::find($groupId);
        if ($group->created_by == $memberToRemoveId) {
            return false;
        }
        
        return true;
    }
}
```

## Custom Events

### 1. Custom Message Event

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $messageType;
    public $channelName;

    public function __construct($data, $messageType = 'user')
    {
        $this->data = $data;
        $this->messageType = $messageType;
        
        // Custom channel logic
        if ($messageType === 'group') {
            $this->channelName = 'custom-group-chat';
        } elseif ($messageType === 'broadcast') {
            $this->channelName = 'broadcast-messages';
        } else {
            $this->channelName = 'custom-user-chat';
        }
    }

    public function broadcastOn()
    {
        return new Channel($this->channelName);
    }

    public function broadcastWith()
    {
        return [
            'event' => 'custom.message.sent',
            'data' => $this->data,
            'type' => $this->messageType,
            'timestamp' => now()->toISOString(),
            'custom_field' => 'custom_value'
        ];
    }

    public function broadcastAs()
    {
        return 'custom.message.sent';
    }
}
```

## Custom Services

### 1. Custom Chat Service

```php
<?php

namespace App\Services;

use DevsFort\Pigeon\Chat\Library\BaseChatService;

class CustomChatService extends BaseChatService
{
    public function getUsersForChat($excludeCurrentUser = true)
    {
        $query = parent::getUsersForChat($excludeCurrentUser);
        
        // Add custom logic
        $query->where('chat_enabled', 1);
        $query->where('last_seen_at', '>=', now()->subDays(7));
        
        return $query->get();
    }

    public function canUserMessage($fromUserId, $toUserId)
    {
        // Add custom permission logic
        if (!$this->checkBusinessHours()) {
            return false;
        }
        
        if (!$this->checkUserSubscription($fromUserId)) {
            return false;
        }
        
        return parent::canUserMessage($fromUserId, $toUserId);
    }

    protected function checkBusinessHours()
    {
        $hour = now()->hour;
        return $hour >= 9 && $hour <= 17; // 9 AM to 5 PM
    }

    protected function checkUserSubscription($userId)
    {
        $user = User::find($userId);
        return $user && $user->subscription && $user->subscription->isActive();
    }
}
```

## Custom Controllers

### 1. Custom Messages Controller

```php
<?php

namespace App\Http\Controllers;

use DevsFort\Pigeon\Chat\Http\Controllers\MessagesController as BaseMessagesController;

class CustomMessagesController extends BaseMessagesController
{
    public function index($id = null)
    {
        // Add custom logic before calling parent
        $this->logChatAccess();
        
        // Call parent method
        $response = parent::index($id);
        
        // Add custom logic after parent
        $this->trackUserActivity();
        
        return $response;
    }

    public function send(Request $request)
    {
        // Custom validation
        $validator = $this->customValidateMessage($request);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Custom permission check
        if (!$this->canSendMessage($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot send messages at this time'
            ], 403);
        }
        
        // Call parent method
        $response = parent::send($request);
        
        // Custom post-processing
        $this->processMessageAnalytics($request);
        
        return $response;
    }

    protected function customValidateMessage(Request $request)
    {
        return Validator::make($request->all(), [
            'message' => 'required|string|max:1000|not_spam',
            'id' => 'required|integer|exists:users,id',
            'type' => 'required|in:user,group'
        ]);
    }

    protected function canSendMessage(Request $request)
    {
        // Check business hours
        if (!$this->isBusinessHours()) {
            return false;
        }
        
        // Check user permissions
        if (!auth()->user()->can('send_messages')) {
            return false;
        }
        
        return true;
    }

    protected function isBusinessHours()
    {
        $hour = now()->hour;
        return $hour >= 9 && $hour <= 17;
    }

    protected function processMessageAnalytics(Request $request)
    {
        // Log message for analytics
        MessageAnalytics::create([
            'user_id' => auth()->id(),
            'recipient_id' => $request->id,
            'type' => $request->type,
            'timestamp' => now()
        ]);
    }
}
```

## Custom Views

### 1. Override Default Views

Publish the views and customize them:

```bash
php artisan vendor:publish --tag=devschat-views
```

Then modify the published views in `resources/views/vendor/DevsFort/`.

### 2. Custom Layout

```php
<!-- resources/views/layouts/custom-chat.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Custom Chat</title>
    <link rel="stylesheet" href="{{ asset('css/custom-chat.css') }}">
</head>
<body>
    <div class="custom-chat-container">
        @include('vendor.DevsFort.pages.app')
    </div>
    
    <script src="{{ asset('js/custom-chat.js') }}"></script>
</body>
</html>
```

### 3. Custom Message Card

```php
<!-- resources/views/layouts/custom-message-card.blade.php -->
<div class="custom-message-card {{ $viewType }}">
    <div class="message-header">
        <span class="sender-name">{{ $sender->name }}</span>
        <span class="message-time">{{ $time }}</span>
    </div>
    
    <div class="message-content">
        @if($attachment)
            @if($attachment[2] === 'image')
                <img src="{{ asset('storage/' . $attachment[0]) }}" alt="Image">
            @else
                <a href="{{ route('attachments.download', $attachment[0]) }}">
                    {{ $attachment[1] }}
                </a>
            @endif
        @endif
        
        <p class="message-text">{{ $message }}</p>
    </div>
    
    <div class="message-footer">
        @if($seen)
            <span class="seen-indicator">✓✓</span>
        @else
            <span class="sent-indicator">✓</span>
        @endif
    </div>
</div>
```

## Custom Routes

### 1. Custom Routes File

```php
<?php
// routes/custom-chat.php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('devschat.path'),
    'middleware' => config('devschat.middleware'),
    'namespace' => config('devschat.namespace')
], function () {
    
    // Override default routes
    Route::get('/', 'CustomMessagesController@index')->name('custom.chat');
    
    // Add custom routes
    Route::post('/custom-action', 'CustomMessagesController@customAction');
    Route::get('/analytics', 'CustomMessagesController@analytics');
    Route::post('/bulk-message', 'CustomMessagesController@bulkMessage');
    
    // Custom group routes
    Route::post('/custom-group-action', 'CustomGroupController@customAction');
    Route::get('/group-analytics/{groupId}', 'CustomGroupController@analytics');
});
```

### 2. Enable Custom Routes

```bash
# In your .env file
DEVSFORT_CUSTOM_ROUTES=routes/custom-chat.php
```

## Custom Middleware

### 1. Custom Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomChatMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user can access chat
        if (!auth()->user()->canAccessChat()) {
            return response()->json([
                'error' => 'Chat access denied'
            ], 403);
        }
        
        // Check user's chat quota
        if (auth()->user()->hasExceededChatQuota()) {
            return response()->json([
                'error' => 'Chat quota exceeded'
            ], 429);
        }
        
        // Log chat access
        $this->logChatAccess($request);
        
        return $next($request);
    }

    protected function logChatAccess(Request $request)
    {
        ChatAccessLog::create([
            'user_id' => auth()->id(),
            'route' => $request->route()->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);
    }
}
```

### 2. Register Custom Middleware

```bash
# In your .env file
DEVSFORT_MIDDLEWARE_CUSTOM=["custom.chat","throttle:60,1"]
```

## Custom Database

### 1. Custom Migrations

```bash
# In your .env file
DEVSFORT_CUSTOM_MIGRATIONS=true
```

Then create your custom migrations in `database/migrations/`.

### 2. Custom Database Connection

```bash
# In your .env file
DEVSFORT_DB_CONNECTION=chat_database
DEVSFORT_DB_PREFIX=chat_
```

## Custom File Handling

### 1. Custom File Processor

```php
<?php

namespace App\Services;

class CustomFileProcessor
{
    public function process($file, $type)
    {
        // Custom file validation
        if (!$this->validateFile($file, $type)) {
            throw new \Exception('File validation failed');
        }
        
        // Custom file processing
        $processedFile = $this->processFile($file, $type);
        
        // Custom storage logic
        $path = $this->storeFile($processedFile, $type);
        
        // Custom metadata
        $metadata = $this->extractMetadata($file);
        
        return [
            'filename' => $processedFile->getFilename(),
            'path' => $path,
            'size' => $processedFile->getSize(),
            'type' => $processedFile->getExtension(),
            'metadata' => $metadata
        ];
    }

    protected function validateFile($file, $type)
    {
        // Custom validation logic
        return true;
    }

    protected function processFile($file, $type)
    {
        // Custom processing logic
        return $file;
    }

    protected function storeFile($file, $type)
    {
        // Custom storage logic
        return $file->store("custom/{$type}s");
    }

    protected function extractMetadata($file)
    {
        // Extract custom metadata
        return [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()
        ];
    }
}
```

## Custom Notifications

### 1. Custom Notification Class

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomChatNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail', 'database', 'slack'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Message Received')
            ->line('You have received a new message.')
            ->action('View Message', url('/chat'))
            ->line('Thank you for using our application!');
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->content('New message received from ' . $notifiable->name);
    }
}
```

## Custom API Responses

### 1. Custom Response Formatter

```php
<?php

namespace App\Services;

class CustomResponseFormatter
{
    public function format($data, $status = 'success', $message = '')
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'version' => '2.0.0',
            'request_id' => uniqid(),
            'custom_field' => 'custom_value'
        ];
    }
}
```

## Examples

### Complete Custom Implementation

Here's a complete example of how to customize the package:

```php
<?php
// app/Services/CustomChatService.php

namespace App\Services;

use DevsFort\Pigeon\Chat\Library\BaseChatService;

class CustomChatService extends BaseChatService
{
    public function getUsersForChat($excludeCurrentUser = true)
    {
        $query = parent::getUsersForChat($excludeCurrentUser);
        
        // Only show users from same company
        $query->where('company_id', auth()->user()->company_id);
        
        // Only show active users
        $query->where('active_status', 1);
        
        // Only show users with verified email
        $query->whereNotNull('email_verified_at');
        
        return $query->get();
    }

    public function canUserMessage($fromUserId, $toUserId)
    {
        // Check if users are friends
        if (!$this->areUsersFriends($fromUserId, $toUserId)) {
            return false;
        }
        
        // Check if user is not blocked
        if ($this->isUserBlocked($fromUserId, $toUserId)) {
            return false;
        }
        
        return true;
    }

    protected function areUsersFriends($user1Id, $user2Id)
    {
        return DB::table('friendships')
            ->where('user_id', $user1Id)
            ->where('friend_id', $user2Id)
            ->where('status', 'accepted')
            ->exists();
    }

    protected function isUserBlocked($fromUserId, $toUserId)
    {
        return DB::table('user_blocks')
            ->where('blocker_id', $toUserId)
            ->where('blocked_id', $fromUserId)
            ->exists();
    }
}
```

### Environment Configuration

```bash
# .env file
DEVSFORT_USER_SERVICE=App\Services\CustomChatService
DEVSFORT_USER_SCOPE=App\Services\CustomUserScope
DEVSFORT_MESSAGE_VALIDATOR=App\Validators\CustomMessageValidator
DEVSFORT_GROUP_VALIDATOR=App\Validators\CustomGroupValidator
DEVSFORT_GROUP_PERMISSION_HANDLER=App\Handlers\CustomGroupPermissionHandler
DEVSFORT_EVENT_MESSAGE_SENT=App\Events\CustomMessageEvent
DEVSFORT_MESSAGES_CONTROLLER=App\Http\Controllers\CustomMessagesController
DEVSFORT_VIEW_LAYOUT=layouts.custom-chat
DEVSFORT_CUSTOM_ROUTES=routes/custom-chat.php
DEVSFORT_MIDDLEWARE_CUSTOM=["custom.chat","throttle:60,1"]
DEVSFORT_CACHE_ENABLED=true
DEVSFORT_CACHE_DRIVER=redis
DEVSFORT_FILE_PROCESSOR=App\Services\CustomFileProcessor
DEVSFORT_CUSTOM_NOTIFICATION=App\Notifications\CustomChatNotification
DEVSFORT_API_RESPONSE_FORMATTER=App\Services\CustomResponseFormatter
```

## Best Practices

1. **Extend Base Classes**: Always extend the base classes provided by the package
2. **Use Configuration**: Leverage the configuration system for customization
3. **Maintain Compatibility**: Ensure your customizations don't break package functionality
4. **Test Thoroughly**: Test all customizations thoroughly before production
5. **Document Changes**: Document all customizations for future maintenance
6. **Use Service Providers**: Register custom services in service providers
7. **Follow Laravel Conventions**: Follow Laravel best practices and conventions

## Troubleshooting

### Common Issues

1. **Custom Classes Not Found**: Ensure proper namespace and autoloading
2. **Configuration Not Loading**: Check environment variable names and values
3. **Routes Not Working**: Verify route registration and middleware
4. **Views Not Rendering**: Check view paths and blade syntax
5. **Database Errors**: Verify database connection and table structure

### Debug Mode

Enable debug mode to see detailed error messages:

```bash
APP_DEBUG=true
```

### Logs

Check Laravel logs for detailed error information:

```bash
tail -f storage/logs/laravel.log
```

---

This customization guide provides comprehensive information on how to override default package behavior and implement custom logic. Use these examples as starting points and adapt them to your specific requirements.
