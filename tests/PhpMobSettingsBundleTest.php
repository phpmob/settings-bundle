<?php

namespace tests\PhpMob\SettingsBundle;

use PhpMob\SettingsBundle\DependencyInjection\PhpMobSettingsExtension;
use PhpMob\SettingsBundle\PhpMobSettingsBundle;
use PHPUnit\Framework\TestCase;

class PhpMobSettingsBundleTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_construct_with_right_extension()
    {
        $bundle = new PhpMobSettingsBundle();

        $this->assertInstanceOf(PhpMobSettingsExtension::class, $bundle->getContainerExtension());
    }
}
