<?php

namespace StevePorter92\Mongodb;

use Illuminate\Support\ServiceProvider;
use StevePorter92\Mongodb\Passport\AuthCode;
use StevePorter92\Mongodb\Passport\Client;
use StevePorter92\Mongodb\Passport\PersonalAccessClient;
use StevePorter92\Mongodb\Passport\Token;

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
