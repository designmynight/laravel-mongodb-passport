<?php

namespace Sysvale\Mongodb\Passport\Bridge;

use Jenssegers\Mongodb\Eloquent\Model;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

/**
 * Class RefreshToken
 * @package Sysvale\Mongodb\Passport\Bridge
 */
class RefreshToken extends Model implements RefreshTokenEntityInterface
{
    use EntityTrait, RefreshTokenTrait;

    /**
     * @var string[]
     */
    protected $casts = [
        'revoked' => 'bool',
    ];

    /**
     * @var string[]
     */
    protected $dates = ['expires_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $table = 'oauth_refresh_tokens';

    /**
     * @var bool
     */
    public $timestamps = false;
}
