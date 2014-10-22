<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Module state checker, compares module data across different storage levels (metadata file / database / configuration).
 */
class ac_module_internals_state extends oxAdminView
{
    /**
     * @var string
     */
    public $sTemplate = 'ac_module_internals_state.tpl';

    /** @var oxModule */
    protected $_oModule;

    /** @var ac_module_internals_data_helper */
    protected $_oModuleDataProviderHelper;

    /** @var ac_module_internals_fix_helper */
    protected $_oModuleFixHelper;

    /**
     * @return ac_module_internals_data_helper
     */
    public function getModuleDataProviderHelper()
    {
        if ($this->_oModuleDataProviderHelper === null) {
            $this->_oModuleDataProviderHelper = oxNew('ac_module_internals_data_helper', $this->getModule(), oxNew('oxModuleList'));
        }

        return $this->_oModuleDataProviderHelper;
    }

    /**
     * @param ac_module_internals_data_helper $oModuleDataProviderHelper
     */
    public function setModuleDataProviderHelper($oModuleDataProviderHelper)
    {
        $this->_oModuleDataProviderHelper = $oModuleDataProviderHelper;
    }

    /**
     * @return ac_module_internals_fix_helper
     */
    public function getModuleFixHelper()
    {
        if ($this->_oModuleFixHelper === null) {
            $this->_oModuleFixHelper = oxNew(
                'ac_module_internals_fix_helper',
                $this->getModule(),
                oxNew('oxModuleList'),
                oxNew('oxModuleCache', $this->getModule())
            );
        }

        return $this->_oModuleFixHelper;
    }

    /**
     * @param ac_module_internals_fix_helper $oModuleFixHelper
     */
    public function setModuleFixHelper($oModuleFixHelper)
    {
        $this->_oModuleFixHelper = $oModuleFixHelper;
    }

    /**
     * Get active module object.
     *
     * @return oxModule
     */
    public function getModule()
    {
        if ($this->_oModule === null) {
            $sModuleId = $this->getEditObjectId();

            $this->_aViewData['oxid'] = $sModuleId;

            /** @var $oModule oxModule */
            $this->_oModule = oxNew('oxModule');
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

        $this->_aViewData['oxid'] = $oHelper->getModuleId();
        $this->_aViewData['aExtended'] = $oHelper->checkExtendedClasses();
        $this->_aViewData['aBlocks'] = $oHelper->checkTemplateBlocks();
        $this->_aViewData['aSettings'] = $oHelper->checkModuleSettings();
        $this->_aViewData['aFiles'] = $oHelper->checkModuleFiles();
        $this->_aViewData['aTemplates'] = $oHelper->checkModuleTemplates();

        if ($oHelper->isMetadataSupported('1.1')) {
            $this->_aViewData['aEvents'] = $oHelper->checkModuleEvents();
            $this->_aViewData['aVersions'] = $oHelper->checkModuleVersions();
        }

        $this->_aViewData['sState'] = array(
            ac_module_internals_data_helper::STATE_FATAL_SHOP => 'sfatals',
            ac_module_internals_data_helper::STATE_FATAL_MODULE => 'sfatalm',
            ac_module_internals_data_helper::STATE_ERROR => 'serror',
            ac_module_internals_data_helper::STATE_WARNING => 'swarning',
            ac_module_internals_data_helper::STATE_OK => 'sok'
        );

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
