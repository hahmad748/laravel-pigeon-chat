<p align="center"><img src="/src/assets/imgs/devsfort.png" alt="devsfort logo"></p>
# Laravel Pigeon Chat

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devsfort/laravel-pigeon-chat.svg)](https://packagist.org/packages/devsfort/laravel-pigeon-chat)
[![Total Downloads](https://img.shields.io/packagist/dt/devsfort/laravel-pigeon-chat.svg)](https://packagist.org/packages/devsfort/laravel-pigeon-chat)
[![License](https://img.shields.io/packagist/l/devsfort/laravel-pigeon-chat.svg)](https://packagist.org/packages/devsfort/laravel-pigeon-chat)

A **highly customizable** Laravel package that provides a complete real-time chat system with individual messaging, group chat, and extensive customization capabilities. Built with modern technologies including Socket.IO, Redis, and Laravel's broadcasting system.

## 📑 **Table of Contents**

- [🚀 Features](#-features)
- [📋 Requirements](#-requirements)
- [🛠 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [🎯 Quick Start](#-quick-start)
- [🔧 Customization](#-customization)
- [🏗 Architecture](#-architecture)
- [📊 Database Schema](#-database-schema)
- [🚀 Real-time Features](#-real-time-features)
- [🔒 Security Features](#-security-features)
- [📈 Performance & Scalability](#-performance--scalability)
- [🧪 Testing](#-testing)
- [🔧 Troubleshooting](#-troubleshooting)
- [📚 Documentation](#-documentation)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [👨‍💻 Author](#️-author)
- [🙏 Acknowledgments](#-acknowledgments)
- [🔄 Changelog](#-changelog)
- [📞 Support](#-support)

## 🚀 **Features**

### **Core Chat Functionality**
- ✅ **Individual User Chat** - One-to-one messaging between users
- ✅ **Group Chat** - Multi-user group conversations with admin controls
- ✅ **Real-time Messaging** - Instant message delivery using Socket.IO
- ✅ **File Attachments** - Support for images, documents, and other files
- ✅ **Message Status** - Read receipts and delivery confirmations
- ✅ **Typing Indicators** - Real-time typing notifications
- ✅ **User Status** - Online/offline status tracking
- ✅ **Search & Favorites** - Message search and favorite conversations
- ✅ **Dark/Light Mode** - User preference themes
- ✅ **Responsive Design** - Mobile-friendly chat interface

### **Advanced Features**
- ✅ **Channel-Based Broadcasting** - Separate channels for user and group chats
- ✅ **Room Management** - Efficient Socket.IO room handling
- ✅ **Permission System** - Granular control over user actions
- ✅ **Content Moderation** - Customizable message validation
- ✅ **Caching System** - Configurable caching for performance
- ✅ **API Support** - RESTful API endpoints with rate limiting
- ✅ **Notification System** - Multi-channel notifications
- ✅ **Database Optimization** - Efficient queries and relationships

### **Customization & Extensibility**
- ✅ **Complete Override System** - Override any aspect of the package
- ✅ **Custom User Logic** - Custom user filtering and permissions
- ✅ **Custom Message Logic** - Custom validation and filtering
- ✅ **Custom Group Logic** - Custom group creation and management
- ✅ **Custom Events** - Custom broadcasting and event handling
- ✅ **Custom Services** - Extensible service layer architecture
- ✅ **Custom Controllers** - Extend and customize controllers
- ✅ **Custom Views** - Complete UI customization
- ✅ **Custom Routes** - Custom routing and middleware
- ✅ **Custom Database** - Custom migrations and connections

## 📋 **Requirements**

- **PHP**: >= 7.4
- **Laravel**: >= 8.0
- **Node.js**: >= 14.0 (for Socket.IO server)
- **Redis**: For broadcasting and caching
- **Database**: MySQL, PostgreSQL, or SQLite

## 🛠 **Installation**

### 1. **Install Package via Composer**

```bash
composer require devsfort/laravel-pigeon-chat
```

### 2. **Publish Configuration and Assets**

```bash
# Publish configuration
php artisan vendor:publish --tag=devschat-config

# Publish migrations
php artisan vendor:publish --tag=devschat-migrations

# Publish views (optional)
php artisan vendor:publish --tag=devschat-views

# Publish controllers (optional)
php artisan vendor:publish --tag=devschat-controllers

# Publish assets
php artisan vendor:publish --tag=devschat-assets

# Publish Node.js server file
php artisan vendor:publish --tag=devschat-server
```

### 3. **Run Migrations**

```bash
php artisan migrate
```

### 4. **Install Node.js Dependencies**

```bash
# Install dependencies for Socket.IO server
npm install express socket.io ioredis

# Or use the provided package.json
cp package-chat.json package.json
npm install
```

### 5. **Configure Broadcasting**

Update your `.env` file:

```bash
BROADCAST_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
```

### 6. **Start Socket.IO Server**

```bash
# Start the chat server
node server.js
```

## ⚙️ **Configuration**

### **Environment Variables**

The package is highly configurable through environment variables:

```bash
# Basic Configuration
DEVSFORT_NAME="Your Chat App Name"
DEVSFORT_PATH="chat"
DEVSFORT_MIDDLEWARE="auth"

# Socket Configuration
SOCKET_HOST="127.0.0.1"
SOCKET_PORT="8005"

# Redis Configuration
REDIS_CLIENT="predis"
REDIS_HOST="127.0.0.1"
REDIS_PORT="6379"
REDIS_DB="0"

# File Upload Configuration
DEVSFORT_ATTACHMENTS_FOLDER="chat-attachments"
DEVSFORT_ATTACHMENTS_MAX_SIZE="150000000"
DEVSFORT_ALLOWED_IMAGES="png,jpg,jpeg,gif"
DEVSFORT_ALLOWED_FILES="zip,rar,txt,pdf"

# Avatar Configuration
DEVSFORT_USER_AVATAR_FOLDER="user-avatars"
DEVSFORT_USER_AVATAR_DEFAULT="default-avatar.png"
DEVSFORT_GROUP_AVATAR_FOLDER="group-avatars"
DEVSFORT_GROUP_AVATAR_DEFAULT="default-group.png"

# Group Configuration
DEVSFORT_GROUPS_MAX_MEMBERS="100"
DEVSFORT_GROUPS_ALLOW_PRIVATE="true"
DEVSFORT_GROUPS_ALLOW_REMOVAL="true"
DEVSFORT_GROUPS_ALLOW_PROMOTION="true"

# Cache Configuration
DEVSFORT_CACHE_ENABLED="true"
DEVSFORT_CACHE_DRIVER="redis"
DEVSFORT_CACHE_PREFIX="chat"
DEVSFORT_CACHE_TTL="3600"

# API Configuration
DEVSFORT_API_ENABLED="true"
DEVSFORT_API_VERSION="v1"
DEVSFORT_API_RATE_LIMIT="60"
```

### **Customization Configuration**

For advanced customization, see the [Customization Guide](CUSTOMIZATION_GUIDE.md):

```bash
# User Customization
DEVSFORT_USER_MODEL="App\Models\CustomUser"
DEVSFORT_USER_SCOPE="App\Services\CustomUserScope"
DEVSFORT_USER_SERVICE="App\Services\CustomUserService"

# Message Customization
DEVSFORT_MESSAGE_VALIDATOR="App\Validators\CustomMessageValidator"
DEVSFORT_MESSAGE_FILTER="App\Filters\CustomMessageFilter"

# Group Customization
DEVSFORT_GROUP_VALIDATOR="App\Validators\CustomGroupValidator"
DEVSFORT_GROUP_PERMISSION_HANDLER="App\Handlers\CustomGroupPermissionHandler"

# Service Customization
DEVSFORT_CHAT_SERVICE="App\Services\CustomChatService"
DEVSFORT_GROUP_SERVICE="App\Services\CustomGroupService"

# Controller Customization
DEVSFORT_MESSAGES_CONTROLLER="App\Http\Controllers\CustomMessagesController"

# View Customization
DEVSFORT_VIEW_LAYOUT="layouts.custom-chat"
DEVSFORT_VIEW_CHAT="pages.custom-chat"

# Route Customization
DEVSFORT_CUSTOM_ROUTES="routes/custom-chat.php"
DEVSFORT_MIDDLEWARE_CUSTOM='["custom.chat","throttle:60,1"]'
```

## 🎯 **Quick Start**

### **Basic Usage**

1. **Access Chat Interface**
   ```
   http://your-app.com/chat
   ```

2. **Send Messages**
   - Click on any user to start a conversation
   - Type your message and press Enter
   - Attach files by clicking the attachment button

3. **Create Groups**
   - Click on the "Groups" tab
   - Click "Create Group"
   - Add members and set group settings

### **API Usage**

```php
// Send a message
$response = Http::post('/chat/sendMessage', [
    'id' => $userId,
    'message' => 'Hello!',
    'type' => 'user'
]);

// Create a group
$response = Http::post('/chat/createGroup', [
    'name' => 'My Group',
    'description' => 'Group description',
    'members' => [1, 2, 3],
    'is_private' => false
]);
```

## 🔧 **Customization**

### **Why Customize?**

The package is designed to be **highly customizable** because:

- **Business Logic**: Every application has unique requirements
- **User Management**: Different user models and permission systems
- **Content Rules**: Custom validation and moderation needs
- **Integration**: Must work with existing systems
- **Scalability**: Adapt to growing application needs

### **Customization Methods**

#### **1. Environment Variables (Simple)**
```bash
# Override basic settings
DEVSFORT_USER_MODEL="App\Models\CustomUser"
DEVSFORT_MESSAGE_VALIDATOR="App\Validators\CustomValidator"
```

#### **2. Class Extension (Recommended)**
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
}
```

#### **3. Interface Implementation (Advanced)**
```php
<?php
namespace App\Validators;

class CustomMessageValidator
{
    public function validate($data)
    {
        // Custom validation logic
        return ['valid' => true, 'errors' => []];
    }
}
```

### **Customization Examples**

See the [Examples Directory](src/Examples/) for complete implementation examples:

- **`CustomUserScope.php`** - User filtering examples
- **`CustomUserService.php`** - User service examples
- **`CustomMessageValidator.php`** - Message validation examples
- **`CustomGroupValidator.php`** - Group validation examples

### **Complete Customization Guide**

For comprehensive customization information, see:

- **[📖 Customization Guide](CUSTOMIZATION_GUIDE.md)** - Complete step-by-step guide with examples
- **[📋 Customization Summary](CUSTOMIZATION_SUMMARY.md)** - Overview of all customization features
- **[💡 Examples Directory](src/Examples/)** - Ready-to-use customization examples

> **💡 Pro Tip**: Start with the [Customization Summary](CUSTOMIZATION_SUMMARY.md) for an overview, then dive into the [Customization Guide](CUSTOMIZATION_GUIDE.md) for detailed implementation steps.

## 🏗 **Architecture**

### **Package Structure**

```
src/
├── ChatServiceProvider.php          # Service provider
├── config/
│   └── devschat.php                # Configuration file
├── database/
│   └── migrations/                  # Database migrations
├── Events/                          # Broadcasting events
├── Facade/
│   └── Chat.php                     # Facade for easy access
├── Http/
│   └── Controllers/                 # Controllers
├── Library/
│   ├── BaseChatService.php          # Extensible base service
│   └── DevsFortChat.php             # Default implementation
├── Models/                          # Eloquent models
├── routes/
│   └── web.php                      # Route definitions
├── views/                           # Blade templates
└── assets/                          # CSS, JS, images
```

### **Key Components**

- **`BaseChatService`** - Extensible service layer
- **`PrivateMessageEvent`** - Broadcasting events
- **`MessagesController`** - Main controller
- **`Group` & `GroupMember`** - Group management models
- **Socket.IO Server** - Real-time communication

## 📊 **Database Schema**

### **Core Tables**

- **`users`** - User information and preferences
- **`messages`** - Chat messages with attachments
- **`favorites`** - Favorite conversations
- **`groups`** - Group information
- **`group_members`** - Group membership and roles

### **Relationships**

```php
// User can have many messages
User -> hasMany(Message)

// Group has many members
Group -> belongsToMany(User, 'group_members')

// Message belongs to sender and receiver
Message -> belongsTo(User, 'from_id')
Message -> belongsTo(User, 'to_id')
```

## 🚀 **Real-time Features**

### **Socket.IO Implementation**

- **Channel-Based Broadcasting** - Separate channels for different message types
- **Room Management** - Efficient user and group room handling
- **Redis Integration** - Scalable broadcasting across multiple servers
- **Event Handling** - Customizable event processing

### **Supported Events**

- `user-chat` - Individual user messages
- `group-chat` - Group chat messages
- `user-status` - User online/offline status
- `typing-indicators` - Real-time typing notifications
- `message-seen` - Message read confirmations

## 🔒 **Security Features**

### **Built-in Security**

- **Authentication Required** - All routes protected by auth middleware
- **Permission Checking** - User action validation
- **Content Validation** - Message content filtering
- **File Upload Security** - File type and size validation
- **CSRF Protection** - Laravel's built-in CSRF protection

### **Custom Security**

- **Custom Permission Handlers** - Implement your own permission logic
- **Custom Validators** - Add custom validation rules
- **Custom Middleware** - Add custom security middleware
- **Role-Based Access** - Integrate with existing role systems

## 📈 **Performance & Scalability**

### **Performance Features**

- **Caching System** - Configurable Redis caching
- **Database Optimization** - Efficient queries and relationships
- **Asset Optimization** - Minified CSS and JavaScript
- **Lazy Loading** - On-demand data loading

### **Scalability Features**

- **Horizontal Scaling** - Multiple Socket.IO instances
- **Redis Clustering** - Support for Redis clusters
- **Load Balancing** - Distribute connections across servers
- **Database Sharding** - Support for database sharding

## 🧪 **Testing**

### **Testing the Package**

```bash
# Test basic functionality
php artisan serve
# Visit: http://localhost:8000/chat

# Test Socket.IO server
node server.js
# Server should start on port 8005

# Test Redis connection
redis-cli ping
# Should return: PONG
```

### **Custom Testing**

```php
<?php
namespace Tests;

use DevsFort\Pigeon\Chat\Library\DevsFortChat;

class ChatTest extends TestCase
{
    public function test_custom_user_service()
    {
        $chatService = new CustomChatService();
        $users = $chatService->getUsersForChat();
        
        $this->assertNotEmpty($users);
    }
}
```

## 🔧 **Troubleshooting**

### **Common Issues**

1. **Messages Not Sending**
   - Check Redis connection
   - Verify Socket.IO server is running
   - Check broadcasting configuration

2. **Users Not Loading**
   - Verify user model configuration
   - Check custom user scope implementation
   - Verify database relationships

3. **Group Creation Fails**
   - Check group validation rules
   - Verify custom group validator
   - Check database permissions

4. **Real-time Not Working**
   - Verify Socket.IO server is running
   - Check Redis configuration
   - Verify event broadcasting

### **Debug Mode**

```bash
# Enable debug mode
APP_DEBUG=true

# Check logs
tail -f storage/logs/laravel.log

# Check Redis
redis-cli monitor

# Check Socket.IO
# Monitor server.js console output
```

## 📚 **Documentation**

### **Available Documentation**

- **[README.md](README.md)** - This file (overview and quick start)
- **[CUSTOMIZATION_GUIDE.md](CUSTOMIZATION_GUIDE.md)** - Complete customization guide
- **[CUSTOMIZATION_SUMMARY.md](CUSTOMIZATION_SUMMARY.md)** - Customization features overview
- **[Examples Directory](src/Examples/)** - Ready-to-use customization examples

### **Documentation Structure**

1. **README.md** - Package overview and quick start
2. **Customization Guide** - Step-by-step customization instructions
3. **Examples** - Working code examples for all customization points
4. **Configuration Reference** - Complete configuration options

## 🤝 **Contributing**

### **How to Contribute**

1. **Fork the repository**
2. **Create a feature branch**
3. **Make your changes**
4. **Add tests if applicable**
5. **Submit a pull request**

### **Development Setup**

```bash
# Clone repository
git clone https://github.com/your-username/laravel-pigeon-chat.git

# Install dependencies
composer install
npm install

# Run tests
php artisan test

# Build assets
npm run build
```

## 📄 **License**

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 **Author**

**Haseeb Ahmad** - [haseeb@devsfort.com](mailto:haseeb@devsfort.com)

### **Professional Profile**
- **CEO & Founder** @ [Devsfort](https://devsfort.com)
- **Technical Team Lead** @ Softversum Dubai
- **Senior Consultant** @ 9t5 Private Limited Lahore
- **AWS Certified** Professional

### **Contact & Links**
- **Website**: [https://haseebahmad.com](https://haseebahmad.com)
- **Email**: [haseeb@devsfort.com](mailto:haseeb@devsfort.com)
- **Company**: [Devsfort](https://devsfort.com)

## 🙏 **Acknowledgments**

- Laravel team for the amazing framework
- Socket.IO team for real-time communication
- Redis team for fast data storage
- All contributors and users of this package

## 🔄 **Changelog**

### **Version 2.0.0** (Latest)
- ✨ **Complete Customization System** - Override any aspect of the package
- ✨ **Group Chat Support** - Full group messaging functionality
- ✨ **Channel-Based Broadcasting** - Separate channels for different message types
- ✨ **Extensible Service Layer** - Base service class for customization
- ✨ **Advanced Configuration** - 50+ configuration options
- ✨ **Comprehensive Documentation** - Complete customization guides
- ✨ **Example Implementations** - Ready-to-use customization examples

### **Version 1.0.0**
- ✨ **Basic Chat Functionality** - Individual user messaging
- ✨ **Real-time Communication** - Socket.IO integration
- ✨ **File Attachments** - Support for various file types
- ✨ **User Management** - Basic user functionality

## 📞 **Support**

### **Getting Help**

- **Documentation**: Check the customization guides first
- **Examples**: Review the example implementations
- **Issues**: Report bugs on GitHub
- **Discussions**: Ask questions in GitHub discussions

### **Support Channels**

- **GitHub Issues**: [Report bugs and request features](https://github.com/hahmad748/laravel-pigeon-chat/issues)
- **GitHub Discussions**: [Ask questions and share solutions](https://github.com/hahmad748/laravel-pigeon-chat/discussions)
- **Email**: [haseeb@devsfort.com](mailto:haseeb@devsfort.com)

---

**⭐ Star this repository if you find it helpful!**

**🔧 Need customization help?** Check the [Customization Guide](CUSTOMIZATION_GUIDE.md) for comprehensive examples and instructions.

**🚀 Ready to get started?** Follow the installation steps above and begin building your custom chat solution!
