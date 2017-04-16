<?php

# For PHP7
// declare(strict_types=1);

// namespace Hug\Tests\Screenshot;

use PHPUnit\Framework\TestCase;

use Hug\Screenshot\Screenshot as Screenshot;

/**
 * http://stackoverflow.com/questions/24047811/how-can-i-provide-credentials-to-phpunit-tests
 */
final class ScreenshotTest extends TestCase
{    

    function __construct()
    {

        define('HUG_SCREENSHOT_SAVE_PATH', sys_get_temp_dir());
        define('HUG_SCREENSHOT_PROVIDERS', __DIR__ . '/providers.json');

    }

    /* ************************************************* */
    /* **************** Screenshot::shot *************** */
    /* ************************************************* */

    /**
     *
     */
    public function testCanShot()
    {
        $Screenshot = new Screenshot();
        $url = 'https://hugo.maugey.fr';
        $widths = ['1024', '768', '480'];

        $test = $Screenshot->shot($url, $widths);
        $this->assertInternalType('array', $test);
        $this->assertEquals(4, count($test));
        $this->assertEquals('success', $test['status']);
        $this->assertEquals(3, count($test['images']));
    }

}

