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
class TestWpRestApiCache extends TestCase
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

    /**
     * Test getRestDispatch.
     */
    public function testGetRestDispatch()
    {
        $rest_dispatch = $this->wp_rest_api_cache::getRestDispatch();
        $this->assertInstanceOf(RestDispatch::class, $rest_dispatch);
    }
}
