<?php

namespace DesignMyNight\Mongodb;

use DesignMyNight\Mongodb\Passport\Bridge\RefreshTokenRepository as BridgeRefreshTokenRepository;
use DesignMyNight\Mongodb\Passport\RefreshTokenRepository;
use Illuminate\Support\ServiceProvider;
use DesignMyNight\Mongodb\Passport\AuthCode;
use DesignMyNight\Mongodb\Passport\Client;
use DesignMyNight\Mongodb\Passport\PersonalAccessClient;
use DesignMyNight\Mongodb\Passport\Token;

class MongodbPassportServiceProvider extends ServiceProvider
{
    /** @var string */
    private $method = 'alias';

    /** @var array */
    private $aliases = [
        'Laravel\Passport\AuthCode' => AuthCode::class,
        'Laravel\Passport\Bridge\RefreshTokenRepository' => BridgeRefreshTokenRepository::class,
        'Laravel\Passport\Client' => Client::class,
        'Laravel\Passport\PersonalAccessClient' => PersonalAccessClient::class,
        'Laravel\Passport\RefreshTokenRepository' => RefreshTokenRepository::class,
        'Laravel\Passport\Token' => Token::class,
    ];

    /** @var Illuminate\Foundation\AliasLoader */
    private $loader;

    /**
     * @return void
     */
    public function register()
    {
        if (class_exists($loader = 'Illuminate\Foundation\AliasLoader')) {
            $this->loader = $loader::getInstance();
            $this->method = 'loadAlias';
        }

        foreach ($this->aliases as $original => $alias) {
            $this->{$this->method}($original, $alias);
        }
    }

    /**
     * @param string $original
     * @param string $alias
     */
    private function alias(string $original, string $alias): void
    {
        class_alias($original, $alias);
    }

    /**
     * @param string $original
     * @param string $alias
     */
    private function loadAlias(string $original, string $alias): void
    {
        // Passport client extends Eloquent model by default, so we alias them.
        $this->loader->alias($original, $alias);
    }
}
