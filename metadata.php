<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

$sMetadataVersion = '2.0';

$aModule = array(
    'id'          => 'moduleinternals',
    'title'       => [
        'de' => 'Module Internals',
        'en' => 'Module Internals'
    ],
    'description' => [
        'en' => 'Internal OXID eShop module system information and troubleshooting tools (V6).',
        'de' => 'Internes OXID eShop Modulsystem Informations- und Troubleshooting Werkzeuge (V6).'
    ],
    'thumbnail'   => 'module_internals.png',
    'version'     => '0.4.0',
    'author'      => 'Oxid Community',
    'url'         => 'https://github.com/OXIDprojects/oxid-module-internals',
    'email'       => '',
    'extend'      => [],
    'controller'  => [
        'module_internals_metadata' => \OxCom\ModuleInternals\Controller\Admin\Metadata::class,
        'module_internals_state'    => \OxCom\ModuleInternals\Controller\Admin\State::class,
        'module_internals_utils'    => \OxCom\ModuleInternals\Controller\Admin\Utils::class
    ],
    'templates'   => [
        'metadata.tpl' => 'OxCom/moduleinternals/views/admin/tpl/metadata.tpl',
        'state.tpl'    => 'OxCom/moduleinternals/views/admin/tpl/state.tpl',
        'utils.tpl'    => 'OxCom/moduleinternals/views/admin/tpl/utils.tpl',
    ],
    'blocks'      => [],
    'settings'    => [],
    'events'      => [],
);


