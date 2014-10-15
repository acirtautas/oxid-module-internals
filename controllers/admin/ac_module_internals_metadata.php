<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Module metadata content retrieving.
 */
class ac_module_internals_metadata extends oxAdminView
{
    /**
     * @var string
     */
    public $sTemplate = 'ac_module_internals_metadata.tpl';

    /**
     * @return string
     */
    public function render()
    {
        $sModuleId = $this->getEditObjectId();

        $this->_aViewData['oxid'] = $sModuleId;

        /** @var $oModule oxModule */
        $oModule = oxNew('oxModule');
        $oModule->load($sModuleId);

        if ($oModule->hasMetadata()) {
            $sModulePath                      = $oModule->getModulePath($sModuleId);
            $sMetadataPath                    = $this->getConfig()->getModulesDir() . $sModulePath . "/metadata.php";
            $sRawMetadata                     = highlight_file($sMetadataPath, 1);
            $this->_aViewData['sRawMetadata'] = $sRawMetadata;
        }

        return $this->sTemplate;
    }
}