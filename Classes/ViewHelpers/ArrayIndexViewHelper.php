<?php

namespace KayStrobach\Themes\ViewHelpers;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Thomas Deuling <typo3@coding.ms>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * can be used to access array keys or object properties dynamically.
 *
 * {themes:arrayIndex(object: results, index: 'key')}
 */
class ArrayIndexViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('object', 'mixed', 'Array or Object', true);
        $this->registerArgument('index', 'string', 'Index or property');
    }

    /**
     *
     * @return mixed
     */
    public function render()
    {
        $object = $this->arguments['object'];
        $index = $this->arguments['index'];
        if (is_object($object)) {
            if (property_exists($object, $index)) {
                return $object->$index;
            }
            try {
                $reflectionClass = new \ReflectionClass($object);
                $reflectionProperty = $reflectionClass->getProperty($index);
                $reflectionProperty->setAccessible(true);
                return $reflectionProperty->getValue($object);
            } catch (\Exception $e) {
                // we want a silent fail here, as the default will return null
            }
        } elseif (is_array($object)) {
            if (array_key_exists($index, $object)) {
                return $object[$index];
            }
        }
        return false;
    }
}
