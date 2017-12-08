<?php
namespace OxCom\ModuleInternals\Controller\Admin;

use OxidEsales\Eshop\Core\Module\ModuleCache as ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList as ModuleList;
use OxCom\ModuleInternals\Core\FixHelper as FixHelper;
use OxCom\ModuleInternals\Core\DataHelper as DataHelper;


/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Module state checker, compares module data across different storage levels (metadata file / database / configuration).
 */
class State extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController {
    /**
     * @var string
     */
    public $sTemplate = 'state.tpl';

    /** @var oxModule */
    protected $_oModule;

    /** @var ac_module_internals_data_helper */
    protected $_oModuleDataProviderHelper;

    /** @var ac_module_internals_fix_helper */
    protected $_oModuleFixHelper;

    /**
     * @return ac_module_internals_data_helper
     */
    public function getModuleDataProviderHelper() {
        if ($this->_oModuleDataProviderHelper === null) {
            $this->_oModuleDataProviderHelper = oxNew(DataHelper::class, $this->getModule(), ModuleList::class);
        }

        return $this->_oModuleDataProviderHelper;
    }

    /**
     * @param ac_module_internals_data_helper $oModuleDataProviderHelper
     */
    public function setModuleDataProviderHelper($oModuleDataProviderHelper) {
        $this->_oModuleDataProviderHelper = $oModuleDataProviderHelper;
    }

    /**
     * @return ac_module_internals_fix_helper
     */
    public function getModuleFixHelper() {
        if ($this->_oModuleFixHelper === null) {
            $this->_oModuleFixHelper = oxNew(
                    FixHelper::class,
                    $this->getModule(),
                    oxNew(ModuleList::class),
                    oxNew(ModuleCache::class, $this->getModule())
            );
        }

        return $this->_oModuleFixHelper;
    }

    /**
     * @param ac_module_internals_fix_helper $oModuleFixHelper
     */
    public function setModuleFixHelper($oModuleFixHelper) {
        $this->_oModuleFixHelper = $oModuleFixHelper;
    }

    /**
     * Get active module object.
     *
     * @return oxModule
     */
    public function getModule() {
        if ($this->_oModule === null) {
            $sModuleId = $this->getEditObjectId();

            $this->addTplParam('oxid', $sModuleId);

            /** @var $oModule oxModule */
            $this->_oModule = oxNew(ModuleCache::class);
            $this->_oModule->load($sModuleId);
        }

        return $this->_oModule;
    }

    /**
     * Collect info about module state.
     *
     * @return string
     */
    public function render() {
        $oHelper = $this->getModuleDataProviderHelper();

        $this->addTplParam('oxid', $oHelper->getModuleId());
        $this->addTplParam('aExtended', $oHelper->checkExtendedClasses());
        $this->addTplParam('aBlocks', $oHelper->checkTemplateBlocks());
        $this->addTplParam('aSettings', $oHelper->checkModuleSettings());
        $this->addTplParam('aFiles', $oHelper->checkModuleFiles());
        $this->addTplParam('aTemplates', $oHelper->checkModuleTemplates());

        if ($oHelper->isMetadataSupported('1.1')) {
            $this->addTplParam('aEvents', $oHelper->checkModuleEvents());
            $this->addTplParam('aVersions', $oHelper->checkModuleVersions());
        }

        $this->addTplParam('sState',  array(
                -3 => 'sfatals',
                -2 => 'sfatalm',
                -1 => 'serror',
                0  => 'swarning',
                1  => 'sok',
        ));

        return $this->sTemplate;
    }

    /**
     * Fix module version.
     */
    public function fix_version() {
        $this->getModuleFixHelper()->fixVersion();
    }

    /**
     * Fix module extend.
     */
    public function fix_extend() {
        $this->getModuleFixHelper()->fixExtend();
    }

    /**
     * Fix module files.
     */
    public function fix_files() {
        $this->getModuleFixHelper()->fixFiles();
    }

    /**
     * Fix module templates.
     */
    public function fix_templates() {
        $this->getModuleFixHelper()->fixTemplates();
    }

    /**
     * Fix module blocks.
     */
    public function fix_blocks() {
        $this->getModuleFixHelper()->fixBlocks();
    }

    /**
     * Fix module settings.
     */
    public function fix_settings() {
        $this->getModuleFixHelper()->fixSettings();
    }

    /**
     * Fix module events.
     */
    public function fix_events() {
        $this->getModuleFixHelper()->fixEvents();
    }
}
