<?php

namespace tests\PhpMob\SettingsBundle\Twig\Helper;

use PhpMob\Settings\Manager\SettingManagerInterface;
use PhpMob\SettingsBundle\Twig\Helper\SettingHelper;
use PHPUnit\Framework\TestCase;

class SettingHelperTest extends TestCase
{
    public function testGet()
    {
        $manager = $this->getMockBuilder(SettingManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager
            ->expects(self::any())
            ->method('get')
            ->willReturn('bar');

        $helper = new SettingHelper($manager);

        self::assertEquals('bar', $helper->get('section.foo'));
    }

    public function testSet()
    {
        $manager = $this->getMockBuilder(SettingManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new SettingHelper($manager);

        self::assertEquals(null, $helper->set('section.foo', 'bar'));
    }

    public function testGetName()
    {
        $manager = $this->getMockBuilder(SettingManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new SettingHelper($manager);

        self::assertEquals('settings', $helper->getName());
    }
}
