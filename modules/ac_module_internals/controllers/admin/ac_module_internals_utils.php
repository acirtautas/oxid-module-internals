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
        $oModule   = $this->getModule();
        $sModuleId = $oModule->getId();

        $this->_aViewData['oxid']       = $sModuleId;
        $this->_aViewData['blIsActive'] = $oModule->isActive();

        return $this->sTemplate;
    }

    /**
     * Reset module cache.
     */
    public function reset_cache()
    {
        $this->getModule()->resetCache();
    }

    /**
     * Activate module.
     */
    public function activate_module()
    {
        $this->getModule()->activate();
    }

    /**
     * Deactivate module.
     */
    public function deactivate_module()
    {
        $this->getModule()->deactivate();
    }
}