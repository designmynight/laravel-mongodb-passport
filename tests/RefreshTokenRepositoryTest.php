<?php

namespace Tests;

use DesignMyNight\Mongodb\Passport\Bridge\RefreshTokenRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Laravel\Passport\RefreshTokenRepository as PassportRefreshTokenRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class RefreshTokenRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that isRefreshTokenRevoked returns true for a revoked token.
     * 
     * This test demonstrates the fix for jenssegers/mongodb which returns arrays
     * instead of objects due to its typeMap configuration.
     */
    public function testIsRefreshTokenRevokedReturnsTrueForRevokedToken()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')
            ->with('id', 'token-id')
            ->andReturnSelf();
        $builder->shouldReceive('first')
            ->andReturn(['id' => 'token-id', 'revoked' => true]);

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('table')
            ->with('oauth_refresh_tokens')
            ->andReturn($builder);

        $passportRefreshTokenRepository = Mockery::mock(PassportRefreshTokenRepository::class);
        $events = Mockery::mock(Dispatcher::class);

        $repository = new RefreshTokenRepository($passportRefreshTokenRepository, $events, $connection);

        $result = $repository->isRefreshTokenRevoked('token-id');

        $this->assertTrue($result);
    }

    /**
     * Test that isRefreshTokenRevoked returns false for a token that exists and is NOT revoked.
     */
    public function testIsRefreshTokenRevokedReturnsFalseForNonRevokedToken()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')
            ->with('id', 'token-id')
            ->andReturnSelf();
        $builder->shouldReceive('first')
            ->andReturn(['id' => 'token-id', 'revoked' => false]);

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('table')
            ->with('oauth_refresh_tokens')
            ->andReturn($builder);

        $passportRefreshTokenRepository = Mockery::mock(PassportRefreshTokenRepository::class);
        $events = Mockery::mock(Dispatcher::class);

        $repository = new RefreshTokenRepository($passportRefreshTokenRepository, $events, $connection);

        $result = $repository->isRefreshTokenRevoked('token-id');

        $this->assertFalse($result);
    }

    /**
     * Test that isRefreshTokenRevoked returns true when the token does not exist.
     */
    public function testIsRefreshTokenRevokedReturnsTrueForNonExistentToken()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')
            ->with('id', 'non-existent-token')
            ->andReturnSelf();
        $builder->shouldReceive('first')
            ->andReturn(null);

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('table')
            ->with('oauth_refresh_tokens')
            ->andReturn($builder);

        $passportRefreshTokenRepository = Mockery::mock(PassportRefreshTokenRepository::class);
        $events = Mockery::mock(Dispatcher::class);

        $repository = new RefreshTokenRepository($passportRefreshTokenRepository, $events, $connection);

        $result = $repository->isRefreshTokenRevoked('non-existent-token');

        $this->assertTrue($result);
    }
}
