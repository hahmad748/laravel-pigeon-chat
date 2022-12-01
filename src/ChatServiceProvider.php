<?php

namespace DevsFort\Pigeon\Chat;
use Illuminate\Support\Facades\Route;
use DevsFort\Pigeon\Chat\Library\DevsFortChat;
use Illuminate\Support\ServiceProvider;
use PhpParser\Node\Scalar\MagicConst\Dir;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('DevsFortChat', function () {
            return new DevsFortChat();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load Views, Migrations and Routes
        $this->loadViewsFrom(__DIR__ . '/views', 'DevsFort');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadRoutes();

        // Publishes
        $this->setPublishes();
    }

    /**
     * Publishing the files that the user may override.
     *
     * @return void
     */
    protected function setPublishes()
    {
        // Config


        $this->publishes([
            __DIR__ . '/config/devschat.php' => config_path('devschat.php')
        ], 'devschat-config');

        // Migrations
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'devschat-migrations');

        // Controllers
        $this->publishes([
            __DIR__ . '/Http/Controllers' => app_path('Http/Controllers/vendor/DevsFort')
        ], 'devschat-controllers');


        // Views
        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/DevsFort')
        ], 'devschat-views');

        // Assets
        $this->publishes([
            // CSS
            __DIR__ . '/assets/css' => public_path('css/devschat'),
            // JavaScript
            __DIR__ . '/assets/js' => public_path('js/devschat'),
            // Images
            __DIR__ . '/assets/imgs' => storage_path('app/public/' . config('devschat.user_avatar.folder'))
        ], 'devschat-assets');
    }
    /**
     * Group the routes and set up configurations to load them.
     *
     * @return void
     */
    protected function loadRoutes()
    {
//        dd($this->routesConfigurations());
        Route::group($this->routesConfigurations(), function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        });
    }

    /**
     * Routes configurations.
     *
     * @return array
     */
    private function routesConfigurations()
    {

        return [
            'prefix' => config('devschat.path'),
            'namespace' =>  config('devschat.namespace'),
            'middleware' => ['web','auth'],

            ];
    }
}