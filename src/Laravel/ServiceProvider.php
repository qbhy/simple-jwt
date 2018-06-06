<?php
/**
 * User: qbhy
 * Date: 2018/5/31
 * Time: 下午2:30
 */

namespace Qbhy\SimpleJwt\Laravel;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Qbhy\SimpleJwt\Encoders\Base64UrlSafeEncoder;
use Qbhy\SimpleJwt\EncryptAdapters\PasswordHashEncrypter;
use Qbhy\SimpleJwt\JWT;
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

            $config = config('simple-jwt');

            JWT::setSupportAlgos($config['algo']['providers'] ?? []);

            $encoder = $config['encoder'] ?? Base64UrlSafeEncoder::class;

            $encrypter = JWT::getSupportAlgos()[$config['algo']['default'] ?? PasswordHashEncrypter::alg()];

            return (new JWTManager(new $encrypter($config['secret']), new $encoder))
                ->setTtl($config['ttl'] ?? 60)
                ->setRefreshTtl($config['refresh_ttl'] ?? 20160);
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
                $configSource => config_path('simple-jwt.php')
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('simple-jwt');
        }
        $this->mergeConfigFrom($configSource, 'simple-jwt');

    }
}