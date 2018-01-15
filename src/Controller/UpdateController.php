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

namespace PhpMob\SettingsBundle\Controller;

use Doctrine\Common\Collections\Collection;
use PhpMob\Settings\Model\SettingInterface;
use PhpMob\Settings\Schema\Section;
use PhpMob\SettingsBundle\Form\Type\SectionUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class UpdateController extends Controller
{
    /**
     * @param string $section
     *
     * @return Section
     */
    private function getSectionSchema(string $section)
    {
        try {
            return $this->get('phpmob.settings.schema_registry')->getSection($section);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @return Collection|SettingInterface[]
     */
    private function getGlobalSettings()
    {
        return $this->get('phpmob.settings.composite_provider')->findGlobalSettings();
    }

    /**
     * @return Collection|SettingInterface[]
     */
    private function getUserSettings()
    {
        return $this->get('phpmob.settings.composite_provider')->findUserSettings((string)$this->getUser());
    }

    /**
     * @param string $section
     * @param Collection $collection
     *
     * @return SettingInterface[]
     */
    private function getSectionedSettings(string $section, Collection $collection)
    {
        return $collection->filter(
            function (SettingInterface $setting) use ($section) {
                return $section === $setting->getSection();
            }
        )->toArray();
    }

    /**
     * @param SettingInterface $setting
     *
     * @return mixed
     */
    private function transformSettingValue(SettingInterface $setting)
    {
        return $this->get('phpmob.settings.transformer')
            ->reverse($setting->getSection(), $setting->getKey(), $setting->getValue());
    }

    /**
     * @param SettingInterface[] $settings
     *
     * @return array|null
     */
    private function createSettingData(array $settings)
    {
        if (empty($settings)) {
            return null;
        }

        $data = [];

        foreach ($settings as $setting) {
            $data[$setting->getKey()] = $this->transformSettingValue($setting);
        }

        return $data;
    }

    /**
     * @param Section $section
     * @param Collection $settings
     *
     * @return FormInterface
     */
    private function createSettingForm(Section $section, Collection $settings)
    {
        $data = $this->createSettingData($this->getSectionedSettings($section->getName(), $settings));

        return $this->get('form.factory')->createNamed('', SectionUpdateType::class, $data, [
            'section' => $section,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getRequestConfiguration(Request $request)
    {
        return [
            'template' => $request->get('_template', '@PhpMobSettings/default.html.twig'),
            'redirect' => $request->get('_redirect', $request->headers->get('referer')),
            'flash' => $request->get('_flash', 'phpmob.settings_update_successfuly'),
            'isHtml' => 'html' === $request->getRequestFormat(),
        ];
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function createFlashTranslateMessage(string $message)
    {
        $translator = $this->get('translator');
        $isTranlateKey = $message !== $translated = $translator->trans($message, [], 'flashes');

        if ($isTranlateKey) {
            return $translated;
        }

        return ucwords(trim(preg_replace('/phpmob|\.|_/', ' ', $message)));
    }

    /**
     * @param string|array $redirect
     *
     * @return RedirectResponse
     */
    private function createRedirectResponse($redirect)
    {
        if (!is_array($redirect) && preg_match('/\//', $redirect)) {
            return $this->redirect($redirect);
        }

        if (!is_array($redirect)) {
            $redirect = ['route' => $redirect, 'parameters' => []];
        }

        return $this->redirectToRoute($redirect['route'], $redirect['parameters']);
    }

    /**
     * @param Section $section
     * @param array $settings
     * @param null|string $owner
     */
    private function updateSettings(Section $section, array $settings = [], ?string $owner = null)
    {
        $manager = $this->get('phpmob.settings.manager');

        foreach ($settings as $key => $value) {
            $manager->setSetting($section->getName(), $key, $value, $owner);
        }

        $manager->flush();
    }

    /**
     * @param Request $request
     * @param Section $section
     * @param null|string $owner
     *
     * @return Response
     */
    public function update(Request $request, Section $section, ?string $owner = null)
    {
        $form = $this->createSettingForm($section, $owner ? $this->getUserSettings() : $this->getGlobalSettings());
        $config = $this->getRequestConfiguration($request);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $this->updateSettings($section, $form->getData(), $owner);

            if (!$config['isHtml']) {
                return JsonResponse::create(null, 204);
            }

            $this->addFlash('success', $this->createFlashTranslateMessage($config['flash']));

            return $this->createRedirectResponse($config['redirect']);
        }

        if ($owner) {
            $allSections = $this->get('phpmob.settings.schema_registry')->getOwners();
        } else {
            $allSections = $this->get('phpmob.settings.schema_registry')->getGlobals();
        }

        if (!$config['isHtml']) {
            return JsonResponse::create([
                'section' => $section,
                'sections' => $allSections,
            ]);
        }

        return $this->render($config['template'], [
            'section' => $section,
            'sections' => $allSections,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param string $section
     *
     * @return Response
     */
    public function globalUpdateAction(Request $request, string $section)
    {
        $section = $this->getSectionSchema($section);

        if ($section->isOwnerAware()) {
            throw new NotFoundHttpException("Wrong setting section update.");
        }

        return $this->update($request, $section);
    }

    /**
     * @param Request $request
     * @param string $section
     *
     * @return Response
     */
    public function userUpdateAction(Request $request, string $section)
    {
        $section = $this->getSectionSchema($section);

        if (!$section->isOwnerAware()) {
            throw new NotFoundHttpException("Wrong setting section update.");
        }

        if (!$this->isGranted($request->attributes->get('_role', 'ROLE_USER'))) {
            throw new AccessDeniedHttpException("Access Denied!");
        }

        return $this->update($request, $section, (string)$this->getUser());
    }
}
