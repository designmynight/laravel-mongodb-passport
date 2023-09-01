<?php

namespace Sysvale\Mongodb\Passport;

use Laravel\Passport\TokenRepository as PassportTokenRepository;

class TokenRepository extends PassportTokenRepository
{
   /**
     * Store the given token instance.
     *
     * @param  \Laravel\Passport\Token  $token
     * @return void
     */

    public function save($token)
    {
        $token->save();
    }

}
