<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

$sMetadataVersion = '1.0';

$aModule = array(
    'id'           => 'oxid-module-internals',
    'title'        => 'Module Internals',
    'description'  => 'Internal OXID eShop module system information and troubleshooting tools (CE|PE 4.9 & EE 5.2).',
    'thumbnail'    => 'ac_module_internals.png',
    'version'      => '0.3.1',
    'author'       => 'Alfonsas Cirtautas',
    'url'          => 'https://github.com/acirtautas/oxid-module-internals',
    'email'        => '',
    'extend'       => array(),
    'files'        => array(
        'ac_module_internals_data_helper'   => 'oxid-module-internals/core/ac_module_internals_data_helper.php',
        'ac_module_internals_fix_helper'   => 'oxid-module-internals/core/ac_module_internals_fix_helper.php',
        'ac_module_internals_metadata' => 'oxid-module-internals/controllers/admin/ac_module_internals_metadata.php',
        'ac_module_internals_state'    => 'oxid-module-internals/controllers/admin/ac_module_internals_state.php',
        'ac_module_internals_utils'    => 'oxid-module-internals/controllers/admin/ac_module_internals_utils.php'
    ),
    'templates'    => array(
        'ac_module_internals_metadata.tpl' => 'oxid-module-internals/views/admin/tpl/ac_module_internals_metadata.tpl',
        'ac_module_internals_state.tpl'    => 'oxid-module-internals/views/admin/tpl/ac_module_internals_state.tpl',
        'ac_module_internals_utils.tpl'    => 'oxid-module-internals/views/admin/tpl/ac_module_internals_utils.tpl',
    )
);
