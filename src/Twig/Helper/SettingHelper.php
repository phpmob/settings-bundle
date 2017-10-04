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

namespace PhpMob\SettingsBundle\Twig\Helper;

use PhpMob\Settings\Manager\SettingManagerInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class SettingHelper extends Helper
{
    /**
     * @var SettingManagerInterface
     */
    private $settingManager;

    /**
     * @param SettingManagerInterface $settingManager
     */
    public function __construct(SettingManagerInterface $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * @param string $path
     * @param null|string $owner
     *
     * @return mixed
     */
    public function get(string $path, ?string $owner = null)
    {
        return $this->settingManager->get($path, $owner);
    }

    /**
     * @param string $path
     * @param $value
     * @param null|string $owner
     */
    public function set(string $path, $value, ?string $owner = null): void
    {
        $this->settingManager->set($path, $value, $owner);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'settings';
    }
}
