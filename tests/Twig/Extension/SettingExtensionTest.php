<?php

namespace tests\PhpMob\SettingsBundle\Twig\Extension;

use PhpMob\SettingsBundle\Twig\Extension\SettingExtension;
use PhpMob\SettingsBundle\Twig\Helper\SettingHelper;
use PHPUnit\Framework\TestCase;

class SettingExtensionTest extends TestCase
{
    public function testGetFunctions()
    {
        $helper = $this->getMockBuilder(SettingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig = new SettingExtension($helper);

        $this->assertInstanceOf('\Twig_SimpleFunction', $twig->getFunctions()[0]);
    }

    public function testGet()
    {
        $helper = $this->getMockBuilder(SettingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper
            ->expects(self::any())
            ->method('get')
            ->willReturn('bar')
            ;

        $twig = new SettingExtension($helper);

        $this->assertEquals('bar', $twig->get('section.foo'));
    }

    public function testSet()
    {
        $helper = $this->getMockBuilder(SettingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper
            ->expects(self::any())
            ->method('set')
            ->willReturn(null)
        ;

        $twig = new SettingExtension($helper);

        $this->assertEquals(null, $twig->set('section.foo', 'bar'));
    }
}
