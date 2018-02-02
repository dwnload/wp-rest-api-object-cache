<?php

namespace Dwnload\WpRestApi\WpAdmin;

use TheFrosty\WP\Utils\Models\BaseModel;

/**
 * Class Settings
 * @package Dwnload\WpRestApi\WpAdmin
 */
class Settings extends BaseModel {

    const EXPIRATION = 'expiration';
    const LENGTH = 'length';
    const PERIOD = 'period';

    /**
     * Settings expiration array.
     *
     * @var array $expiration
     */
    protected $expiration;

    public function getExpiration() : array {
        return $this->expiration;
    }

    /**
     * @param int $length
     */
    public function setLength( int $length ) {
        $this->expiration[ self::EXPIRATION ][ self::LENGTH ] = $length;

    }

    /**
     * @param int $period
     */
    public function setPeriod( int $period ) {
        $this->expiration[ self::EXPIRATION ][ self::PERIOD ] = $period;
    }
}