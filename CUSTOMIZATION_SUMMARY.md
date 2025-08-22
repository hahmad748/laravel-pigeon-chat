# Laravel Pigeon Chat - Customization Features Summary

## 🎯 **Overview**

The Laravel Pigeon Chat package has been completely redesigned to be **highly customizable**, allowing package installers to override default behavior, implement custom logic, and integrate with their existing application architecture.

> **📖 Back to Main Documentation**: [README.md](README.md) - Complete package overview and quick start guide

## 🚀 **Key Customization Features**

### 1. **User Management Customization**
- **Custom User Model**: Override the default User model
- **Custom User Scope**: Control which users appear in chat
- **Custom User Service**: Implement custom messaging permissions
- **User Filtering**: Filter users based on custom criteria (company, role, status, etc.)

### 2. **Message Logic Customization**
- **Custom Message Validation**: Implement custom message validation rules
- **Custom Message Filtering**: Add custom message filtering logic
- **Message Permissions**: Control who can send messages to whom
- **Content Moderation**: Implement custom content filtering

### 3. **Group Chat Customization**
- **Custom Group Validation**: Implement custom group creation rules
- **Custom Permission Handler**: Control group access and management
- **Member Management**: Custom logic for adding/removing members
- **Group Types**: Support for different group types (public, private, etc.)

### 4. **Event System Customization**
- **Custom Events**: Override default broadcasting events
- **Custom Channels**: Implement custom broadcasting channels
- **Event Data**: Customize event payload and structure
- **Broadcasting Logic**: Custom broadcasting behavior

### 5. **Service Layer Customization**
- **Base Service Class**: Extendable base service with default implementations
- **Custom Services**: Override any service method
- **Service Injection**: Use Laravel's service container for custom services
- **Method Overriding**: Override specific methods while keeping others

### 6. **Controller Customization**
- **Custom Controllers**: Extend default controllers
- **Method Overriding**: Override specific controller methods
- **Custom Logic**: Add custom logic before/after default operations
- **Permission Integration**: Integrate with custom permission systems

### 7. **View Customization**
- **Custom Layouts**: Override default view layouts
- **Custom Components**: Customize message cards, contact items, etc.
- **View Publishing**: Publish and modify default views
- **Blade Integration**: Full Blade template customization

### 8. **Route Customization**
- **Custom Routes**: Override default routes
- **Custom Route Files**: Use separate route files
- **Middleware Integration**: Custom middleware support
- **Route Naming**: Custom route names and structure

### 9. **Database Customization**
- **Custom Migrations**: Use custom migration files
- **Database Connection**: Custom database connections
- **Table Prefixing**: Custom table prefixes
- **Schema Customization**: Modify database schema

### 10. **File Handling Customization**
- **Custom File Processors**: Implement custom file upload logic
- **File Validation**: Custom file validation rules
- **Storage Configuration**: Custom storage disks and paths
- **File Metadata**: Extract and store custom file metadata

### 11. **Notification Customization**
- **Custom Notifications**: Implement custom notification classes
- **Multiple Channels**: Support for multiple notification channels
- **Notification Logic**: Custom notification sending logic
- **Template Customization**: Custom notification templates

### 12. **API Customization**
- **Response Formatting**: Custom API response formats
- **Rate Limiting**: Custom rate limiting rules
- **API Versioning**: Support for API versioning
- **Custom Endpoints**: Add custom API endpoints

## 🔧 **Implementation Methods**

### **Method 1: Environment Variables**
All customization options can be set via environment variables:

```bash
# User customization
DEVSFORT_USER_MODEL=App\Models\CustomUser
DEVSFORT_USER_SCOPE=App\Services\CustomUserScope
DEVSFORT_USER_SERVICE=App\Services\CustomUserService

# Message customization
DEVSFORT_MESSAGE_VALIDATOR=App\Validators\CustomMessageValidator
DEVSFORT_MESSAGE_FILTER=App\Filters\CustomMessageFilter

# Group customization
DEVSFORT_GROUP_VALIDATOR=App\Validators\CustomGroupValidator
DEVSFORT_GROUP_PERMISSION_HANDLER=App\Handlers\CustomGroupPermissionHandler

# Service customization
DEVSFORT_CHAT_SERVICE=App\Services\CustomChatService
DEVSFORT_GROUP_SERVICE=App\Services\CustomGroupService

# Controller customization
DEVSFORT_MESSAGES_CONTROLLER=App\Http\Controllers\CustomMessagesController

# View customization
DEVSFORT_VIEW_LAYOUT=layouts.custom-chat
DEVSFORT_VIEW_CHAT=pages.custom-chat

# Route customization
DEVSFORT_CUSTOM_ROUTES=routes/custom-chat.php
DEVSFORT_MIDDLEWARE_CUSTOM=["custom.chat","throttle:60,1"]
```

### **Method 2: Class Extension**
Extend base classes to customize behavior:

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
        $query->where('company_id', auth()->user()->company_id);
        $query->where('active_status', 1);
        
        return $query->get();
    }

    public function canUserMessage($fromUserId, $toUserId)
    {
        // Custom permission logic
        if (!$this->areUsersFriends($fromUserId, $toUserId)) {
            return false;
        }
        
        return parent::canUserMessage($fromUserId, $toUserId);
    }
}
```

### **Method 3: Interface Implementation**
Implement custom interfaces for specific functionality:

```php
<?php
namespace App\Validators;

class CustomMessageValidator
{
    public function validate($data)
    {
        $errors = [];
        
        // Custom validation logic
        if (strlen($data['message']) > 1000) {
            $errors[] = 'Message too long';
        }
        
        if ($this->containsInappropriateContent($data['message'])) {
            $errors[] = 'Inappropriate content';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
```

## 📋 **Configuration Structure**

The configuration file (`config/devschat.php`) is organized into logical sections:

```php
'customization' => [
    // User customization
    'user_model' => env('DEVSFORT_USER_MODEL', 'App\Models\User'),
    'user_scope' => env('DEVSFORT_USER_SCOPE', null),
    'user_service' => env('DEVSFORT_USER_SERVICE', null),
    
    // Message customization
    'message_validator' => env('DEVSFORT_MESSAGE_VALIDATOR', null),
    'message_filter' => env('DEVSFORT_MESSAGE_FILTER', null),
    
    // Group customization
    'group_validator' => env('DEVSFORT_GROUP_VALIDATOR', null),
    'group_permission_handler' => env('DEVSFORT_GROUP_PERMISSION_HANDLER', null),
    
    // Service customization
    'chat_service' => env('DEVSFORT_CHAT_SERVICE', 'DevsFort\Pigeon\Chat\Library\DevsFortChat'),
    'user_service' => env('DEVSFORT_USER_SERVICE', null),
    'group_service' => env('DEVSFORT_GROUP_SERVICE', null),
    
    // Controller customization
    'messages_controller' => env('DEVSFORT_MESSAGES_CONTROLLER', 'DevsFort\Pigeon\Chat\Http\Controllers\MessagesController'),
    
    // View customization
    'layout' => env('DEVSFORT_VIEW_LAYOUT', 'DevsFort::layouts.app'),
    'chat_interface' => env('DEVSFORT_VIEW_CHAT', 'DevsFort::pages.app'),
    
    // Route customization
    'routes' => [
        'enabled' => env('DEVSFORT_ROUTES_ENABLED', true),
        'custom_routes_file' => env('DEVSFORT_CUSTOM_ROUTES', null),
    ],
    
    // Middleware customization
    'middleware' => [
        'auth' => env('DEVSFORT_MIDDLEWARE_AUTH', 'auth'),
        'custom' => env('DEVSFORT_MIDDLEWARE_CUSTOM', []),
    ],
    
    // Database customization
    'database' => [
        'connection' => env('DEVSFORT_DB_CONNECTION', 'default'),
        'prefix' => env('DEVSFORT_DB_PREFIX', ''),
        'custom_migrations' => env('DEVSFORT_CUSTOM_MIGRATIONS', false),
    ],
    
    // Cache customization
    'cache' => [
        'enabled' => env('DEVSFORT_CACHE_ENABLED', true),
        'driver' => env('DEVSFORT_CACHE_DRIVER', 'redis'),
        'prefix' => env('DEVSFORT_CACHE_PREFIX', 'devschat'),
        'ttl' => env('DEVSFORT_CACHE_TTL', 3600),
    ],
    
    // File upload customization
    'file_upload' => [
        'disk' => env('DEVSFORT_FILE_DISK', 'public'),
        'max_size' => env('DEVSFORT_FILE_MAX_SIZE', 150000000),
        'allowed_types' => [
            'images' => env('DEVSFORT_ALLOWED_IMAGES', 'png,jpg,jpeg,gif'),
            'files' => env('DEVSFORT_ALLOWED_FILES', 'zip,rar,txt,pdf'),
        ],
        'custom_processor' => env('DEVSFORT_FILE_PROCESSOR', null),
    ],
    
    // Notification customization
    'notifications' => [
        'enabled' => env('DEVSFORT_NOTIFICATIONS_ENABLED', true),
        'channels' => env('DEVSFORT_NOTIFICATION_CHANNELS', ['mail', 'database']),
        'custom_notification_class' => env('DEVSFORT_CUSTOM_NOTIFICATION', null),
    ],
    
    // API customization
    'api' => [
        'enabled' => env('DEVSFORT_API_ENABLED', true),
        'version' => env('DEVSFORT_API_VERSION', 'v1'),
        'rate_limiting' => env('DEVSFORT_API_RATE_LIMIT', 60),
        'custom_response_formatter' => env('DEVSFORT_API_RESPONSE_FORMATTER', null),
    ],
],
```

## 🎨 **Example Use Cases**

### **Use Case 1: Company-Based User Filtering**
```php
// CustomUserScope.php
public function apply(Builder $query)
{
    $query->where('company_id', auth()->user()->company_id);
    $query->where('active_status', 1);
    $query->where('department_id', auth()->user()->department_id);
    return $query;
}
```

### **Use Case 2: Friend-Based Messaging**
```php
// CustomUserService.php
public function canUserMessage($fromUserId, $toUserId)
{
    return $this->areUsersFriends($fromUserId, $toUserId) &&
           !$this->isUserBlocked($fromUserId, $toUserId);
}
```

### **Use Case 3: Role-Based Group Management**
```php
// CustomGroupPermissionHandler.php
public function canUserManageGroup($userId, $groupId)
{
    $user = User::find($userId);
    return $user->hasRole(['admin', 'moderator']) ||
           $this->isGroupAdmin($userId, $groupId);
}
```

### **Use Case 4: Custom Message Validation**
```php
// CustomMessageValidator.php
public function validate($data)
{
    $errors = [];
    
    if (strlen($data['message']) > $this->getUserLimit($data['from_id'])) {
        $errors[] = 'Message length exceeds your limit';
    }
    
    if ($this->containsSpam($data['message'])) {
        $errors[] = 'Message contains spam content';
    }
    
    return ['valid' => empty($errors), 'errors' => $errors];
}
```

## 🔒 **Security Features**

### **Permission System**
- User-to-user messaging permissions
- Group creation and management permissions
- File upload permissions
- API access permissions

### **Content Moderation**
- Custom message validation
- Content filtering
- Spam detection
- Inappropriate content blocking

### **Access Control**
- Custom middleware support
- Route-level permissions
- Controller-level permissions
- Service-level permissions

## 📈 **Performance Features**

### **Caching System**
- Configurable cache drivers
- Custom cache prefixes
- Configurable TTL
- Cache invalidation

### **Database Optimization**
- Custom database connections
- Query optimization support
- Custom migration support
- Database prefixing

### **File Handling**
- Custom storage disks
- File processing optimization
- Metadata extraction
- Custom file validation

## 🚀 **Scalability Features**

### **Service Architecture**
- Extensible service layer
- Custom service injection
- Service method overriding
- Base service inheritance

### **Event System**
- Custom event broadcasting
- Channel-based messaging
- Custom event data
- Event customization

### **API System**
- Custom response formatting
- Rate limiting support
- API versioning
- Custom endpoints

## 📚 **Documentation & Examples**

The package includes comprehensive documentation:

1. **Customization Guide** (`CUSTOMIZATION_GUIDE.md`)
   - Complete customization examples
   - Step-by-step implementation
   - Best practices
   - Troubleshooting guide

2. **Example Classes** (`src/Examples/`)
   - `CustomUserScope.php` - User filtering examples
   - `CustomUserService.php` - User service examples
   - Ready-to-use customization templates

3. **Base Classes** (`src/Library/`)
   - `BaseChatService.php` - Extensible base service
   - Default implementations
   - Override points

## ✅ **Benefits of This Approach**

### **For Package Users**
- **Complete Control**: Override any aspect of the package
- **Easy Integration**: Integrate with existing systems
- **Flexible Logic**: Implement custom business rules
- **Maintainable**: Clean separation of concerns

### **For Package Developers**
- **Extensible**: Easy to add new features
- **Maintainable**: Clear customization points
- **Documented**: Comprehensive customization guide
- **Testable**: Testable customization system

## 🔄 **Migration Path**

### **From Default to Custom**
1. **Start Simple**: Use environment variables for basic customization
2. **Extend Classes**: Create custom classes that extend base classes
3. **Override Methods**: Override specific methods as needed
4. **Custom Logic**: Implement custom business logic
5. **Test Thoroughly**: Test all customizations

### **Backward Compatibility**
- All default functionality remains intact
- Customizations are additive, not replacing
- Default implementations provide fallbacks
- Gradual migration supported

## 🎯 **Conclusion**

The Laravel Pigeon Chat package now provides **unprecedented customization capabilities** while maintaining:

- ✅ **Backward Compatibility**
- ✅ **Easy Integration**
- ✅ **Comprehensive Documentation**
- ✅ **Example Implementations**
- ✅ **Flexible Architecture**
- ✅ **Performance Optimization**
- ✅ **Security Features**
- ✅ **Scalability Support**

Package installers can now:
- **Override any default behavior**
- **Implement custom business logic**
- **Integrate with existing systems**
- **Customize user management**
- **Control messaging permissions**
- **Customize group management**
- **Implement custom validation**
- **Add custom events**
- **Customize views and layouts**
- **Add custom routes and middleware**
- **Implement custom file handling**
- **Add custom notifications**
- **Customize API responses**

This makes the package suitable for **any Laravel application** regardless of complexity or specific requirements.
