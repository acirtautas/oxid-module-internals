<?php

namespace OxCom\ModuleInternals\Controller\Admin;

use \OxidEsales\EshopCommunity\Core\Module\Module as Module;

/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Internal module utilities.
 */
class Utils extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    /** @var oxModule */
    protected $_oModule;

    /** @var oxModuleCache */
    protected $_oModuleCache;

    /** @var oxModuleInstaller */
    protected $_oModuleInstaller;

    /**
     * @var string
     */
    public $sTemplate = 'utils.tpl';

    /**
     * Get active module object.
     *
     * @return oxModule
     */
    public function getModule()
    {
        if ($this->_oModule === NULL) {
            $sModuleId = $this->getEditObjectId();

            $this->_aViewData['oxid'] = $sModuleId;
            $this->_oModule = oxNew(Module::class);
            $this->_oModule->load($sModuleId);
        }

        return $this->_oModule;
    }

    /**
     * Returns initialized cache instance
     *
     * @return oxModuleCache
     */
    public function getModuleCache()
    {
        if ($this->_oModuleCache === NULL) {
            $this->_oModuleCache = oxNew('oxModuleCache', $this->getModule());
        }

        return $this->_oModuleCache;
    }

    /**
     * Returns initialized module installer instance
     *
     * @return oxModuleInstaller
     */
    public function getModuleInstaller()
    {
        if ($this->_oModuleInstaller === NULL) {
            $this->_oModuleInstaller = oxNew('oxModuleInstaller', $this->getModuleCache());
        }

        return $this->_oModuleInstaller;
    }

    /**
     * @return string
     */
    public function render()
    {
        $oModule = $this->getModule();
        $sModuleId = $oModule->getId();

        $this->_aViewData['oxid'] = $sModuleId;
        $this->_aViewData['blIsActive'] = $oModule->isActive();

        return $this->sTemplate;
    }

    /**
     * Reset module cache.
     */
    public function reset_cache()
    {
        $this->getModuleCache()->resetCache();
    }

    /**
     * Activate module.
     */
    public function activate_module()
    {
        $this->getModuleInstaller()->activate($this->getModule());
    }

    /**
     * Deactivate module.
     */
    public function deactivate_module()
    {
        $this->getModuleInstaller()->deactivate($this->getModule());
    }
}
