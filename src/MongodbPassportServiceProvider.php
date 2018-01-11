<?php

namespace DesignMyNight\Mongodb;

use Illuminate\Support\ServiceProvider;
use DesignMyNight\Mongodb\Passport\AuthCode;
use DesignMyNight\Mongodb\Passport\Client;
use DesignMyNight\Mongodb\Passport\PersonalAccessClient;
use DesignMyNight\Mongodb\Passport\Token;

class MongodbPassportServiceProvider extends ServiceProvider
{
    public function register()
    {
        /*
         * Passport client extends Eloquent model by default, so we alias them.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Laravel\Passport\AuthCode', AuthCode::class);
        $loader->alias('Laravel\Passport\Client', Client::class);
        $loader->alias('Laravel\Passport\PersonalAccessClient', PersonalAccessClient::class);
        $loader->alias('Laravel\Passport\Token', Token::class);
    }
}
