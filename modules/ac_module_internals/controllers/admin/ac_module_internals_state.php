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
     * Collect info about module state.
     *
     * @return string
     */
    public function render()
    {
        $oModule     = $this->getModule();
        $sModuleId   = $oModule->getId();
        $sModulePath = $oModule->getModulePath($sModuleId);

        $this->_aViewData['oxid']       = $sModuleId;

        $this->_aViewData['aExtended']  = $this->_checkExtendedClasses($sModulePath, $oModule->getInfo('extend'), $oModule->getAllModules());
        $this->_aViewData['aBlocks']    = $this->_checkTemplateBlocks($sModulePath, $oModule->getInfo('blocks'), $oModule->getModuleBlocks($sModuleId), $oModule->getInfo('templates'));
        $this->_aViewData['aSettings']  = $this->_checkModuleSettings($sModulePath, $oModule->getInfo('settings'), $oModule->getModuleSettings($sModuleId));
        $this->_aViewData['aFiles']     = $this->_checkModuleFiles($sModulePath, $oModule->getInfo('files'), $oModule->getModuleFiles($sModuleId));
        $this->_aViewData['aTemplates'] = $this->_checkModuleTemplates($sModulePath, $oModule->getInfo('templates'), $oModule->getModuleTemplates($sModuleId));

        if ($oModule->isMetadataSupported('1.1')) {
            $this->_aViewData['aEvents']    = $this->_checkModuleEvents($sModulePath, $oModule->getInfo('events'), $oModule->getModuleEvents($sModuleId));
            $this->_aViewData['aVersions']  = $this->_checkModuleVersions($sModulePath, $oModule->getInfo('version'), $oModule->getModuleVersion($sModuleId));
        }

        $this->_aViewData['sState'] = array (-3 => 'sfatals', -2 => 'sfatalm', -1 => 'serror', 0 => 'swarning', 1 => 'sok');

        return $this->sTemplate;
    }

    /**
     * Fix module version.
     */
    public function fix_version()
    {
        $oModule  = $this->getModule();
        $sVersion = $oModule->getInfo('version');

        $oModule->setModuleVersion($oModule->getId(), $sVersion);
    }

    /**
     * Fix module extend.
     */
    public function fix_extend()
    {
        $oModule  = $this->getModule();
        $aExtend  = $oModule->getInfo('extend');

        $oModule->setModuleExtend($oModule->getId(), $aExtend);
    }

    /**
     * Fix module files.
     */
    public function fix_files()
    {
        $oModule = $this->getModule();
        $aFiles  = $oModule->getInfo('files');

        $oModule->setModuleFiles($oModule->getId(), $aFiles);
    }

    /**
     * Fix module templates.
     */
    public function fix_templates()
    {
        $oModule    = $this->getModule();
        $aTemplates = $oModule->getInfo('templates');

        $oModule->setModuleTemplates($oModule->getId(), $aTemplates);
    }

    /**
     * Fix module blocks.
     */
    public function fix_blocks()
    {
        $oModule   = $this->getModule();
        $aBlocks = $oModule->getInfo('blocks');

        $oModule->setModuleBlocks($oModule->getId(), $aBlocks);
    }
    /**
     * Fix module settings.
     */
    public function fix_settings()
    {
        $oModule   = $this->getModule();
        $aSettings = $oModule->getInfo('settings');

        $oModule->setModuleSettings($oModule->getId(), $aSettings);
    }

    /**
     * Fix module events.
     */
    public function fix_events()
    {
        $oModule = $this->getModule();
        $aEvents = $oModule->getInfo('events');

        $oModule->setModuleEvents($oModule->getId(), $aEvents);
    }

    /**
     *Analyze extended class information in metadata and database.
     *
     * @param $sModulePath
     * @param $aMetadataExtend
     * @param $aAllModules
     *
     * @return array
     */
    protected function _checkExtendedClasses($sModulePath, $aMetadataExtend, $aAllModules)
    {
        $aResult = array();

        // Check if all classes are extended.
        if (is_array($aMetadataExtend)) {
            $aMetadataExtend = array_change_key_case($aMetadataExtend, CASE_LOWER);
            foreach ($aMetadataExtend as $sClassName => $sModuleName) {
                $iState = 0;
                if (is_array($aAllModules) && isset($aAllModules[$sClassName])) {
                    // Is module extending class
                    if (is_array($aAllModules[$sClassName])) {
                        $iState = in_array($sModuleName, $aAllModules[$sClassName]) ? 1 : 0;
                    }
                }

                $aResult[$sClassName][$sModuleName] = $iState;
            }
        }

        // Check for redundant extend data by path
        if ($sModulePath && is_array($aAllModules)) {
            foreach ($aAllModules as $sClassName => $mModuleName) {
                if (is_array($mModuleName)) {
                    foreach ($mModuleName as $sModuleName) {
                        if (!isset($aResult[$sClassName][$sModuleName]) && strpos($sModuleName, $sModulePath . '/') === 0) {
                            $aResult[$sClassName][$sModuleName] = -1;
                        }
                    }
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze template block information in metadata and database.
     *
     * @param $sModulePath
     * @param $aMetadataBlocks
     * @param $aDatabaseBlocks
     * @param $aMetadataTemplates
     *
     * @return array
     */
    protected function _checkTemplateBlocks($sModulePath, $aMetadataBlocks, $aDatabaseBlocks, $aMetadataTemplates)
    {
        $sModulesDir = $this->getConfig()->getModulesDir();

        $aResult = array();

        // Check if all blocks are injected.
        if (is_array($aMetadataBlocks)) {
            foreach ($aMetadataBlocks as $aBlock) {
                $iState = 0;
                if (is_array($aDatabaseBlocks)) {
                    foreach ($aDatabaseBlocks as $aDbBlock) {
                        // Is template block inserted
                        if (
                            ($aBlock['template'] == $aDbBlock['OXTEMPLATE']) &&
                            ($aBlock['block'] == $aDbBlock['OXBLOCKNAME']) &&
                            ($aBlock['file'] == $aDbBlock['OXFILE'])
                        ) {
                            $iState = 1;
                        }
                    }
                }

                if (!file_exists($sModulesDir.'/'.$sModulePath.'/'.$aBlock['file']) &&
                    !file_exists($sModulesDir.'/'.$sModulePath.'/out/blocks/'.basename($aBlock['file'])) &&
                    !file_exists($sModulesDir.'/'.$sModulePath.'/out/blocks/'.basename($aBlock['file']).'.tpl') ) {
                    $iState = -2;
                }

                $aResult[$aBlock['template']][$aBlock['file']]['file'] = $iState;
            }
        }

        // Check for redundant blocks for current module.
        if (is_array($aDatabaseBlocks)) {
            foreach ($aDatabaseBlocks as $aDbBlock) {

                if (!isset($aResult[$aDbBlock['OXTEMPLATE']][$aDbBlock['OXFILE']])) {
                    $aResult[$aDbBlock['OXTEMPLATE']][$aDbBlock['OXFILE']] = -1;
                    if (!file_exists($sModulesDir.'/'.$sModulePath.'/'.$aDbBlock['OXFILE']) &&
                        !file_exists($sModulesDir.'/'.$sModulePath.'/out/blocks/'.basename($aDbBlock['OXFILE'])) &&
                        !file_exists($sModulesDir.'/'.$sModulePath.'/out/blocks/'.basename($aDbBlock['OXFILE'])).'.tpl') {
                        $aResult[$aDbBlock['OXTEMPLATE']][$aDbBlock['OXFILE']]['file'] = -3;
                    }
                }
            }
        }

        // Check if template file exists and block is defined.
        if (is_array($aMetadataBlocks)) {
            foreach ($aMetadataBlocks as $aBlock) {

                // Get template from shop..
                $sTemplate = $this->getConfig()->getTemplatePath($aBlock['template'], false);

                // Get template from shop admin ..
                if (!$sTemplate) {
                    $sTemplate = $this->getConfig()->getTemplatePath($aBlock['template'], true);
                }

                // Get template from module ..
                if (!$sTemplate && isset($aMetadataTemplates[$aBlock['template']]) ) {

                    $sModulesDir = $this->getConfig()->getModulesDir();

                    if (file_exists($sModulesDir.'/'.$aMetadataTemplates[$aBlock['template']])) {
                        $sTemplate = $sModulesDir.'/'.$aMetadataTemplates[$aBlock['template']];
                    }
                }

                if (empty($sTemplate)) {
                    $aResult[$aBlock['template']][$aBlock['file']]['template'] = -3;
                } else {
                    $sContent = file_get_contents($sTemplate);
                    if (!preg_match('/\[{.*block.* name.*= *"'.$aBlock['block'].'".*}\]/', $sContent)) {
                        $aResult[$aBlock['template']][$aBlock['file']]['template'] = -1;
                    }
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze settings in metadata ans settings.
     *
     * @param $sModulePath
     * @param $aMetadataSettings
     * @param $aDatabaseSettings
     *
     * @return array
     */
    protected function _checkModuleSettings($sModulePath, $aMetadataSettings, $aDatabaseSettings)
    {
        $aResult = array();

        // Check if all settings are injected.
        if (is_array($aMetadataSettings)) {
            foreach ($aMetadataSettings as $aData) {
                $sName = $aData['name'];
                $aResult[$sName] = 0;
            }
        }

        // Check for redundant settings for current module.
        if (is_array($aDatabaseSettings)) {
            foreach ($aDatabaseSettings as $aData) {
                $sName = $aData['OXVARNAME'];

                if (!isset($aResult[$sName])) {
                    $aResult[$sName] = -1;
                } else {
                    $aResult[$sName] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze files in metadata ans settings.
     *
     * @param $sModulePath
     * @param $aMetadataFiles
     * @param $aDatabaseFiles
     *
     * @return array
     */
    protected function _checkModuleFiles($sModulePath, $aMetadataFiles, $aDatabaseFiles)
    {
        $sModulesDir = $this->getConfig()->getModulesDir();

        $aResult = array();

        // Check if all module files are injected.
        if (is_array($aMetadataFiles)) {
            $aMetadataFiles = array_change_key_case($aMetadataFiles, CASE_LOWER);
            foreach ($aMetadataFiles as $sClass => $sFile) {
                $aResult[$sClass][$sFile] = 0;
                if (!file_exists($sModulesDir.'/'.$sFile)) {
                    $aResult[$sClass][$sFile] = -2;
                }
            }
        }

        // Check for redundant or missing module files
        if (is_array($aDatabaseFiles)) {
            foreach ($aDatabaseFiles as $sClass => $sFile) {
                if (!isset($aResult[$sClass][$sFile])) {
                    @$aResult[$sClass][$sFile] = -1;
                    if (!file_exists($sModulesDir.'/'.$sFile)) {
                        @$aResult[$sClass][$sFile] = -3;
                    }
                } elseif($aResult[$sClass][$sFile] == 0 ) {
                    @$aResult[$sClass][$sFile] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze templates in metadata ans settings.
     *
     * @param $sModulePath
     * @param $aMetadataTemplates
     * @param $aDatabaseTemplates
     *
     * @return array
     */
    protected function _checkModuleTemplates($sModulePath, $aMetadataTemplates, $aDatabaseTemplates)
    {
        $sModulesDir = $this->getConfig()->getModulesDir();

        $aResult = array();

        // Check if all module templates are injected.
        if (is_array($aMetadataTemplates)) {
            $aMetadataTemplates = array_change_key_case($aMetadataTemplates, CASE_LOWER);
            foreach ($aMetadataTemplates as $sTemplate => $sFile) {
                $aResult[$sTemplate][$sFile] = 0;
                if (!file_exists($sModulesDir.'/'.$sFile)) {
                    $aResult[$sTemplate][$sFile] = -2;
                }
            }
        }

        // Check for redundant or missing module templates
        if (is_array($aDatabaseTemplates)) {
            foreach ($aDatabaseTemplates as $sTemplate => $sFile) {
                if (!isset($aResult[$sTemplate][$sFile])) {
                    @$aResult[$sTemplate][$sFile] = -1;
                    if (!file_exists($sModulesDir.'/'.$sFile)) {
                        @$aResult[$sTemplate][$sFile] = -3;
                    }
                } elseif($aResult[$sTemplate][$sFile] == 0 ) {
                    @$aResult[$sTemplate][$sFile] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze events in metadata ans settings.
     *
     * @param $sModulePath
     * @param $aMetadataEvents
     * @param $aDatabaseEvents
     *
     * @return array
     */
    protected function _checkModuleEvents($sModulePath, $aMetadataEvents, $aDatabaseEvents)
    {
        $aResult = array();

        // Check if all events are injected.
        if (is_array($aMetadataEvents)) {
            foreach ($aMetadataEvents as $sEvent => $mCallback) {
                $sCallback = print_r($mCallback, 1);
                $aResult[$sEvent][$sCallback] = 0;
            }
        }

        // Check for redundant or missing events.
        if (is_array($aDatabaseEvents)) {
            foreach ($aDatabaseEvents as $sEvent => $mCallback) {
                $sCallback = print_r($mCallback, 1);
                if (!isset($aResult[$sEvent][$sCallback])) {
                    $aResult[$sEvent][$sCallback] = -1;
                } else {
                    $aResult[$sEvent][$sCallback] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze versions in metadata ans settings.
     *
     * @param $sModulePath
     * @param $sMetadataVersion
     * @param $sDatabaseVersion
     *
     * @return array
     */
    protected function _checkModuleVersions($sModulePath, $sMetadataVersion, $sDatabaseVersion)
    {
        $aResult = array();

        // Check version..
        if ($sMetadataVersion) {
            $aResult[$sMetadataVersion] = 0;
        }

        // Check for versions match injected.
        if ($sDatabaseVersion) {

            if (!isset($aResult[$sDatabaseVersion])) {
                $aResult[$sDatabaseVersion] = -1;
            } else {
                $aResult[$sDatabaseVersion] = 1;
            }
        }

        return $aResult;
    }
}