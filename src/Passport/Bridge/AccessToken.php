<?php

namespace DesignMyNight\Mongodb\Passport\Bridge;

use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    /**
     * Create a new token instance.
     *
     * @param string|int $userIdentifier
     * @param array $scopes
     */
    public function __construct($userIdentifier, array $scopes = [])
    {
        $this->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }
    }

    /**
     * Generate a JWT from the access token.
     *
     * This method overrides the default implementation from AccessTokenTrait
     * to avoid the deprecated replicateAsHeader functionality in lcobucci/jwt.
     *
     * @param CryptKey $privateKey
     * @return Token
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        $now = new DateTimeImmutable();
        $expiresAt = new DateTimeImmutable('@' . $this->getExpiryDateTime()->getTimestamp());

        $builder = (new Builder())
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier())
            ->withHeader('jti', $this->getIdentifier())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->relatedTo($this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes());

        return $builder->getToken(
            new Sha256(),
            new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase())
        );
    }
}
