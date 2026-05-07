<?php

namespace DesignMyNight\Mongodb;

use Illuminate\Support\ServiceProvider;
use DesignMyNight\Mongodb\Passport\AuthCode;
use DesignMyNight\Mongodb\Passport\Bridge\RefreshTokenRepository;
use DesignMyNight\Mongodb\Passport\Client;
use DesignMyNight\Mongodb\Passport\PersonalAccessClient;
use DesignMyNight\Mongodb\Passport\RefreshToken;
use DesignMyNight\Mongodb\Passport\Token;
use DesignMyNight\Mongodb\Passport\TokenRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository as PassportTokenRepository;

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

        // Passport 8+ uses useRefreshTokenModel() for refresh token validation
        // This method doesn't exist in Passport 7, so we check before calling
        if (method_exists(Passport::class, 'useRefreshTokenModel')) {
            Passport::useRefreshTokenModel(RefreshToken::class);
        }

        $this->app->bind(PassportRefreshTokenRepository::class, function () {
            return $this->app->make(RefreshTokenRepository::class);
        });

        $this->app->bind(PassportTokenRepository::class, function () {
            return $this->app->make(TokenRepository::class);
        });
    }
}
