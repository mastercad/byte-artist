<?php
namespace App\Tests\Integration\Service\Filter\Ubb;

use App\Service\Filter\Ubb\Extender;
use PHPUnit\Framework\TestCase;

class ExtenderTest extends TestCase
{
    /**
     * @covers App\Service\Filter\Ubb\Extender::retrieveYouTubeInformation
     **/
    public function testExtenderRetrieveYouTubeInformation()
    {
        $extender = new Extender;

        $content = '';

        $expected = '';

        $this->assertSame($expected, $extender->filter($content));
    }
}
