<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Internal module utilities.
 */
class ac_module_internals_utils extends oxAdminView
{
    /**
     * @var string
     */
    public $sTemplate = 'ac_module_internals_utils.tpl';

    /**
     * Get active module object.
     *
     * @return oxModule
     */
    public function getModule()
    {
        $sModuleId = $this->getEditObjectId();

        $this->_aViewData['oxid'] = $sModuleId;

        /** @var $oModule oxModule */
        $oModule = oxNew('oxModule');
        $oModule->load($sModuleId);

        return $oModule;
    }

    /**
     * @return string
     */
    public function render()
    {
        $sModuleId = $this->getEditObjectId();

        $this->_aViewData['oxid'] = $sModuleId;

        return $this->sTemplate;
    }

    /**
     * Reset module cache.
     */
    public function reset_cache()
    {
        $oModule = $this->getModule();
        $oModule->resetCache();
    }
}