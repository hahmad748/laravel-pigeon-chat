<p align="center"><img src="/src/assets/imgs/devsfort.png" alt="devsfort logo"></p>
# DEVSFORT CHAT WITH SOCKET.IO
A Laravel package that allows you to add a complete user messaging system into your new/existing Laravel application with only a few easy steps.

# Requirements
- PHP >=7
- Laravel >= 7.4

# Features
 
 - Users chat system.
 - Real-time contacts list updates.
 - Favorites contacts list (Like stories style) and add to favorite button.
 - Saved Messages to save your messages online like Telegram messenger app.
 - Search functionality.
 - Contact item's last message indicator (e.g. You: ....).
 - Real-time user's active status.
 - Real-time typing indicator.
 - Real-time seen messages indicator.
 - Real-time internet connection status.
 - Upload attachments (Photo/File).
 - Shared photos, delete conversation.. (User's info right side).
 - Responsive design with all devices.
 - User settings and chat customization : user's profile photo, dark mode and chat color.
   with simple and wonderful UI design.
- Dark & Light modes available so everyone is happy 😁


# Installation
Video Tutorial on YouTube - [Click Here](https://youtube.com)

OR

Follow the steps below :



#### 1. Install the package in your Laravel app

**Quick Note:** If you are installing this package in a new project, make sure to install the default user authentication system provided with [Laravel](https://laravel.com/docs).

```sh
$ composer require devsfort/laravel-pigeon-chat
```

#### 2. Publishing Assets
Packages' assets to be published :<br/>
The Important assets:
- config
- assets
- migrations

 and the optional assets :
- views

to publish the assets, do the following command line with changing the tag value .. that means after `--tag=` write `devschat-` + asset name as mentioned above.<br/>
Example :
```sh
$ php artisan vendor:publish --tag=devschat-config
```
* NOTE: Publishing assets means (e.g. config) that creating a copy of the package's config file into the `config` folder of your Laravel applications and like so with the other asstes (Package's Views, controllers, migrations ...). 

#### 3. Migrations
Migrate the new `migrations` that added by the previous step 
```sh
$ php artisan migrate
```
#### 4. Storage Symlink
Create a shortcut or a symlink to the `storage` folder into the `public` folder
```sh
$ php artisan storage:link
```

#### 5. App config
For Laravel `<=v5.4` that doesn't support package auto-discovery, add the following provider into `config/app.php` providers array list :
```php
...
/*
* Package Service Providers...
*/
\DevsFort\Pigeon\Chat\ChatServiceProvider::class,
...
```

and the following alias into  into `config/app.php` aliases:
```php
...
/*
* Class Aliases
*/
\'DevsFortChat' =>DevsFort\Pigeon\Chat\Facade\Chat::class,
...
```

 * After installing the package, you can access the messenger by the default path(route path) which is `/devschat`, and you can change path name in the config file `config/devschat.php` as mentioned in the `configurations` below.
##### That's it .. Enjoy :)

<br/>

# Configurations
You can find and modify the default configurations of the package at `config/devschat.php` file that you published in the step 2 of the installation steps .. and all configurations is documented well to be understood by other developers.
* All package’s files is documented to understand the whole code.

### Messenger Name
This value is the name of the app which is used in the views or elsewhere in the app.
```sh
...
'name' => env('DevsFort_NAME', 'Devsfort Messenger'),
...
```

### Messenger Path in Your App
This value is the path of the package or in other meaning, it is the prefix of all the registered routes in this package.
 `e.g (yourapp.domain/devschat)`
```sh
...
'path' => env('DevsFort_PATH', 'devschat'),
...
```


### Package's web routes middleware
This value is the middleware of all routes registered in this package which is by default : `auth`.
```sh
...
'middleware' => env('DevsFort_MIDDLEWARE', 'auth'),
...
```


### User Avatar
This is the user's avatar setting that includes :
```sh
...
'user_avatar' => [
        'folder' => 'users-avatar',
        ...
    ],
...
```
which is the default folder name to upload and get user's avatar from.

```sh
...
'user_avatar' => [
        ...
        'default' => 'avatar.png',
    ],
...
```
which is the default avatar file name for users stored in database .. and when you publishing `assets`, a copy of the avatar photo will be copied into your storage path.

### Attachments By Default
This array contains the important default values that used in this package :
```sh
...
'attachments' => [
        'folder' => 'attachments',
        ...
    ],
...
```
This is the default folder name for `attachments` in the storage which is all the attachments will be stored in .. and also going to be used in attachments urls in the views.

```sh
...
'attachments' => [
        ...
        'route' => 'attachments.download',
    ],
...
```
It is the route name of the `download attachments` method.

### Controller's namespace
This property if you may need to change the namespace of the route's controllers of this package after publishing the 'controllers' asset, from the default one to your App's controllers namespace.

By default: `DevsFort\Pigeon\Chat\Http\Controllers` <br/>
If published to be modified, it should be like: `App\Http\Controllers\vendor\DevsFort`

```sh
...
'namespace' => env('DevsFort_ROUTES_NAMESPACE', 'DevsFort\Pigeon\Chat\Http\Controllers'),
```

### Start NODE SERVER
- After Asset publishing, `server.js` file will be created at `public/js/devschat/server.js`
- Start Server by typing ``` node server.js ```
- Server will start on port specified in env file or config


## License
[MIT](https://choosealicense.com/licenses/mit/)

