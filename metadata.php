<?php
/**
 * @package   moduleinternals
 * @category  OXID Module
 * @version   1.1.0
 * @license   GPL3 License http://opensource.org/licenses/GPL
 * @author    Alfonsas Cirtautas / OXID Community
 * @link      https://github.com/OXIDprojects/ocb_cleartmp
 * @see       https://github.com/acirtautas/oxid-module-internals
 */
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;

$sMetadataVersion = '2.0';

$sLinkToClass = Registry::get(Config::class)->getConfigParam('sShopURL').'index.php';
$sLinkToClass.= "?cl=checkconsistency";
$sLinkToClass.= "&key=".Registry::get(Config::class)->getConfigParam('sACActiveCompleteKey');

$aModule = [
    'id'          => 'moduleinternals',
    'title'       => [
        'de' => 'OXID Community Module Internals (by Alfonsas Cirtautas)',
        'en' => 'OXID Community Module Internals (by Alfonsas Cirtautas)',
    ],
    'description' => [
        'en' => 'Internal OXID eShop module system information and troubleshooting tools (V6).
        <hr>
    Overview health status: <a href="'.$sLinkToClass.'" target="_blank">'.$sLinkToClass.'</a>',

        'de' => 'Internes OXID eShop Modulsystem Informations- und Troubleshooting Werkzeuge (V6).
        <hr>
    Komplette &Uuml;bersicht: <a href="'.$sLinkToClass.'" target="_blank">'.$sLinkToClass.'</a>',
    ],
    'thumbnail'   => 'module_internals.png',
    'version'     => '1.1.0',
    'author'      => 'OXID Community',
    'url'         => 'https://github.com/OXIDprojects/oxid-module-internals',
    'email'       => '',
    'extend'      => [
        \OxidEsales\Eshop\Core\Module\Module::class => \OxidCommunity\ModuleInternals\Core\InternalModule::class,
    ],
    'controllers' => [
        'module_internals_metadata' => \OxidCommunity\ModuleInternals\Controller\Admin\Metadata::class,
        'module_internals_state'    => \OxidCommunity\ModuleInternals\Controller\Admin\State::class,
        'module_internals_utils'    => \OxidCommunity\ModuleInternals\Controller\Admin\UtilsController::class,
        'checkconsistency'          => \OxidCommunity\ModuleInternals\Controller\CheckConsistency::class,
    ],
    'templates'   => [
        'metadata.tpl'              => 'oxcom/moduleinternals/views/admin/tpl/metadata.tpl',
        'state.tpl'                 => 'oxcom/moduleinternals/views/admin/tpl/state.tpl',
        'utils.tpl'                 => 'oxcom/moduleinternals/views/admin/tpl/utils.tpl',
        'checkconsistency.tpl'      => 'oxcom/moduleinternals/views/flow/tpl/checkconsistency.tpl',
    ],
    'settings'    => [
        [
            'group' => 'AC_CONFIG',
            'name'  => 'blACActiveCompleteCheck',
            'type'  => 'bool',
            'value' => 'false'
        ],
        [
            'group' => 'AC_CONFIG',
            'name'  => 'sACActiveCompleteKey',
            'type'  => 'str',
            'value' => ''
        ],
    ]
];


