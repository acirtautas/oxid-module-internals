<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 * @author Saulius Cepauskas
 */

/**
 * Class ac_module_internals_helper
 *
 * Data helper service: retrieves and compares module configuration. Checks if all module relevant data is properly
 * registered and exists. Business logic moved into service from the controller.
 */
class ac_module_internals_data_helper
{
    /** @var oxModule */
    protected $_oModule;

    /** @var oxModuleList */
    protected $_oModuleList;

    /** @var oxDB */
    protected $_oDb;

    /** @var oxConfig */
    protected $_oConfig;

    /**
     * Injects helper parameters
     *
     * @param oxModule $oModule
     * @param oxModuleList $oModuleList
     */
    public function __construct(oxModule $oModule, oxModuleList $oModuleList)
    {
        $this->_oModule = $oModule;
        $this->_oModuleList = $oModuleList;
    }

    /**
     * @return oxModule
     */
    public function getModule()
    {
        return $this->_oModule;
    }

    /**
     * @param oxModule $oModule
     */
    public function setModule(oxModule $oModule)
    {
        $this->_oModule = $oModule;
    }

    /**
     * @return oxModuleList
     */
    public function getModuleList()
    {
        return $this->_oModuleList;
    }

    /**
     * @param oxModuleList $oModuleList
     */
    public function setModuleList(oxModuleList $oModuleList)
    {
        $this->_oModuleList = $oModuleList;
    }

    /**
     * @return oxLegacyDb
     */
    public function getDb()
    {
        if (is_null($this->_oDb)) {
            $this->setDb(oxDb::getDb(oxDb::FETCH_MODE_ASSOC));
        }

        return $this->_oDb;
    }

    /**
     * @param oxLegacyDb $oDb
     */
    public function setDb(oxLegacyDb $oDb)
    {
        $this->_oDb = $oDb;
    }

    /**
     * @return oxConfig
     */
    public function getConfig()
    {
        if (is_null($this->_oConfig)) {
            $this->setConfig(oxRegistry::getConfig());
        }

        return $this->_oConfig;
    }

    /**
     * @param oxConfig $oConfig
     */
    public function setConfig(oxConfig $oConfig)
    {
        $this->_oConfig = $oConfig;
    }

    /**
     * Returns module info
     *
     * @param string $sName
     * @param int $iLang
     * @return string
     */
    public function getInfo($sName, $iLang = null)
    {
        return $this->getModule()->getInfo($sName, $iLang);
    }

    /**
     * Get template blocks defined in database.
     *
     * @return array
     */
    public function getModuleBlocks()
    {
        $sSelect   = 'SELECT * FROM oxtplblocks WHERE oxmodule = ? AND oxshopid = ?';
        $iModuleId = $this->getModuleId();
        $iShopId   = $this->getConfig()->getShopId();

        return $this->getDb()->getAll($sSelect, array($iModuleId, $iShopId));
    }

    /**
     * Get module settings defined in database.
     *
     * @return array
     */
    public function getModuleSettings()
    {
        $sSelect   = 'SELECT * FROM oxconfig WHERE oxmodule = ? AND oxshopid = ?';
        $iModuleId = $this->getModuleId();
        $iShopId   = $this->getConfig()->getShopId();

        return $this->getDb()->getAll($sSelect, array($iModuleId, $iShopId));
    }

    /**
     * Returns array of module files
     *
     * @return array
     */
    public function getModuleFiles()
    {
        $aReturn = array();
        $aList = $this->getModuleList()->getModuleFiles();

        if (isset($aList[$this->getModuleId()])) {
            $aReturn = $aList[$this->getModuleId()];
        }

        return $aReturn;
    }

    /**
     * Returns array of module templates
     *
     * @return array
     */
    public function getModuleTemplates()
    {
        $aReturn = array();
        $aList = $this->getModuleList()->getModuleTemplates();

        if (isset($aList[$this->getModuleId()])) {
            $aReturn = $aList[$this->getModuleId()];
        }

        return $aReturn;
    }

    /**
     * Returns array of module events
     *
     * @return array
     */
    public function getModuleEvents()
    {
        $aReturn = array();
        $aList = $this->getModuleList()->getModuleEvents();

        if (isset($aList[$this->getModuleId()])) {
            $aReturn = $aList[$this->getModuleId()];
        }

        return $aReturn;
    }

    /**
     * Returns module version
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $aList = $this->getModuleList()->getModuleVersions();

        return isset($aList[$this->getModuleId()]) ? $aList[$this->getModuleId()] : '';
    }

    /**
     * Check supported metadata version
     *
     * @param string $sMetadataVersion Metadata version
     *
     * @return bool
     */
    public function isMetadataSupported($sMetadataVersion)
    {
        $sLatestVersion = '1.0';
        if (method_exists($this->getModuleList(), 'getModuleVersions') || method_exists($this->getModule(), 'getModuleEvents')) {
            $sLatestVersion = '1.1';
        }

        return version_compare($sLatestVersion, $sMetadataVersion) >= 0;
    }

    /**
     * Returns injected module path
     *
     * @return string
     */
    public function getModulePath()
    {
        return $this->getModule()->getModulePath($this->getModuleId());
    }

    /**
     * Returns injected module ID
     *
     * @return string
     */
    public function getModuleId()
    {
        return $this->getModule()->getId();
    }

    /**
     * Analyze extended class information in metadata and database.
     *
     * @return array
     */
    public function checkExtendedClasses()
    {
        $oConfig = $this->getConfig();

        $sModulePath = $this->getModulePath();
        $aMetadataExtend = $this->getInfo('extend');
        $aAllModules = $oConfig->getModulesWithExtendedClass();

        $aResult = array();
        $sModulesDir = $oConfig->getModulesDir(true);

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

                if (!file_exists($sModulesDir . $sModuleName . ".php")) {
                    $aResult[$sClassName][$sModuleName] = -2; // sfatalm
                } else {
                    $aResult[$sClassName][$sModuleName] = $iState;
                }
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
     * @return array
     */
    public function checkTemplateBlocks()
    {
        $oConfig = $this->getConfig();

        $sModulePath = $this->getModulePath();
        $aMetadataBlocks = $this->getInfo('blocks');
        $aDatabaseBlocks = $this->getModuleBlocks();
        $aMetadataTemplates = $this->getInfo('templates');

        $sModulesDir = $oConfig->getModulesDir();

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

                if (!file_exists($sModulesDir . '/' . $sModulePath . '/' . $aBlock['file']) &&
                    !file_exists($sModulesDir . '/' . $sModulePath . '/out/blocks/' . basename($aBlock['file'])) &&
                    !file_exists($sModulesDir . '/' . $sModulePath . '/out/blocks/' . basename($aBlock['file']) . '.tpl')
                ) {
                    $iState = -2;
                }

                $aResult[$aBlock['template']][$aBlock['file']]['file'] = $iState;
            }
        }

        // Check for redundant blocks for current module.
        if (is_array($aDatabaseBlocks)) {
            foreach ($aDatabaseBlocks as $aDbBlock) {

                $sBaseFile = basename($aDbBlock['OXFILE']);

                if (!isset($aResult[$aDbBlock['OXTEMPLATE']][$aDbBlock['OXFILE']])) {
                    $aResult[$aDbBlock['OXTEMPLATE']][$aDbBlock['OXFILE']] = -1;
                    if (!file_exists($sModulesDir . '/' . $sModulePath . '/' . $aDbBlock['OXFILE']) &&
                        !file_exists($sModulesDir . '/' . $sModulePath . '/out/blocks/' . $sBaseFile) &&
                        !file_exists($sModulesDir . '/' . $sModulePath . '/out/blocks/' . $sBaseFile) . '.tpl'
                    ) {
                        $aResult[$aDbBlock['OXTEMPLATE']][$aDbBlock['OXFILE']]['file'] = -3;
                    }
                }
            }
        }

        // Check if template file exists and block is defined.
        if (is_array($aMetadataBlocks)) {
            foreach ($aMetadataBlocks as $aBlock) {

                // Get template from shop..
                $sTemplate = $oConfig->getTemplatePath($aBlock['template'], false);

                // Get template from shop admin ..
                if (!$sTemplate) {
                    $sTemplate = $oConfig->getTemplatePath($aBlock['template'], true);
                }

                // Get template from module ..
                if (!$sTemplate && isset($aMetadataTemplates[$aBlock['template']])) {

                    $sModulesDir = $oConfig->getModulesDir();

                    if (file_exists($sModulesDir . '/' . $aMetadataTemplates[$aBlock['template']])) {
                        $sTemplate = $sModulesDir . '/' . $aMetadataTemplates[$aBlock['template']];
                    }
                }

                if (empty($sTemplate)) {
                    $aResult[$aBlock['template']][$aBlock['file']]['template'] = -3;
                } else {
                    $sContent = file_get_contents($sTemplate);
                    if (!preg_match('/\[{.*block.* name.*= *"' . $aBlock['block'] . '".*}\]/', $sContent)) {
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
     * @return array
     */
    public function checkModuleSettings()
    {
        $aMetadataSettings = $this->getInfo('settings');
        $aDatabaseSettings = $this->getModuleSettings();

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
     * @return array
     */
    public function checkModuleFiles()
    {
        $aMetadataFiles = $this->getInfo('files');
        $aDatabaseFiles = $this->getModuleFiles();

        $sModulesDir = $this->getConfig()->getModulesDir();

        $aResult = array();

        // Check if all module files are injected.
        if (is_array($aMetadataFiles)) {
            $aMetadataFiles = array_change_key_case($aMetadataFiles, CASE_LOWER);
            foreach ($aMetadataFiles as $sClass => $sFile) {
                $aResult[$sClass][$sFile] = 0;
                if (!file_exists($sModulesDir . '/' . $sFile)) {
                    $aResult[$sClass][$sFile] = -2;
                }
            }
        }

        // Check for redundant or missing module files
        if (is_array($aDatabaseFiles)) {
            foreach ($aDatabaseFiles as $sClass => $sFile) {
                if (!isset($aResult[$sClass][$sFile])) {
                    @$aResult[$sClass][$sFile] = -1;
                    if (!file_exists($sModulesDir . '/' . $sFile)) {
                        @$aResult[$sClass][$sFile] = -3;
                    }
                } elseif ($aResult[$sClass][$sFile] == 0) {
                    @$aResult[$sClass][$sFile] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze templates in metadata ans settings.
     *
     * @return array
     */
    public function checkModuleTemplates()
    {
        $aMetadataTemplates = $this->getInfo('templates');
        $aDatabaseTemplates = $this->getModuleTemplates();

        $sModulesDir = $this->getConfig()->getModulesDir();

        $aResult = array();

        // Check if all module templates are injected.
        if (is_array($aMetadataTemplates)) {
            $aMetadataTemplates = array_change_key_case($aMetadataTemplates, CASE_LOWER);
            foreach ($aMetadataTemplates as $sTemplate => $sFile) {
                $aResult[$sTemplate][$sFile] = 0;
                if (!file_exists($sModulesDir . '/' . $sFile)) {
                    $aResult[$sTemplate][$sFile] = -2;
                }
            }
        }

        // Check for redundant or missing module templates
        if (is_array($aDatabaseTemplates)) {
            foreach ($aDatabaseTemplates as $sTemplate => $sFile) {
                if (!isset($aResult[$sTemplate][$sFile])) {
                    @$aResult[$sTemplate][$sFile] = -1;
                    if (!file_exists($sModulesDir . '/' . $sFile)) {
                        @$aResult[$sTemplate][$sFile] = -3;
                    }
                } elseif ($aResult[$sTemplate][$sFile] == 0) {
                    @$aResult[$sTemplate][$sFile] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze events in metadata ans settings.
     *
     * @return array
     */
    public function checkModuleEvents()
    {
        $aMetadataEvents = $this->getInfo('events');
        $aDatabaseEvents = $this->getModuleEvents();

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
     * @return array
     */
    public function checkModuleVersions()
    {
        $sMetadataVersion = $this->getInfo('version');
        $sDatabaseVersion = $this->getModuleVersion();

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
