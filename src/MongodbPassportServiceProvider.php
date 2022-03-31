<?php

namespace Sysvale\Mongodb;

use Illuminate\Support\ServiceProvider;
use Sysvale\Mongodb\Passport\AuthCode;
use Sysvale\Mongodb\Console\ClientCommand;
use Sysvale\Mongodb\Passport\Bridge\RefreshTokenRepository;
use Sysvale\Mongodb\Passport\Client;
use Sysvale\Mongodb\Passport\PersonalAccessClient;
use Sysvale\Mongodb\Passport\Token;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Laravel\Passport\Console\ClientCommand as PassportClientCommand;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository as PassportTokenRepository;
use Sysvale\Mongodb\Passport\TokenRepository;

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

        $this->app->extend(PassportClientCommand::class, function () {
            return new ClientCommand();
        });

        $this->app->bind(PassportTokenRepository::class, function () {
            return $this->app->make(TokenRepository::class);
        });
    }
}
