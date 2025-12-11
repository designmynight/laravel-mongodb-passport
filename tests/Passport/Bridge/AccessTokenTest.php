<?php

namespace Tests\Passport\Bridge;

use DateTime;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /** @var string */
    private $privateKeyPath;

    /** @var string */
    private $publicKeyPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->privateKeyPath = $this->createTemporaryKeyPair();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->privateKeyPath)) {
            unlink($this->privateKeyPath);
        }

        if (file_exists($this->publicKeyPath)) {
            unlink($this->publicKeyPath);
        }
    }

    private function createTemporaryKeyPair(): string
    {
        $privateKeyPath = sys_get_temp_dir() . '/oauth-private-' . uniqid() . '.key';
        $publicKeyPath = sys_get_temp_dir() . '/oauth-public-' . uniqid() . '.key';

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);
        openssl_pkey_export($privateKey, $privateKeyPem);
        file_put_contents($privateKeyPath, $privateKeyPem);

        $publicKeyDetails = openssl_pkey_get_details($privateKey);
        file_put_contents($publicKeyPath, $publicKeyDetails['key']);

        $this->publicKeyPath = $publicKeyPath;

        return $privateKeyPath;
    }

    private function createMockClient(string $identifier = 'test-client'): ClientEntityInterface
    {
        $client = $this->createMock(ClientEntityInterface::class);
        $client->method('getIdentifier')->willReturn($identifier);

        return $client;
    }

    private function createMockScope(string $identifier): ScopeEntityInterface
    {
        $scope = $this->createMock(ScopeEntityInterface::class);
        $scope->method('getIdentifier')->willReturn($identifier);
        $scope->method('jsonSerialize')->willReturn($identifier);

        return $scope;
    }

    /**
     * @test
     */
    public function it_converts_to_jwt_without_deprecation_warning(): void
    {
        $accessToken = new \DesignMyNight\Mongodb\Passport\Bridge\AccessToken('user-123', []);
        $accessToken->setIdentifier('token-id-123');
        $accessToken->setClient($this->createMockClient());
        $accessToken->setExpiryDateTime(new DateTime('+1 hour'));

        $previousErrorHandler = set_error_handler(function ($errno, $errstr) {
            if ($errno === E_USER_DEPRECATED && strpos($errstr, 'Replicating claims as headers is deprecated') !== false) {
                $this->fail('Deprecation warning was triggered: ' . $errstr);
            }

            return false;
        });

        try {
            $cryptKey = new CryptKey($this->privateKeyPath, null, false);
            $jwt = $accessToken->convertToJWT($cryptKey);

            $this->assertNotNull($jwt);
            $this->assertInstanceOf(\Lcobucci\JWT\Token::class, $jwt);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @test
     */
    public function it_includes_jti_claim_in_token(): void
    {
        $tokenId = 'token-id-456';
        $accessToken = new \DesignMyNight\Mongodb\Passport\Bridge\AccessToken('user-123', []);
        $accessToken->setIdentifier($tokenId);
        $accessToken->setClient($this->createMockClient());
        $accessToken->setExpiryDateTime(new DateTime('+1 hour'));

        $cryptKey = new CryptKey($this->privateKeyPath, null, false);
        $jwt = $accessToken->convertToJWT($cryptKey);

        $parser = new Parser();
        $parsedToken = $parser->parse((string) $jwt);

        $this->assertEquals($tokenId, $parsedToken->getClaim('jti'));
    }

    /**
     * @test
     */
    public function it_includes_jti_in_header_for_backwards_compatibility(): void
    {
        $tokenId = 'token-id-789';
        $accessToken = new \DesignMyNight\Mongodb\Passport\Bridge\AccessToken('user-123', []);
        $accessToken->setIdentifier($tokenId);
        $accessToken->setClient($this->createMockClient());
        $accessToken->setExpiryDateTime(new DateTime('+1 hour'));

        $cryptKey = new CryptKey($this->privateKeyPath, null, false);
        $jwt = $accessToken->convertToJWT($cryptKey);

        $parser = new Parser();
        $parsedToken = $parser->parse((string) $jwt);

        $this->assertEquals($tokenId, $parsedToken->getHeader('jti'));
    }

    /**
     * @test
     */
    public function it_includes_all_standard_claims(): void
    {
        $userId = 'user-123';
        $clientId = 'client-456';
        $tokenId = 'token-789';

        $accessToken = new \DesignMyNight\Mongodb\Passport\Bridge\AccessToken($userId, [
            $this->createMockScope('read'),
            $this->createMockScope('write'),
        ]);
        $accessToken->setIdentifier($tokenId);
        $accessToken->setClient($this->createMockClient($clientId));
        $accessToken->setExpiryDateTime(new DateTime('+1 hour'));

        $cryptKey = new CryptKey($this->privateKeyPath, null, false);
        $jwt = $accessToken->convertToJWT($cryptKey);

        $parser = new Parser();
        $parsedToken = $parser->parse((string) $jwt);

        $this->assertEquals($clientId, $parsedToken->getClaim('aud'));
        $this->assertEquals($tokenId, $parsedToken->getClaim('jti'));
        $this->assertEquals($userId, $parsedToken->getClaim('sub'));
        $this->assertNotNull($parsedToken->getClaim('iat'));
        $this->assertNotNull($parsedToken->getClaim('nbf'));
        $this->assertNotNull($parsedToken->getClaim('exp'));

        $scopes = $parsedToken->getClaim('scopes');
        $this->assertCount(2, $scopes);
    }

    /**
     * @test
     */
    public function it_can_be_parsed_after_creation(): void
    {
        $accessToken = new \DesignMyNight\Mongodb\Passport\Bridge\AccessToken('user-123', []);
        $accessToken->setIdentifier('token-id-123');
        $accessToken->setClient($this->createMockClient());
        $accessToken->setExpiryDateTime(new DateTime('+1 hour'));

        $cryptKey = new CryptKey($this->privateKeyPath, null, false);
        $jwt = $accessToken->convertToJWT($cryptKey);

        $tokenString = (string) $jwt;

        $parser = new Parser();
        $parsedToken = $parser->parse($tokenString);

        $this->assertNotNull($parsedToken);
        $this->assertEquals('token-id-123', $parsedToken->getClaim('jti'));
    }
}
