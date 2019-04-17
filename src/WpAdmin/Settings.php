<?php declare(strict_types=1);

namespace Dwnload\WpRestApi\WpAdmin;

use TheFrosty\WpUtilities\Models\BaseModel;

/**
 * Class Settings
 * @package Dwnload\WpRestApi\WpAdmin
 */
class Settings extends BaseModel
{

    const BYPASS = 'bypass';
    const EXPIRATION = 'expiration';
    const LENGTH = 'length';
    const PERIOD = 'period';

    /**
     * Settings array.
     *
     * @var array $settings
     */
    protected $settings = [];

    /**
     * Get's the expiration settings array.
     * @return array
     */
    public function getSettings() : array
    {
        return $this->settings;
    }

    /**
     * Sets the expiration length.
     * @param int $length
     */
    public function setLength(int $length)
    {
        $this->settings[self::EXPIRATION][self::LENGTH] = $length;
    }

    /**
     * Sets the expiration period.
     * @param int $period
     */
    public function setPeriod(int $period)
    {
        $this->settings[self::EXPIRATION][self::PERIOD] = $period;
    }

    /**
     * Sets the bypass value.
     * @param string $value
     */
    public function setBypass(string $value)
    {
        $this->settings[self::BYPASS] = $value;
    }
}
