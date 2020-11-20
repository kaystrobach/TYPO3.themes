<?php

namespace KayStrobach\Themes\Utilities;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * Class TsParserUtility.
 *
 * Provides an API to the complex TSParser
 */
class TsParserUtility implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
     */
    protected $tsParser;
    protected $tsParserTplRow;
    protected $tsParserConstants;
    protected $tsParserInitialized;

    /**
     * @param $pid
     * @param array $constants
     * @param array $isSetConstants
     *
     * @return void
     */
    public function applyToPid($pid, array $constants, $isSetConstants = [])
    {
        $this->initializeTsParser($pid);
        $this->setConstants($pid, $constants, $isSetConstants);
        //@todo add hook to apply additional options
    }

    /**
     * @param $pid
     *
     * @return mixed
     */
    public function getConstants($pid)
    {
        $this->initializeTsParser($pid);

        $return = $this->tsParserConstants;
        if (is_array($return)) {
            foreach ($return as $key => $field) {
                $return[$key]['isDefault'] = ($field['value'] === $field['default_value']);

                if ($field['type'] === 'int+') {
                    $return[$key]['typeCleaned'] = 'Int';
                } elseif (substr($field['type'], 0, 3) === 'int') {
                    $return[$key]['typeCleaned'] = 'Int';
                    $return[$key]['range'] = substr($field['type'], 3);
                } elseif ($field['type'] === 'small') {
                    $return[$key]['typeCleaned'] = 'Text';
                } elseif ($field['type'] === 'color') {
                    $return[$key]['typeCleaned'] = 'Color';
                } elseif ($field['type'] === 'boolean') {
                    $return[$key]['typeCleaned'] = 'Boolean';
                } elseif ($field['type'] === 'string') {
                    $return[$key]['typeCleaned'] = 'String';
                } elseif (substr($field['type'], 0, 4) === 'file') {
                    $return[$key]['typeCleaned'] = 'File';
                } elseif (substr($field['type'], 0, 7) === 'options') {
                    $return[$key]['typeCleaned'] = 'Options';
                    $options = explode(',', substr($field['type'], 8, -1));
                    $return[$key]['options'] = [];
                    foreach ($options as $option) {
                        $t = explode('=', $option);
                        if (count($t) === 2) {
                            $return[$key]['options'][$t[1]] = $t[0];
                        } else {
                            $return[$key]['options'][$t[0]] = $t[0];
                        }
                    }
                } elseif ($field['type'] === '') {
                    $return[$key]['typeCleaned'] = 'String';
                } else {
                    $return[$key]['typeCleaned'] = 'Fallback';
                }
            }

            return $return;
        }
    }

    /**
     * @param $pid
     * @param array $categoriesToShow
     * @param array $deniedFields
     *
     * @return array
     */
    public function getCategories($pid, $categoriesToShow = [], $deniedFields = [])
    {
        $this->initializeTsParser($pid);

        foreach ($this->tsParser->categories as $categoryName => $category) {
            if ((count($categoriesToShow) === 0) || (in_array($categoryName, $categoriesToShow))) {
                foreach (array_keys($category) as $constantName) {
                    if (in_array($constantName, $deniedFields)) {
                        unset($this->tsParser->categories[$categoryName][$constantName]);
                    }
                }
            } else {
                unset($this->tsParser->categories[$categoryName]);
            }
        }

        return $this->tsParser->categories;
    }

    /**
     * @param $pid
     *
     * @return array
     */
    public function getSubCategories($pid)
    {
        $this->initializeTsParser($pid);

        return $this->tsParser->subCategories;
    }

    /**
     * @param $pid
     *
     * @return \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
     */
    protected function getTsParser($pid)
    {
        $this->initializeTsParser($pid);

        return $this->tsParser;
    }

    /**
     * @todo access check!
     *
     * @param $pid
     * @param $constants
     * @param array $isSetConstants
     *
     * @return void
     */
    protected function setConstants($pid, $constants, $isSetConstants = [])
    {
        $this->getConstants($pid);

        $filteredConstants = [];
        /*
        foreach($constants as $constant) {
            foreach($this->tsParserConstants as $allowedConstants) {
                if($constant['name'] == $allowedConstants['name']) {
                    $filteredConstants[] = $constant;
                    break;
                }
            }
        }
        */
        $filteredConstants = $constants;

        $postData = [
            'data'  => $constants,
            'check' => $isSetConstants,
        ];

        $this->tsParser->changed = 0;
        //$this->tsParser->ext_dontCheckIssetValues = 1;
        $this->tsParser->ext_procesInput($postData, $_FILES, $this->tsParserConstants, $this->tsParserTplRow);

        if ($this->tsParser->changed) {
            // Set the data to be saved
            $saveId = $this->tsParserTplRow['uid'];
            $recData = [];
            $recData['sys_template'][$saveId]['constants'] = implode($this->tsParser->raw, chr(10));
            // Create new  tce-object
            /**
             * @var \TYPO3\CMS\Core\DataHandling\DataHandler
             */
            $tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
            $tce->stripslashes_values = 0;

            /*
             * Save data and clear the cache
             * (note: currently only admin-users can clear the cache)
             */
            $user = clone $GLOBALS['BE_USER'];
            $user->user['admin'] = 1;
            $tce->start($recData, [], $user);
            $tce->admin = 1;
            $tce->process_datamap();
            $tce->clear_cacheCmd('pages');
            unset($user);
        }
    }

    /**
     * @param $pageId
     * @param int $templateUid
     *
     * @return bool
     */
    protected function initializeTsParser($pageId, $templateUid = 0)
    {
        if (!$this->tsParserInitialized) {
            $this->tsParserInitialized = true;
            $this->tsParser = GeneralUtility::makeInstance('TYPO3\\CMS\Core\\TypoScript\\ExtendedTemplateService');
            // Do not log time-performance information
            $this->tsParser->tt_track = 0;

            $this->tsParser->ext_localGfxPrefix = ExtensionManagementUtility::extPath('tstemplate');
            $this->tsParser->ext_localWebGfxPrefix = $GLOBALS['BACK_PATH'].PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('tstemplate'));

            $this->tsParserTplRow = $this->tsParser->ext_getFirstTemplate($pageId, $templateUid);

            if (is_array($this->tsParserTplRow)) {
                /** @var \TYPO3\CMS\Frontend\Page\PageRepository $sysPageRepository */
                $sysPageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
                $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageId);
                $rootLine = $rootlineUtility->get();
                // This generates the constants/config + hierarchy info for the template.
                $this->tsParser->runThroughTemplates($rootLine, $templateUid);
                // The editable constants are returned in an array.
                $this->tsParserConstants = $this->tsParser->generateConfig_constants();
                // The returned constants are sorted in categories, that goes into the $tmpl->categories array
                $this->tsParser->ext_categorizeEditableConstants($this->tsParserConstants);
                $this->tsParser->ext_regObjectPositions($this->tsParserTplRow['constants']);
                // This array will contain key=[expanded constantname], value=linenumber in template. (after edit_divider, if any)
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}
