<?php

namespace KayStrobach\Themes\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ThemeController.
 */
class ThemeController extends ActionController
{
    /**
     * @var string
     */
    protected $templateName = '';

    /**
     * @var array
     */
    protected $typoScriptSetup;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository
     */
    protected $themeRepository;

    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->typoScriptSetup = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
    }

    /**
     * @param \KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository
     * @return void
     */
    public function injectThemeRepository(\KayStrobach\Themes\Domain\Repository\ThemeRepository $themeRepository) {
        $this->themeRepository = $themeRepository;
    }

    /**
     * @param \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository
     * @return void
     */
    public function injectPageRepository(\TYPO3\CMS\Frontend\Page\PageRepository $pageRepository) {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return void
     */
    public function initializeAction()
    {
        $this->themeRepository = new \KayStrobach\Themes\Domain\Repository\ThemeRepository();
    }

    /**
     * renders the given theme.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->templateName = $this->evaluateTypoScript('plugin.tx_themes.settings.templateName');
        $templateFile = $this->getTemplateFile();
        if ($templateFile !== null) {
            $this->view->setTemplatePathAndFilename($templateFile);
        }
        $this->view->assign('templateName', $this->templateName);

        $frontendController = $this->getFrontendController();
        $this->view->assign('theme', $this->themeRepository->findByPageOrRootline($frontendController->id));
        // Get page data
        $pageArray = $this->pageRepository->getPage($frontendController->id);
        $pageArray['icon'] = '';
        // Map page icon
        if (isset($pageArray['tx_themes_icon']) && $pageArray['tx_themes_icon'] != '') {
            $setup = $frontendController->tmpl->setup;
            $pageArray['icon'] = $setup['lib.']['icons.']['cssMap.'][$pageArray['tx_themes_icon']];
        }
        // Get settings, because they aren't available
        $configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'Themes');
        unset($configuration['settings']['templateName']);
        $this->view->assign('settings', $configuration['settings']);
        $this->view->assign('page', $pageArray);
        $this->view->assign('data', $pageArray);
        $this->view->assign('TSFE', $frontendController);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * renders a given TypoScript Path.
     *
     * @param $path
     *
     * @return string
     */
    protected function evaluateTypoScript($path)
    {
        if (version_compare(TYPO3_version, '9.0', '>=')) {
            $pathSegments = GeneralUtility::trimExplode('.', $path);
            $lastSegment = array_pop($pathSegments);

            if (!empty($pathSegments) && is_array($pathSegments)) {
                $setup = $this->typoScriptSetup;

                foreach ($pathSegments as $segment) {
                    if (!array_key_exists($segment . '.', $setup)) {
                        return '';
                    }
                    $setup = $setup[$segment . '.'];
                }

                return $GLOBALS['TSFE']->cObj->cObjGetSingle($setup[$lastSegment], $setup[$lastSegment . '.']);
            }

            return '';
        } else {
            /** @var \TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper $vh */
            $vh = $this->objectManager->get('TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper');
            $closure = function () {
                return '';
            };
            $vh->setRenderChildrenClosure($closure);
            if (version_compare(TYPO3_version, '8.0', '<')) {
                return $vh->render($path);
            } else {
                $vh->setArguments(['typoscriptObjectPath' => $path]);

                return $vh->render();
            }
        }
    }

    /**
     * gets a TS Array by path.
     *
     * @param $typoscriptObjectPath
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     *
     * @return array
     */
    protected function getTsArrayByPath($typoscriptObjectPath)
    {
        $pathSegments = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('.', $typoscriptObjectPath);
        $setup = $this->typoScriptSetup;
        foreach ($pathSegments as $segment) {
            if (!array_key_exists(($segment.'.'), $setup)) {
                throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('TypoScript object path "'.htmlspecialchars($typoscriptObjectPath).'" does not exist', 1253191023);
            }
            $setup = $setup[$segment.'.'];
        }

        return $setup;
    }

    /**
     * get the needed templateFile from TS.
     *
     * @return null|string
     */
    protected function getTemplateFile()
    {
        $templatePaths = $this->getTsArrayByPath('plugin.tx_themes.view.templateRootPaths');
        krsort($templatePaths);
        foreach ($templatePaths as $templatePath) {
            $cleanedPath = GeneralUtility::getFileAbsFileName($templatePath).'Theme/'.$this->templateName.'.html';
            if (is_file($cleanedPath)) {
                return $cleanedPath;
            }
        }
    }
}
