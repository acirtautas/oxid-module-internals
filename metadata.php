<?php
/**
 * @package   moduleinternals
 * @category  OXID Module
 * @version   1.0.1
 * @license   GPL3 License http://opensource.org/licenses/GPL
 * @author    Alfonsas Cirtautas / OXID Community
 * @link      https://github.com/OXIDprojects/ocb_cleartmp
 * @see       https://github.com/acirtautas/oxid-module-internals
 */

$sMetadataVersion = '2.0';

$aModule = [
    'id'          => 'moduleinternals',
    'title'       => [
        'de' => 'OXID module internals (by Alfonsas Cirtautas)',
        'en' => 'OXID module internals (by Alfonsas Cirtautas)',
    ],
    'description' => [
        'en' => 'Internal OXID eShop module system information and troubleshooting tools (V6).',
        'de' => 'Internes OXID eShop Modulsystem Informations- und Troubleshooting Werkzeuge (V6).',
    ],
    'thumbnail'   => 'module_internals.png',
    'version'     => '1.0.1',
    'author'      => 'OXID Community',
    'url'         => 'https://github.com/OXIDprojects/oxid-module-internals',
    'email'       => '',
    'extend'      => [
        OxidEsales\Eshop\Core\Module\Module::class => OxidCommunity\ModuleInternals\Core\Module::class,
    ],
    'controllers' => [
        'module_internals_metadata' => OxidCommunity\ModuleInternals\Controller\Admin\Metadata::class,
        'module_internals_state'    => OxidCommunity\ModuleInternals\Controller\Admin\State::class,
        'module_internals_utils'    => OxidCommunity\ModuleInternals\Controller\Admin\Utils::class,
    ],
    'templates'   => [
        'metadata.tpl' => 'OxidCommunity/moduleinternals/views/admin/tpl/metadata.tpl',
        'state.tpl'    => 'OxidCommunity/moduleinternals/views/admin/tpl/state.tpl',
        'utils.tpl'    => 'OxidCommunity/moduleinternals/views/admin/tpl/utils.tpl',
    ],
];


