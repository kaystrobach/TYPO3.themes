<?php

namespace KayStrobach\Themes\Tca;

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

use KayStrobach\Themes\Domain\Model\Theme;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ThemeSelector.
 */
class ThemeSelector
{
    public function items(&$PA, $fobj)
    {
        /**
         * @var \KayStrobach\Themes\Domain\Repository\ThemeRepository
         */
        $repository = GeneralUtility::makeInstance('KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository');

        $themes = $repository->findAll();

        $PA['items'] = [
            [
                0 => 'None',
                1 => '',
            ],
        ];

        /** @var Theme $theme */
        foreach ($themes as $theme) {
            $PA['items'][] = [
                0 => $theme->getTitle(),
                1 => $theme->getExtensionName(),
                2 => $theme->getPreviewImage(),
            ];
        }
    }
}
