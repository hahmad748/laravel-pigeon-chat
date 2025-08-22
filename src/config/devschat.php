<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Messenger app name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the app which is used in the views or elsewhere
    | in this app.
    |
    */
    'name' => env('DEVSFORT_NAME', 'DevsFort Messenger'),

    /*
    |--------------------------------------------------------------------------
    | Package path
    |--------------------------------------------------------------------------
    |
    | This value is the path of the package or in other meaning, it is the prefix
    | of all the registered routes in this package.
    |
    | e.g. : app.test/devschat
    |
    */
    'path' => env('DEVSFORT_PATH', 'devschat'),

    /*
    |--------------------------------------------------------------------------
    | Package's web routes middleware
    |--------------------------------------------------------------------------
    |
    | This value is the middleware of all routes registered in this package
    | which is by default : auth
    |
    */
    'middleware' => env('DEVSFORT_MIDDLEWARE', 'auth'),

    /*
    |--------------------------------------------------------------------------
    | REDIS credentials
    |--------------------------------------------------------------------------
    |
    | This array includes all the credentials that required to use pusher API
    | with Chatty package, which is used to broadcast events over websockets to
    | create a real-time features.
    |
    */
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),
        'cluster' => false,
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Socket configuration
    |--------------------------------------------------------------------------
    |
    | This array includes socket server configuration for real-time messaging.
    |
    */
    'socket' => [
        'host' => env('SOCKET_HOST','127.0.0.1'),
        'port' =>  env('SOCKET_PORT','8005'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Avatar
    |--------------------------------------------------------------------------
    |
    | This is the user's avatar setting that includes :
    | [folder]  which is the default folder name to upload and get
    |           user's avatar from.
    | [default] which is the default avatar file name for users stored
    |           in database.
    |
    */
    'user_avatar' => [
        'folder' => env('DEVSFORT_USER_AVATAR_FOLDER', 'users-avatar'),
        'default' => env('DEVSFORT_USER_AVATAR_DEFAULT', 'avatar.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Group Avatar
    |--------------------------------------------------------------------------
    |
    | This is the group's avatar setting that includes :
    | [folder]  which is the default folder name to upload and get
    |           group's avatar from.
    | [default] which is the default avatar file name for groups stored
    |           in database.
    |
    */
    'group_avatar' => [
        'folder' => env('DEVSFORT_GROUP_AVATAR_FOLDER', 'groups-avatar'),
        'default' => env('DEVSFORT_GROUP_AVATAR_DEFAULT', 'default-group.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachments By Default
    |--------------------------------------------------------------------------
    |
    | This array contains the important default values that used in this package.
    |
    | The first value in this array is the default folder name in the storage
    | which is all the attachments will be stored in.
    | This is also going to be used in attachments urls in the views.
    |
    */
    'attachments' => [
        'folder' => env('DEVSFORT_ATTACHMENTS_FOLDER', 'attachments'),
        // Below is the route name to download attachments.
        'route' => env('DEVSFORT_ATTACHMENTS_ROUTE', 'attachments.download'),
        'max_size' => env('DEVSFORT_ATTACHMENTS_MAX_SIZE', 150000000), // 150MB in bytes
    ],

    /*
    |--------------------------------------------------------------------------
    | Group Chat Settings
    |--------------------------------------------------------------------------
    |
    | These values configure group chat functionality.
    |
    */
    'groups' => [
        'max_members' => env('DEVSFORT_GROUPS_MAX_MEMBERS', 100),
        'allow_private_groups' => env('DEVSFORT_GROUPS_ALLOW_PRIVATE', true),
        'allow_member_removal' => env('DEVSFORT_GROUPS_ALLOW_REMOVAL', true),
        'allow_admin_promotion' => env('DEVSFORT_GROUPS_ALLOW_PROMOTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customization & Override Settings
    |--------------------------------------------------------------------------
    |
    | These settings allow you to override default package behavior
    | and implement custom logic for various functionalities.
    |
    */
    'customization' => [
        /*
        |--------------------------------------------------------------------------
        | User Model & Query Customization
        |--------------------------------------------------------------------------
        |
        | Override the default user model and query logic
        |
        */
        'user_model' => env('DEVSFORT_USER_MODEL', 'App\Models\User'),
        
        'user_query_builder' => env('DEVSFORT_USER_QUERY_BUILDER', null), // Custom query builder class
        
        'user_scope' => env('DEVSFORT_USER_SCOPE', null), // Custom scope class for user filtering
        
        /*
        |--------------------------------------------------------------------------
        | Message Customization
        |--------------------------------------------------------------------------
        |
        | Override message handling and validation logic
        |
        */
        'message_model' => env('DEVSFORT_MESSAGE_MODEL', 'DevsFort\Pigeon\Chat\Models\Message'),
        
        'message_validator' => env('DEVSFORT_MESSAGE_VALIDATOR', null), // Custom validation class
        
        'message_filter' => env('DEVSFORT_MESSAGE_FILTER', null), // Custom message filtering class
        
        /*
        |--------------------------------------------------------------------------
        | Group Customization
        |--------------------------------------------------------------------------
        |
        | Override group handling and membership logic
        |
        */
        'group_model' => env('DEVSFORT_GROUP_MODEL', 'DevsFort\Pigeon\Chat\Models\Group'),
        
        'group_member_model' => env('DEVSFORT_GROUP_MEMBER_MODEL', 'DevsFort\Pigeon\Chat\Models\GroupMember'),
        
        'group_validator' => env('DEVSFORT_GROUP_VALIDATOR', null), // Custom group validation class
        
        'group_permission_handler' => env('DEVSFORT_GROUP_PERMISSION_HANDLER', null), // Custom permission logic
        
        /*
        |--------------------------------------------------------------------------
        | Event Customization
        |--------------------------------------------------------------------------
        |
        | Override default events and broadcasting logic
        |
        */
        'events' => [
            'message_sent' => env('DEVSFORT_EVENT_MESSAGE_SENT', 'DevsFort\Pigeon\Chat\Events\PrivateMessageEvent'),
            'user_status' => env('DEVSFORT_EVENT_USER_STATUS', 'DevsFort\Pigeon\Chat\Events\UserStatusEvent'),
            'typing_indicator' => env('DEVSFORT_EVENT_TYPING', 'DevsFort\Pigeon\Chat\Events\TypingIndicatorEvent'),
            'message_seen' => env('DEVSFORT_EVENT_MESSAGE_SEEN', 'DevsFort\Pigeon\Chat\Events\MessageSeenEvent'),
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Service Customization
        |--------------------------------------------------------------------------
        |
        | Override default service classes
        |
        */
        'services' => [
            'chat_service' => env('DEVSFORT_CHAT_SERVICE', 'DevsFort\Pigeon\Chat\Library\DevsFortChat'),
            'user_service' => env('DEVSFORT_USER_SERVICE', null), // Custom user service class
            'group_service' => env('DEVSFORT_GROUP_SERVICE', null), // Custom group service class
            'message_service' => env('DEVSFORT_MESSAGE_SERVICE', null), // Custom message service class
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Controller Customization
        |--------------------------------------------------------------------------
        |
        | Override default controller classes
        |
        */
        'controllers' => [
            'messages_controller' => env('DEVSFORT_MESSAGES_CONTROLLER', 'DevsFort\Pigeon\Chat\Http\Controllers\MessagesController'),
        ],
        
        /*
        |--------------------------------------------------------------------------
        | View Customization
        |--------------------------------------------------------------------------
        |
        | Override default view paths and components
        |
        */
        'views' => [
            'layout' => env('DEVSFORT_VIEW_LAYOUT', 'DevsFort::layouts.app'),
            'chat_interface' => env('DEVSFORT_VIEW_CHAT', 'DevsFort::pages.app'),
            'message_card' => env('DEVSFORT_VIEW_MESSAGE_CARD', 'DevsFort::layouts.messageCard'),
            'contact_item' => env('DEVSFORT_VIEW_CONTACT_ITEM', 'DevsFort::layouts.listItem'),
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Route Customization
        |--------------------------------------------------------------------------
        |
        | Override default route definitions
        |
        */
        'routes' => [
            'enabled' => env('DEVSFORT_ROUTES_ENABLED', true),
            'custom_routes_file' => env('DEVSFORT_CUSTOM_ROUTES', null), // Path to custom routes file
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Middleware Customization
        |--------------------------------------------------------------------------
        |
        | Override default middleware
        |
        */
        'middleware' => [
            'auth' => env('DEVSFORT_MIDDLEWARE_AUTH', 'auth'),
            'custom' => env('DEVSFORT_MIDDLEWARE_CUSTOM', []), // Array of custom middleware
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Database Customization
        |--------------------------------------------------------------------------
        |
        | Override default database configuration
        |
        */
        'database' => [
            'connection' => env('DEVSFORT_DB_CONNECTION', 'default'),
            'prefix' => env('DEVSFORT_DB_PREFIX', ''),
            'custom_migrations' => env('DEVSFORT_CUSTOM_MIGRATIONS', false), // Use custom migration files
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Cache Customization
        |--------------------------------------------------------------------------
        |
        | Override default caching behavior
        |
        */
        'cache' => [
            'enabled' => env('DEVSFORT_CACHE_ENABLED', true),
            'driver' => env('DEVSFORT_CACHE_DRIVER', 'redis'),
            'prefix' => env('DEVSFORT_CACHE_PREFIX', 'devschat'),
            'ttl' => env('DEVSFORT_CACHE_TTL', 3600), // Time to live in seconds
        ],
        
        /*
        |--------------------------------------------------------------------------
        | File Upload Customization
        |--------------------------------------------------------------------------
        |
        | Override default file upload behavior
        |
        */
        'file_upload' => [
            'disk' => env('DEVSFORT_FILE_DISK', 'public'),
            'max_size' => env('DEVSFORT_FILE_MAX_SIZE', 150000000), // 150MB
            'allowed_types' => [
                'images' => env('DEVSFORT_ALLOWED_IMAGES', 'png,jpg,jpeg,gif'),
                'files' => env('DEVSFORT_ALLOWED_FILES', 'zip,rar,txt,pdf'),
            ],
            'custom_processor' => env('DEVSFORT_FILE_PROCESSOR', null), // Custom file processing class
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Notification Customization
        |--------------------------------------------------------------------------
        |
        | Override default notification behavior
        |
        */
        'notifications' => [
            'enabled' => env('DEVSFORT_NOTIFICATIONS_ENABLED', true),
            'channels' => env('DEVSFORT_NOTIFICATION_CHANNELS', ['mail', 'database']),
            'custom_notification_class' => env('DEVSFORT_CUSTOM_NOTIFICATION', null),
        ],
        
        /*
        |--------------------------------------------------------------------------
        | API Customization
        |--------------------------------------------------------------------------
        |
        | Override default API behavior
        |
        */
        'api' => [
            'enabled' => env('DEVSFORT_API_ENABLED', true),
            'version' => env('DEVSFORT_API_VERSION', 'v1'),
            'rate_limiting' => env('DEVSFORT_API_RATE_LIMIT', 60), // Requests per minute
            'custom_response_formatter' => env('DEVSFORT_API_RESPONSE_FORMATTER', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route's controllers namespace
    |--------------------------------------------------------------------------
    |
    | You may need to change the namespace of the route's controllers of
    | this package after publishing the 'controllers' asset, from the
    | default one to your App's controllers namespace.
    |
    | By default: DevsFort\Pigeon\Chat\Http\Controllers
    |
    */
    'namespace' => env('DEVSFORT_ROUTES_NAMESPACE', 'DevsFort\Pigeon\Chat\Http\Controllers'),
];
