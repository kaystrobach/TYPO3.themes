<?php

namespace KayStrobach\Themes\ViewHelpers\Variable;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @author Thomas Deuling <typo3@coding.ms>, coding.ms
 */
class PopBehaviourViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'Name', true);
    }

    /**
     * Pop the behaviour with the variable in $name.
     *
     * @return void
     */
    public function render()
    {
        $name = $this->arguments['name'];
        if (false === $this->templateVariableContainer->exists('themes')) {
            return;
        } else {
            $themes = $this->templateVariableContainer->get('themes');
            if (isset($themes['behaviour'])) {
                $value = '';
                if (isset($themes['behaviour']['css'])) {
                    if (isset($themes['behaviour']['css'][$name])) {
                        $value = $themes['behaviour']['css'][$name];
                        unset($themes['behaviour']['css'][$name]);
                    }
                }
                if (isset($themes['behaviour']['css2key'])) {
                    if (isset($themes['behaviour']['css2key'][$value])) {
                        unset($themes['behaviour']['css2key'][$value]);
                    }
                }
                $themes['behaviour']['key2css'] = $themes['behaviour']['css'];
                $themes['behaviour']['cssClasses'] = implode(' ', $themes['behaviour']['css']);
                // Write back
                $this->templateVariableContainer->remove('themes');
                $this->templateVariableContainer->add('themes', $themes);
            }
        }
    }
}
