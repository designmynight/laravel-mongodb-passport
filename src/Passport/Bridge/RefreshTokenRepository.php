<?php

namespace DesignMyNight\Mongodb\Passport\Bridge;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Laravel\Passport\Bridge\RefreshTokenRepository as BaseRefreshTokenRepository;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\RefreshTokenRepository as PassportRefreshTokenRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * Class RefreshTokenRepository
 * @package App\Passport\Bridge
 */
class RefreshTokenRepository extends BaseRefreshTokenRepository
{
    /**
     * The database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * Create a new repository instance.
     *
     * @param  \Laravel\Passport\RefreshTokenRepository  $refreshTokenRepository
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Database\Connection  $database
     * @return void
     */
    public function __construct(PassportRefreshTokenRepository $refreshTokenRepository, Dispatcher $events, Connection $database)
    {
        parent::__construct($refreshTokenRepository, $events);
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
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

    /**
     * Check if the refresh token has been revoked.
     *
     * This method overrides the base implementation to use array access
     * instead of object property access, which is required for jenssegers/mongodb
     * due to its typeMap configuration returning arrays instead of objects.
     *
     * @param string $tokenId
     * @return bool
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = $this->database->table('oauth_refresh_tokens')
            ->where('id', $tokenId)->first();

        return $refreshToken === null || $refreshToken['revoked'];
    }
}
