<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

$sMetadataVersion = '1.0';

$aModule = array(
    'id'           => 'ac_module_internals',
    'title'        => 'Module Internals',
    'description'  => 'Internal OXID eShop module system information and troubleshooting tools.',
    'thumbnail'    => 'ac_module_internals.png',
    'version'      => '0.2.2',
    'author'       => 'Alfonsas Cirtautas',
    'url'          => 'https://github.com/acirtautas/oxid-module-internals',
    'email'        => '',
    'extend'       => array(
        'oxmodule' => 'ac_module_internals/core/ac_module'
    ),
    'files'        => array(
        'ac_module_internals_metadata' => 'ac_module_internals/controllers/admin/ac_module_internals_metadata.php',
        'ac_module_internals_state'    => 'ac_module_internals/controllers/admin/ac_module_internals_state.php',
        'ac_module_internals_utils'    => 'ac_module_internals/controllers/admin/ac_module_internals_utils.php'
    ),
    'templates'    => array(
        'ac_module_internals_metadata.tpl' => 'ac_module_internals/out/admin/tpl/ac_module_internals_metadata.tpl',
        'ac_module_internals_state.tpl'    => 'ac_module_internals/out/admin/tpl/ac_module_internals_state.tpl',
        'ac_module_internals_utils.tpl'    => 'ac_module_internals/out/admin/tpl/ac_module_internals_utils.tpl',
    )
);