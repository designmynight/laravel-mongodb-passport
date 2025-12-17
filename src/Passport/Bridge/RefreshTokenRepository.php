<?php

namespace DesignMyNight\Mongodb\Passport\Bridge;

use DesignMyNight\Mongodb\Passport\RefreshToken as RefreshTokenModel;
use Laravel\Passport\Bridge\RefreshTokenRepository as BaseRefreshTokenRepository;
use Laravel\Passport\Events\RefreshTokenCreated;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * Class RefreshTokenRepository
 * @package App\Passport\Bridge
 */
class RefreshTokenRepository extends BaseRefreshTokenRepository
{
    /**
     * @inheritDoc
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = RefreshTokenModel::where('_id', $tokenId)->first();

        return $refreshToken === null || $refreshToken->revoked;
    }

    /**
     * @param RefreshToken|RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshTokenEntity->newModelQuery()->create([
            '_id' => $id = $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);

        $this->events->dispatch(new RefreshTokenCreated($id, $accessTokenId));
    }
}
