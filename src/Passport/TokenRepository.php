<?php

namespace DesignMyNight\Mongodb\Passport;

use Laravel\Passport\TokenRepository as PassportTokenRepository;

class TokenRepository extends PassportTokenRepository
{
    /**
     * Store the given token instance.
     *
     * Overrides the base method to remove the Laravel\Passport\Token type hint,
     * allowing MongoDB-compatible Token models to be saved.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $token
     * @return void
     */
    public function save($token)
    {
        $token->save();
    }
}
