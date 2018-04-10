<?php
/**
 * Created by PhpStorm.
 * User: apassy
 * Date: 2/15/18
 * Time: 8:39 AM
 */

namespace Dwnload\WpRestApi\Tests;

use Dwnload\WpRestApi\RestApi\RestDispatch;
use Dwnload\WpRestApi\WpRestApiCache;
use PHPUnit\Framework\TestCase;

/**
 * Class TestWpRestApiCache
 * @package Dwnload\WpRestApi\Tests
 */
class WpRestApiCacheTest extends TestCase
{

    /**
     * @var WpRestApiCache $wp_rest_api_cache
     */
    private $wp_rest_api_cache;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->wp_rest_api_cache = new WpRestApiCache();
    }

    public function tearDown()
    {
        unset($this->wp_rest_api_cache);
    }

    /**
     * Test class has constants.
     */
    public function testConstants()
    {
        $expected = [
            WpRestApiCache::FILTER_PREFIX,
            WpRestApiCache::ID,
        ];
        $constants = $this->getReflection()->getConstants();
        $this->assertNotEmpty($constants);
        $this->assertSame($expected, array_values($constants));
    }

    /**
     * Test getRestDispatch.
     */
    public function testGetRestDispatch()
    {
        $rest_dispatch = WpRestApiCache::getRestDispatch();
        $this->assertInstanceOf(RestDispatch::class, $rest_dispatch);
    }

    /**
     * Gets an instance of the \ReflectionObject.
     *
     * @return \ReflectionObject
     */
    private function getReflection() : \ReflectionObject
    {
        static $reflector;

        if (! ($reflector instanceof \ReflectionObject)) {
            $reflector = new \ReflectionObject($this->wp_rest_api_cache);
        }

        return $reflector;
    }
}
