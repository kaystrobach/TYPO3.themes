<?php

$l10n = 'LLL:EXT:themes/Resources/Private/Language/ButtonContent.xlf:';

return [
    'ctrl'      => [
        'title'              => $l10n.'tx_themes_buttoncontent',
        'label'              => 'linktext',
        'tstamp'             => 'tstamp',
        'crdate'             => 'crdate',
        'cruser_id'          => 'cruser_id',
        'versioningWS'       => true,
        'origUid'            => 't3_origuid',
        'sortby'             => 'sorting',
        'delete'             => 'deleted',
        'rootLevel'          => -1,
        'thumbnail'          => 'resources',
        'dividers2tabs'      => true,
        'enablecolumns'      => [
            'disabled' => 'hidden',
        ],
        'iconfile'           => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('themes').'Resources/Public/Icons/button_content.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'linktext,linktarget,linktitle,icon',
    ],
    'columns' => [
        'linktext' => [
            'label'  => $l10n.'linktext',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max'  => 30,
            ],
        ],
        'linktarget' => [
            'exclude' => 1,
            'label'   => $l10n.'linktarget',
            'config'  => [
                'renderType' => 'inputLink',
                'eval'    => 'trim',
                'max'     => 1024,
                'size'    => 50,
                'softref' => 'typolink',
                'type'    => 'input',
            ],
        ],
        'linktitle' => [
            'exclude' => 1,
            'label'   => $l10n.'linktitle',
            'config'  => [
                'type' => 'input',
                'size' => 50,
                'max'  => 256,
                'eval' => 'required',
            ],
        ],
        'icon' => [
            'exclude' => 1,
            'label'   => $l10n.'icon',
            'config'  => [
                'type'         => 'select',
                'renderType'   => 'selectSingle',
                'items'        => [
                    ['', ''],
                ],
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config'  => [
                'type'  => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:cms/locallang_ttc.xlf:hidden.I.0',
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config'  => [
                'type'    => 'input',
                'renderType' => 'inputDateTime',
                'size'    => 13,
                'eval'    => 'datetime',
                'default' => 0,
            ],
            'l10n_mode'    => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config'  => [
                'type'    => 'input',
                'renderType' => 'inputDateTime',
                'size'    => 13,
                'eval'    => 'datetime',
                'default' => 0,
                'range'   => [
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                ],
            ],
            'l10n_mode'    => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'sys_language_uid' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config'  => [
                'type'                => 'select',
                'renderType'          => 'selectSingle',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items'               => [
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                    ],
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.default_value',
                        0,
                    ],
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
            'config'      => [
                'type' => 'passthrough',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'fe_group' => [
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config'  => [
                'type'       => 'select',
                'size'       => 5,
                'maxitems'   => 20,
                'items'      => [
                    ['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login', -1],
                    ['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.any_login', -2],
                    ['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.usergroups', '--div--'],
                ],
                'exclusiveKeys'       => '-1,-2',
                'foreign_table'       => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
            ],
        ],
        'tt_content' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
    'types'     => [
        '1' => [
            'showitem' => 'linktext,linktarget,linktitle,icon,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,hidden,starttime,endtime,fe_group',
        ],
    ],
];
