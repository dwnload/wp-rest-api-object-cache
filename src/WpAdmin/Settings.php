<?php declare(strict_types=1);

namespace Dwnload\WpRestApi\WpAdmin;

use TheFrosty\WpUtilities\Models\BaseModel;

/**
 * Class Settings
 * @package Dwnload\WpRestApi\WpAdmin
 */
class Settings extends BaseModel
{

    const EXPIRATION = 'expiration';
    const LENGTH = 'length';
    const PERIOD = 'period';

    /**
     * Settings expiration array.
     *
     * @var array $expiration
     */
    protected $expiration = [];

    /**
     * Get's the expiration settings array.
     * @return array
     */
    public function getExpiration() : array
    {
        return $this->expiration;
    }

    /**
     * Sets the expiration length.
     * @param int $length
     */
    public function setLength(int $length)
    {
        $this->expiration[self::EXPIRATION][self::LENGTH] = $length;
    }

    /**
     * Sets the expiration period.
     * @param int $period
     */
    public function setPeriod(int $period)
    {
        $this->expiration[self::EXPIRATION][self::PERIOD] = $period;
    }
}
