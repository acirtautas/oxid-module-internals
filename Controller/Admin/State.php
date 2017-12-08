<?php

namespace OxCom\ModuleInternals\Controller\Admin;

use OxCom\ModuleInternals\Core\DataHelper as DataHelper;
use OxCom\ModuleInternals\Core\FixHelper as FixHelper;
use OxidEsales\Eshop\Core\Module\ModuleCache as ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList as ModuleList;
use OxidEsales\Eshop\Core\Module\Module as Module;

/**
 * Module internals tools.
 *
 * @author Oxid Community
 */

/**
 * Module state checker, compares module data across different storage levels (metadata file / database / configuration).
 */
class State extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    /**
     * @var string
     */
    public $sTemplate = 'state.tpl';

    /** @var Module */
    protected $_oModule;

    /** @var DataHelper */
    protected $_oModuleDataProviderHelper;

    /** @var FixHelper */
    protected $_oModuleFixHelper;

    /**
     * @return DataHelper
     */
    public function getModuleDataProviderHelper()
    {
        if ($this->_oModuleDataProviderHelper === NULL) {
            $this->_oModuleDataProviderHelper = oxNew(DataHelper::class, $this->getModule(), ModuleList::class);
        }

        return $this->_oModuleDataProviderHelper;
    }

    /**
     * @param DataHelper $oModuleDataProviderHelper
     */
    public function setModuleDataProviderHelper($oModuleDataProviderHelper)
    {
        $this->_oModuleDataProviderHelper = $oModuleDataProviderHelper;
    }

    /**
     * @return FixHelper
     */
    public function getModuleFixHelper()
    {
        if ($this->_oModuleFixHelper === NULL) {
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
     * @param FixHelper $oModuleFixHelper
     */
    public function setModuleFixHelper($oModuleFixHelper)
    {
        $this->_oModuleFixHelper = $oModuleFixHelper;
    }

    /**
     * Get active module object.
     *
     * @return Module
     */
    public function getModule()
    {
        if ($this->_oModule === NULL) {
            $sModuleId = $this->getEditObjectId();

            $this->addTplParam('oxid', $sModuleId);

            /** @var $oModule Module */
            $this->_oModule = oxNew(Module::class);
            $this->_oModule->load($sModuleId);
        }

        return $this->_oModule;
    }

    /**
     * Collect info about module state.
     *
     * @return string
     */
    public function render()
    {
        $oHelper = $this->getModuleDataProviderHelper();

        //valid for all metadata versions
        $this->addTplParam('oxid', $oHelper->getModuleId());
        $this->addTplParam('aExtended', $oHelper->checkExtendedClasses());
        $this->addTplParam('aBlocks', $oHelper->checkTemplateBlocks());
        $this->addTplParam('aSettings', $oHelper->checkModuleSettings());
        $this->addTplParam('aTemplates', $oHelper->checkModuleTemplates());

        //valid not for  metadata version 1.*
        if ($oHelper->isMetadataSupported('1.0') | $oHelper->isMetadataSupported('1.1')) {
            $this->addTplParam('aFiles', $oHelper->checkModuleFiles());
        }

        //valid  for  metadata version 1.1 and 2.0
        if ($oHelper->isMetadataSupported('1.1')) {
            $this->addTplParam('aEvents', $oHelper->checkModuleEvents());
            $this->addTplParam('aVersions', $oHelper->checkModuleVersions());
        }

        $this->addTplParam('sState', [
            -3 => 'sfatals',
            -2 => 'sfatalm',
            -1 => 'serror',
            0  => 'swarning',
            1  => 'sok',
        ]);

        return $this->sTemplate;
    }

    /**
     * Fix module version.
     */
    public function fix_version()
    {
        $this->getModuleFixHelper()->fixVersion();
    }

    /**
     * Fix module extend.
     */
    public function fix_extend()
    {
        $this->getModuleFixHelper()->fixExtend();
    }

    /**
     * Fix module files.
     */
    public function fix_files()
    {
        $this->getModuleFixHelper()->fixFiles();
    }

    /**
     * Fix module templates.
     */
    public function fix_templates()
    {
        $this->getModuleFixHelper()->fixTemplates();
    }

    /**
     * Fix module blocks.
     */
    public function fix_blocks()
    {
        $this->getModuleFixHelper()->fixBlocks();
    }

    /**
     * Fix module settings.
     */
    public function fix_settings()
    {
        $this->getModuleFixHelper()->fixSettings();
    }

    /**
     * Fix module events.
     */
    public function fix_events()
    {
        $this->getModuleFixHelper()->fixEvents();
    }
}
