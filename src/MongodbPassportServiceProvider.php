<?php

namespace DesignMyNight\Mongodb;

use DesignMyNight\Mongodb\Console\ClientCommand;
use Illuminate\Support\ServiceProvider;
use DesignMyNight\Mongodb\Passport\AuthCode;
use DesignMyNight\Mongodb\Passport\Bridge\RefreshTokenRepository;
use DesignMyNight\Mongodb\Passport\Client;
use DesignMyNight\Mongodb\Passport\PersonalAccessClient;
use DesignMyNight\Mongodb\Passport\Token;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Laravel\Passport\Console\ClientCommand as OriginalClientCommand;
use Laravel\Passport\Passport;

class MongodbPassportServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::useClientModel(Client::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        Passport::useTokenModel(Token::class);

        $this->app->bind(PassportRefreshTokenRepository::class, function () {
            return $this->app->make(RefreshTokenRepository::class);
        });

        $this->app->extend(OriginalClientCommand::class, function () {
            return new ClientCommand();
        });
    }
}
