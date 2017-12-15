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

namespace OxidCommunity\ModuleInternals\Core;

use \OxidEsales\Eshop\Core\DatabaseProvider as DatabaseProvider;
use \OxidEsales\Eshop\Core\Registry as Registry;
use \OxidEsales\Eshop\Core\Module\ModuleList as ModuleList;

/**
 * Class Metadata
 * extending OxidEsales\Eshop\Core\Module\Module as Module
 */
class Module extends Module_parent
{

    /**
     * Get template blocks defined in database.
     *
     * @return array
     */
    public function getModuleBlocks()
    {
        $aResults = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->select(
            'SELECT * FROM oxtplblocks WHERE oxModule = ? AND oxshopid = ?',
            [$this->getId(), Registry::getConfig()->getShopId()]
        );

        return $aResults->fetchAll();
    }

    /**
     * Get module settings defined in database.
     *
     * @return array
     */
    public function getModuleSettings()
    {
        $aResult = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->select(
            'SELECT * FROM oxconfig WHERE oxModule = ? AND oxshopid = ?',
            [sprintf('module:%s', $this->getId()), Registry::getConfig()->getShopId()]
        );

        return $aResult->fetchAll();
    }

    /**
     * Check supported metadata version
     *
     * @return bool
     */
    public function isMetadataSupported()
    {
        $sMetadataVersion = $this->getMetaDataVersion();

        $sLatestVersion = '1.0';

        if (method_exists('oxModuleList', 'getModuleVersions') || method_exists('oxModule', 'getModuleEvents')) {
            $sLatestVersion = '1.1';
        }

        if (method_exists(ModuleList::class, 'getModuleConfigParametersByKey')) {
            $sLatestVersion = '2.0';
        }

        return version_compare($sLatestVersion, $sMetadataVersion) >= 0;
    }

    /**
     * check if current metadata version is $sVersion
     *
     * @param $sVersion
     *
     * @return bool
     */
    public function checkMetadataVersion($sVersion)
    {
        return version_compare($this->getMetaDataVersion(), $sVersion) == 0;
    }

    /**
     * Returns array of module files / entries in metadata.php
     * like 'template' => / 'blocks' => ....
     * possible entries
     *
     * ModuleList::MODULE_KEY_FILES = 'files'
     * ModuleList::MODULE_KEY_CONTROLLERS = 'controllers'
     * ModuleList::MODULE_KEY_EVENTS => 'events'
     * ModuleList::MODULE_KEY_VERSIONS => module version
     *
     * @return array
     */
    public function getModuleEntries($sType)
    {
        $aReturn = [];
        $aList = oxNew(ModuleList::class)->getModuleConfigParametersByKey($sType);

        if (isset($aList[ $this->getId() ])) {
            $aReturn = $aList[ $this->getId() ];
        }

        return $aReturn;
    }

    /**
     * checks if module file exists on directory
     * switches between metadata version for checking
     * checks also for namespace class if metadata version = 2.0
     *
     * @param string $sModulesDir
     * @param string $sClassName
     * @param string $sExtention
     *
     * @return bool
     */
    public function checkFileExists($sModulesDir = null, $sClassName, $sExtention = '.php')
    {
        if ($this->checkMetadataVersion('2.0')) {
            $composerClassLoader = include VENDOR_PATH . 'autoload.php';

            return $composerClassLoader->findFile($sClassName);
        } else {
            return file_exists($sModulesDir . $sClassName . $sExtention);
        }
    }

    /**
     * Analyze versions in metadata
     * checks if metadata version is same as database entry for metadata
     *
     * @return array
     */
    public function checkModuleVersions()
    {
        $sMetadataVersion = $this->getInfo('version');
        $sDatabaseVersion = $this->getModuleEntries(ModuleList::MODULE_KEY_VERSIONS);

        $aResult = [];

        // Check version..
        if ($sMetadataVersion) {
            $aResult[ $sMetadataVersion ] = 0;
        }

        // Check for versions match injected.
        if ($sDatabaseVersion) {
            if (!isset($aResult[ $sDatabaseVersion ])) {
                $aResult[ $sDatabaseVersion ] = -1;
            } else {
                $aResult[ $sDatabaseVersion ] = 1;
            }
        }

        return $aResult;
    }

    /**
     * Analyze extended class information in metadata and database.
     *
     * @return array
     */
    public function checkExtendedClasses()
    {
        $sModulePath = $this->getModulePath();

        $aMetadataExtend = $this->getInfo('extend');
        $oxidConfig = Registry::getConfig();

        if (method_exists($oxidConfig, 'getModulesWithExtendedClass')) {
            $aAllModules = $oxidConfig->getModulesWithExtendedClass();
        } else {
            $aAllModules = $oxidConfig->getAllModules();
        }

        $aResult = [];
        $sModulesDir = Registry::getConfig()->getModulesDir(true);

        // Check if all classes are extended.
        if (is_array($aMetadataExtend)) {
            /**
             * only convert class names to lower if we don't use namespace
             */
            if (!$this->checkMetadataVersion('2.0'))
                $aMetadataExtend = array_change_key_case($aMetadataExtend, CASE_LOWER);

            foreach ($aMetadataExtend as $sClassName => $sModuleName) {
                $iState = 0;
                if (is_array($aAllModules) && isset($aAllModules[ $sClassName ])) {
                    // Is module extending class
                    if (is_array($aAllModules[ $sClassName ])) {
                        $iState = in_array($sModuleName, $aAllModules[ $sClassName ]) ? 1 : 0;
                    }
                }

                if ($this->checkFileExists($sModulesDir, $sModuleName))
                    $aResult[ $sClassName ][ $sModuleName ] = $iState;
                else
                    $aResult[ $sClassName ][ $sModuleName ] = -2; // class sfatalm
            }
        }

        // Check for redundant extend data by path
        if ($sModulePath && is_array($aAllModules)) {
            foreach ($aAllModules as $sClassName => $mModuleName) {
                if (is_array($mModuleName)) {
                    foreach ($mModuleName as $sModuleName) {
                        /**
                         * we don't need to check for filesystem directory - we only use namespaces in version 2.0
                         */
                        if ($this->checkMetadataVersion('2.0')) {
                            $moduleNameSpace = $this->getModuleNameSpace($sModulePath);
                            if (!isset($aResult[ $sClassName ][ $sModuleName ]) && strpos($sModuleName, $moduleNameSpace) === 0) {
                                $aResult[ $sClassName ][ $sModuleName ] = -1;
                            }
                        } else {
                            if (!isset($aResult[ $sClassName ][ $sModuleName ]) && strpos($sModuleName, $sModulePath . '/') === 0) {
                                $aResult[ $sClassName ][ $sModuleName ] = -1;
                            }
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
        $sModulePath = $this->getModulePath();
        $aMetadataBlocks = $this->getInfo('blocks');
        $aDatabaseBlocks = $this->getModuleBlocks();
        $aMetadataTemplates = $this->getInfo('templates');

        $sModulesDir = Registry::getConfig()->getModulesDir();

        $aResult = [];

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

                $aResult[ $aBlock['template'] ][ $aBlock['file'] ]['file'] = $iState;
            }
        }

        // Check for redundant blocks for current module.
        if (is_array($aDatabaseBlocks)) {
            foreach ($aDatabaseBlocks as $aDbBlock) {

                $sBaseFile = basename($aDbBlock['OXFILE']);

                if (!isset($aResult[ $aDbBlock['OXTEMPLATE'] ][ $aDbBlock['OXFILE'] ])) {
                    $aResult[ $aDbBlock['OXTEMPLATE'] ][ $aDbBlock['OXFILE'] ] = -1;
                    if (!file_exists($sModulesDir . '/' . $sModulePath . '/' . $aDbBlock['OXFILE']) &&
                        !file_exists($sModulesDir . '/' . $sModulePath . '/out/blocks/' . $sBaseFile) &&
                        !file_exists($sModulesDir . '/' . $sModulePath . '/out/blocks/' . $sBaseFile) . '.tpl'
                    ) {
                        $aResult[ $aDbBlock['OXTEMPLATE'] ][ $aDbBlock['OXFILE'] ]['file'] = -3;
                    }
                }
            }
        }

        // Check if template file exists and block is defined.
        if (is_array($aMetadataBlocks)) {
            foreach ($aMetadataBlocks as $aBlock) {

                // Get template from shop..
                $sTemplate = Registry::getConfig()->getTemplatePath($aBlock['template'], false);

                // Get template from shop admin ..
                if (!$sTemplate) {
                    $sTemplate = Registry::getConfig()->getTemplatePath($aBlock['template'], true);
                }

                // Get template from module ..
                if (!$sTemplate && isset($aMetadataTemplates[ $aBlock['template'] ])) {

                    $sModulesDir = Registry::getConfig()->getModulesDir();

                    if (file_exists($sModulesDir . '/' . $aMetadataTemplates[ $aBlock['template'] ])) {
                        $sTemplate = $sModulesDir . '/' . $aMetadataTemplates[ $aBlock['template'] ];
                    }
                }

                if (empty($sTemplate)) {
                    $aResult[ $aBlock['template'] ][ $aBlock['file'] ]['template'] = -3;
                } else {
                    $sContent = file_get_contents($sTemplate);
                    if (!preg_match('/\[{.*block.* name.*= *"' . $aBlock['block'] . '".*}\]/', $sContent)) {
                        $aResult[ $aBlock['template'] ][ $aBlock['file'] ]['template'] = -1;
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

        $aResult = [];

        // Check if all settings are injected.
        if (is_array($aMetadataSettings)) {
            foreach ($aMetadataSettings as $aData) {
                $sName = $aData['name'];
                $aResult[ $sName ] = 0;
            }
        }

        // Check for redundant settings for current module.
        if (is_array($aDatabaseSettings)) {
            foreach ($aDatabaseSettings as $aData) {
                $sName = $aData['OXVARNAME'];

                if (!isset($aResult[ $sName ])) {
                    $aResult[ $sName ] = -1;
                } else {
                    $aResult[ $sName ] = 1;
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
        $aDatabaseTemplates = $this->getModuleEntries(ModuleList::MODULE_KEY_TEMPLATES);

        $sModulesDir = Registry::getConfig()->getModulesDir();

        $aResult = [];

        // Check if all module templates are injected.
        if (is_array($aMetadataTemplates)) {
            $aMetadataTemplates = array_change_key_case($aMetadataTemplates, CASE_LOWER);
            foreach ($aMetadataTemplates as $sTemplate => $sFile) {
                $aResult[ $sTemplate ][ $sFile ] = 0;
                if (!file_exists($sModulesDir . '/' . $sFile)) {
                    $aResult[ $sTemplate ][ $sFile ] = -2;
                }
            }
        }

        // Check for redundant or missing module templates
        if (is_array($aDatabaseTemplates)) {
            foreach ($aDatabaseTemplates as $sTemplate => $sFile) {
                if (!isset($aResult[ $sTemplate ][ $sFile ])) {
                    @$aResult[ $sTemplate ][ $sFile ] = -1;
                    if (!file_exists($sModulesDir . '/' . $sFile)) {
                        @$aResult[ $sTemplate ][ $sFile ] = -3;
                    }
                } elseif ($aResult[ $sTemplate ][ $sFile ] == 0) {
                    @$aResult[ $sTemplate ][ $sFile ] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * Analyze controller in metadata
     *
     * @return array
     */
    public function checkModuleController()
    {
        $aMetadataFiles = $this->getInfo('controllers');
        $aDatabaseFiles = $this->getModuleEntries(ModuleList::MODULE_KEY_CONTROLLERS);

        return $this->checkModuleFileConsistency($aMetadataFiles, $aDatabaseFiles);
    }

    /**
     * Analyze file in metadata
     *
     * @return array
     */
    public function checkModuleFiles()
    {
        $aMetadataFiles = $this->getInfo('files');
        $aDatabaseFiles = $this->getModuleEntries(ModuleList::MODULE_KEY_FILES);

        return $this->checkModuleFileConsistency($aMetadataFiles, $aDatabaseFiles);
    }

    /**
     * checks for database entries and filesystem check
     *
     * @param $aMetadataFiles
     * @param $aDatabaseFiles
     *
     * @return array
     */
    public function checkModuleFileConsistency($aMetadataFiles, $aDatabaseFiles)
    {
        $aResult = [];

        // Check if all module files are injected.
        if (is_array($aMetadataFiles)) {
            if (!$this->checkMetadataVersion('2.0')) {
                $aMetadataFiles = array_change_key_case($aMetadataFiles, CASE_LOWER);
                $sModulesDir = Registry::getConfig()->getModulesDir();
            }
            foreach ($aMetadataFiles as $sClass => $sFile) {
                $aResult[ $sClass ][ $sFile ] = 0;

                if (!$this->checkFileExists($sModulesDir, $sFile, null))
                    $aResult[ $sClass ][ $sFile ] = -2;
            }
        }

        // Check for redundant or missing module files
        if (is_array($aDatabaseFiles)) {
            foreach ($aDatabaseFiles as $sClass => $sFile) {
                if (!isset($aResult[ $sClass ][ $sFile ])) {
                    @$aResult[ $sClass ][ $sFile ] = -1;
                    /**
                     * @todo update to $this->checkFileExists()
                     */
                    if ($this->checkMetadataVersion('2.0')) {
                        $composerClassLoader = include VENDOR_PATH . 'autoload.php';
                        if (!$composerClassLoader->findFile($sFile)) {
                            @$aResult[ $sClass ][ $sFile ] = -2;
                        }
                    } else {
                        if (!file_exists($sModulesDir . $sFile)) {
                            @$aResult[ $sClass ][ $sFile ] = -3;
                        }
                    }
                } elseif ($aResult[ $sClass ][ $sFile ] == 0) {
                    @$aResult[ $sClass ][ $sFile ] = 1;
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
        $aDatabaseEvents = $this->getModuleEntries(ModuleList::MODULE_KEY_EVENTS);

        $aResult = [];

        // Check if all events are injected.
        if (is_array($aMetadataEvents)) {
            foreach ($aMetadataEvents as $sEvent => $mCallback) {
                $sCallback = print_r($mCallback, 1);
                $aResult[ $sEvent ][ $sCallback ] = 0;
            }
        }

        // Check for redundant or missing events.
        if (is_array($aDatabaseEvents)) {
            foreach ($aDatabaseEvents as $sEvent => $mCallback) {
                $sCallback = print_r($mCallback, 1);
                if (!isset($aResult[ $sEvent ][ $sCallback ])) {
                    $aResult[ $sEvent ][ $sCallback ] = -1;
                } else {
                    $aResult[ $sEvent ][ $sCallback ] = 1;
                }
            }
        }

        return $aResult;
    }

    /**
     * @param string $sModulePath
     *
     * @return string
     */
    public function getModuleNameSpace($sModulePath)
    {
        $moduleNameSpace = '';
        $composerClassLoader = include VENDOR_PATH . 'autoload.php';
        $nameSpacePrefixes = $composerClassLoader->getPrefixesPsr4();
        foreach ($nameSpacePrefixes as $nameSpacePrefix => $paths) {
            foreach ($paths as $path) {
                if (strpos($path, $sModulePath) !== false) {
                    $moduleNameSpace = $nameSpacePrefix;

                    return $moduleNameSpace;
                }
            }
        }

        return $moduleNameSpace;
    }
}