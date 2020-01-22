<?php

namespace DesignMyNight\Mongodb\Passport\Bridge;

use Laravel\Passport\Bridge\RefreshTokenRepository as BaseRefreshTokenRepository;
use Laravel\Passport\Events\RefreshTokenCreated;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use MongoDB\BSON\UTCDateTime;

/**
 * Class RefreshTokenRepository
 * @package App\Passport\Bridge
 */
class RefreshTokenRepository extends BaseRefreshTokenRepository
{
    /**
     * @inheritDoc
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->refreshTokenRepository->create([
            'id' => $id = $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => new UTCDateTime($refreshTokenEntity->getExpiryDateTime()->getTimestamp() * 1000),
        ]);

        $this->events->dispatch(new RefreshTokenCreated($id, $accessTokenId));
    }
}
