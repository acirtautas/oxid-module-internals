<?php

namespace OxCom\ModuleInternals\Controller\Admin;

use \OxidEsales\EshopCommunity\Core\Module\Module as Module;

/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Module metadata content retrieving.
 */
class Metadata extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    /**
     * @var string
     */
    public $sTemplate = 'metadata.tpl';

    /**
     * @return string
     */
    public function render()
    {
        $sModuleId = $this->getEditObjectId();

        $this->addTplParam('oxid', $sModuleId);

        /** @var $oModule oxModule */
        $oModule = oxNew(Module::class);
        $oModule->load($sModuleId);

        if ($oModule->hasMetadata()) {
            $sModulePath = $oModule->getModulePath($sModuleId);
            $sMetadataPath = $this->getConfig()->getModulesDir() . $sModulePath . "/metadata.php";
            $sRawMetadata = highlight_file($sMetadataPath, 1);
            $this->addTplParam('sRawMetadata', $sRawMetadata);
        }

        return $this->sTemplate;
    }
}