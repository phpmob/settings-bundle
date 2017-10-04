<?php

/*
 * This file is part of the PhpMob package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMob\SettingsBundle\Twig\Extension;

use PhpMob\SettingsBundle\Twig\Helper\SettingHelper;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class SettingExtension extends \Twig_Extension
{
    /**
     * @var SettingHelper
     */
    private $settingHelper;

    /**
     * @param SettingHelper $settingHelper
     */
    public function __construct(SettingHelper $settingHelper)
    {
        $this->settingHelper = $settingHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('settings_get', [$this, 'get']),
            new \Twig_Function('settings_set', [$this, 'set']),
        ];
    }

    /**
     * @param string $path
     * @param null|string $owner
     *
     * @return mixed
     */
    public function get(string $path, ?string $owner = null)
    {
        return $this->settingHelper->get($path, $owner);
    }

    /**
     * @param string $path
     * @param $value
     * @param null|string $owner
     */
    public function set(string $path, $value, ?string $owner = null): void
    {
        $this->settingHelper->set($path, $value, $owner);
    }
}
