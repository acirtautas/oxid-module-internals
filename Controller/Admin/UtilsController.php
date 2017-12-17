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

namespace OxidCommunity\ModuleInternals\Controller\Admin;

use \OxidEsales\Eshop\Core\Module\Module as Module;
use \OxidEsales\Eshop\Core\Module\ModuleCache as ModuleCache;
use \OxidEsales\Eshop\Core\Module\ModuleInstaller as ModuleInstaller;

/**
 * Module internals tools.
 *
 * @author Oxid Community
 */

/**
 * Internal module utilities.
 */
class UtilsController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
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
        if ($this->_oModule === null) {
            $sModuleId = $this->getEditObjectId();

            $this->addTplParam('oxid', $sModuleId);
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
        if ($this->_oModuleCache === null) {
            $this->_oModuleCache = oxNew(ModuleCache::class, $this->getModule());
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
        if ($this->_oModuleInstaller === null) {
            $this->_oModuleInstaller = oxNew(ModuleInstaller::class, $this->getModuleCache());
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

        $this->addTplParam('oxid', $sModuleId);
        $this->addTplParam('blIsActive', $oModule->isActive());

        return $this->sTemplate;
    }

    /**
     * Reset module cache.
     */
    public function resetModuleCache()
    {
        $this->getModuleCache()->resetCache();
    }

    /**
     * Activate module.
     */
    public function activateModule()
    {
        $this->getModuleInstaller()->activate($this->getModule());
    }

    /**
     * Deactivate module.
     */
    public function deactivateModule()
    {
        $this->getModuleInstaller()->deactivate($this->getModule());
    }
}
