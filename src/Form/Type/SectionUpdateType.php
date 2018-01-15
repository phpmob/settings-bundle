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

namespace PhpMob\SettingsBundle\Form\Type;

use PhpMob\Settings\Schema\Section;
use PhpMob\Settings\Schema\SettingSchema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class SectionUpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $settings = $options['section']->getSettings();

        /** @var SettingSchema $setting */
        foreach ($settings as $setting) {
            if (!$setting->isEnabled()) {
                continue;
            }

            $blueprint = $setting->getBlueprint();
            $blueprintOptions = $blueprint->getOptions();
            $blueprintOptions['constraints'] = [];

            foreach ($blueprint->getConstraints() as $class => $constraintOptions) {
                if (!class_exists($class)) {
                    $class = sprintf("Symfony\\Component\\Validator\\Constraints\\".$class);
                }

                $blueprintOptions['constraints'][] = new $class($constraintOptions);
            }

            $type = $blueprint->getType();

            if (get_parent_class($type) === AbstractBlueprintType::class) {
                $blueprintOptions['description'] = $setting->getDescription();
            }

            $builder->add($setting->getKey(), $type, array_merge([
                'label' => $setting->getLabel(),
                'required' => false,
            ], $blueprintOptions));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('section');
        $resolver->setAllowedTypes('section', Section::class);
    }
}
