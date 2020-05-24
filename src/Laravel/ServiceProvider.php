<?php

declare(strict_types=1);
/**
 * This file is part of qbhy/simple-jwt.
 *
 * @link     https://github.com/qbhy/simple-jwt
 * @document https://github.com/qbhy/simple-jwt/blob/master/README.md
 * @contact  qbhy0715@qq.com
 * @license  https://github.com/qbhy/simple-jwt/blob/master/LICENSE
 */
namespace Qbhy\SimpleJwt\Laravel;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Qbhy\SimpleJwt\JWTManager;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        // Base64UrlSafeEncoder
    }

    public function register()
    {
        $this->setupConfig();

        $this->app->singleton(JWTManager::class, function () {
            return new JWTManager(config('simple-jwt'));
        });
    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $configSource = realpath(__DIR__ . '/config/simple-jwt.php');
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                $configSource => base_path('config/simple-jwt.php'),
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('simple-jwt');
        }
        $this->mergeConfigFrom($configSource, 'simple-jwt');
    }
}
