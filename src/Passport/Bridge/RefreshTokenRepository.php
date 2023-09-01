<?php

namespace Sysvale\Mongodb\Passport\Bridge;

use Laravel\Passport\Bridge\RefreshTokenRepository as BaseRefreshTokenRepository;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\Passport;
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
        return Passport::refreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = $this->database->table('oauth_refresh_tokens')
            ->where('id', $tokenId)->first();

        return $refreshToken === null || $refreshToken['revoked'];
    }

    /**
     * @param RefreshToken|RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshTokenEntity->newModelQuery()->create([
            'id' => $id = $refreshTokenEntity->getIdentifier(),
            'access_token_id' => $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
        ]);

        $this->events->dispatch(new RefreshTokenCreated($id, $accessTokenId));
    }
}
