<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

$sMetadataVersion = '1.0';

$aModule = array(
    'id'           => 'ac_module_internals',
    'title'        => 'Module Internals (CE|PE 4.9 & EE 5.2)',
    'description'  => 'Internal OXID eShop module system information and troubleshooting tools (CE|PE 4.9 & EE 5.2).',
    'thumbnail'    => 'ac_module_internals.png',
    'version'      => '0.2.2',
    'author'       => 'Alfonsas Cirtautas',
    'url'          => 'https://github.com/acirtautas/oxid-module-internals',
    'email'        => '',
    'extend'       => array(),
    'files'        => array(
        'ac_module_internals_data_helper'   => 'ac_module_internals/core/ac_module_internals_data_helper.php',
        'ac_module_internals_fix_helper'   => 'ac_module_internals/core/ac_module_internals_fix_helper.php',
        'ac_module_internals_metadata' => 'ac_module_internals/controllers/admin/ac_module_internals_metadata.php',
        'ac_module_internals_state'    => 'ac_module_internals/controllers/admin/ac_module_internals_state.php',
        'ac_module_internals_utils'    => 'ac_module_internals/controllers/admin/ac_module_internals_utils.php'
    ),
    'templates'    => array(
        'ac_module_internals_metadata.tpl' => 'ac_module_internals/views/admin/tpl/ac_module_internals_metadata.tpl',
        'ac_module_internals_state.tpl'    => 'ac_module_internals/views/admin/tpl/ac_module_internals_state.tpl',
        'ac_module_internals_utils.tpl'    => 'ac_module_internals/views/admin/tpl/ac_module_internals_utils.tpl',
    )
);
