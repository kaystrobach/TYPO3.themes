<?php

namespace KayStrobach\Themes\DataProcessing;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

/**
 * DataProcessor for header link parts
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class HeaderLinkDataProcessor implements DataProcessorInterface
{
    /**
     * Process data for the header link.
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @throws ContentRenderingException
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        if (isset($processedData['data']['header_link']) && trim($processedData['data']['header_link']) !== '') {
            $typoLinkCodecService = GeneralUtility::makeInstance(TypoLinkCodecService::class);
            $processedData['headerLink'] = $typoLinkCodecService->decode($processedData['data']['header_link']);
        }

        return $processedData;
    }
}
