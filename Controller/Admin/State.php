<?php

namespace OxCom\ModuleInternals\Controller\Admin;

use OxCom\ModuleInternals\Core\FixHelper as FixHelper;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Module\ModuleCache as ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList as ModuleList;
use OxidEsales\Eshop\Core\Module\Module as Module;
use OxidEsales\Eshop\Core\Registry as Registry;

/**
 * Module internals tools.
 *
 * @author Oxid Community
 */

/**
 * Module state checker, compares module data across different storage levels (metadata file / database / configuration).
 */
class State extends AdminController
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
     * init current Module
     * State constructor.
     */
    public function __construct()
    {
        $this->getModule();
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

            $module = oxNew(Module::class);
            $module->load($sModuleId);
            $this->_oModule = $module;
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
        //valid for all metadata versions
        $this->addTplParam('oxid', $this->getModule()->getId());
        $this->addTplParam('aExtended', $this->checkExtendedClasses());
        $this->addTplParam('aBlocks', $this->checkTemplateBlocks());
        $this->addTplParam('aSettings', $this->checkModuleSettings());
        $this->addTplParam('aTemplates', $this->checkModuleTemplates());

        //valid not for  metadata version 1.*
        if ($this->getModule()->checkMetadataVersion('1.0') || $this->getModule()->checkMetadataVersion('1.1')) {
            $this->addTplParam('aFiles', $this->getModule()->checkModuleFiles());
        }

        //valid  for  metadata version 1.1 and 2.0
        if ($this->getModule()->checkMetadataVersion('1.1') || $this->getModule()->checkMetadataVersion('2.0')) {
            $this->addTplParam('aEvents', $this->checkModuleEvents());
            $this->addTplParam('aVersions', $this->getModule()->checkModuleVersions() ? 1 : -1 );
        }

        /**
         * @todo check if files is set - should'nt be
         */
        if ($this->getModule()->checkMetadataVersion('2.0'))
            $this->addTplParam('aControllers', $this->checkModuleController());


        $this->addTplParam(
            'sState',
            [
                -3 => 'sfatals',
                -2 => 'sfatalm',
                -1 => 'serror',
                0  => 'swarning',
                1  => 'sok',
            ]
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

    /**
     * Analyze extended class information in metadata and database.
     *
     * @return array
     */
    public function checkExtendedClasses()
    {
        $oModule = $this->getModule();
        $sModulePath = $oModule->getModulePath();

        $aMetadataExtend = $oModule->getInfo('extend');
        $oxidConfig = Registry::getConfig();

        if (method_exists($oxidConfig, 'getModulesWithExtendedClass')) {
            $aAllModules = $oxidConfig->getModulesWithExtendedClass();
        } else {
            $aAllModules = $oxidConfig->getAllModules();
        }

        $aResult = [];
        $sModulesDir = Registry::getConfig()->getModulesDir(TRUE);

        // Check if all classes are extended.
        if (is_array($aMetadataExtend)) {
            /**
             * only convert class names to lower if we don't use namespace
             */
            if (!$oModule->checkMetadataVersion('2.0'))
                $aMetadataExtend = array_change_key_case($aMetadataExtend, CASE_LOWER);

            foreach ($aMetadataExtend as $sClassName => $sModuleName) {
                $iState = 0;
                if (is_array($aAllModules) && isset($aAllModules[$sClassName])) {
                    // Is module extending class
                    if (is_array($aAllModules[$sClassName])) {
                        $iState = in_array($sModuleName, $aAllModules[$sClassName]) ? 1 : 0;
                    }
                }

                if ($oModule->checkFileExists($sModulesDir, $sModuleName))
                    $aResult[$sClassName][$sModuleName] = $iState;
                else
                    $aResult[$sClassName][$sModuleName] = -2; // class sfatalm
            }
        }

        // Check for redundant extend data by path
        if ($sModulePath && is_array($aAllModules)) {
            foreach ($aAllModules as $sClassName => $mModuleName) {
                if (is_array($mModuleName)) {
                    foreach ($mModuleName as $sModuleName) {
                        if ($oModule->checkMetadataVersion('2.0')) {
                            if (!isset($aResult[$sClassName][$sModuleName])) {
                                $aResult[$sClassName][$sModuleName] = -1;
                            }
                        } else {
                            if (!isset($aResult[$sClassName][$sModuleName]) && strpos($sModuleName, $sModulePath . '/') === 0) {
                                $aResult[$sClassName][$sModuleName] = -1;
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
     * @todo debug $aBlock
     * @return array
     */
    public function checkTemplateBlocks()
    {
        $oModule = $this->getModule();
        $sModulePath = $oModule->getModulePath();
        $aMetadataBlocks = $oModule->getInfo('blocks');
        $aDatabaseBlocks = $oModule->getModuleBlocks();
        $aMetadataTemplates = $oModule->getInfo('templates');

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
                $sTemplate = Registry::getConfig()->getTemplatePath($aBlock['template'], FALSE);

                // Get template from shop admin ..
                if (!$sTemplate) {
                    $sTemplate = Registry::getConfig()->getTemplatePath($aBlock['template'], TRUE);
                }

                // Get template from module ..
                if (!$sTemplate && isset($aMetadataTemplates[$aBlock['template']])) {

                    $sModulesDir = Registry::getConfig()->getModulesDir();

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
        $oModule = $this->getModule();

        $aMetadataSettings = $oModule->getInfo('settings');
        $aDatabaseSettings = $oModule->getModuleSettings();

        $aResult = [];

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
        $oModule = $this->getModule();
        $aMetadataFiles = $oModule->getInfo('files');
        $aDatabaseFiles = $oModule->getModuleEntries(ModuleList::MODULE_KEY_FILES);

        $sModulesDir = Registry::getConfig()->getModulesDir();

        $aResult = [];

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
     * Analyze controller in metadata ans settings.
     *
     * @return array
     */
    public function checkModuleController()
    {
        $oModule = $this->getModule();
        $aMetadataFiles = $oModule->getInfo('controllers');
        $aDatabaseFiles = $oModule->getModuleEntries(ModuleList::MODULE_KEY_CONTROLLERS);

        $sModulesDir = Registry::getConfig()->getModulesDir();

        $aResult = [];

        // Check if all module files are injected.
        if (is_array($aMetadataFiles)) {
            if (!$oModule->checkMetadataVersion('2.0'))
                $aMetadataFiles = array_change_key_case($aMetadataFiles, CASE_LOWER);
            foreach ($aMetadataFiles as $sClass => $sFile) {
                $aResult[$sClass][$sFile] = 0;

                if ($oModule->checkMetadataVersion('2.0')) {
                    $composerClassLoader = include VENDOR_PATH . 'autoload.php';
                    if (!$composerClassLoader->findFile($sFile)) {
                        $aResult[$sClass][$sFile] = -2;
                    }
                } else {
                    if (!file_exists($sModulesDir . '/' . $sFile)) {
                        $aResult[$sClass][$sFile] = -2;
                    }
                }
            }
        }

        // Check for redundant or missing module files
        if (is_array($aDatabaseFiles)) {
            foreach ($aDatabaseFiles as $sClass => $sFile) {
                if (!isset($aResult[$sClass][$sFile])) {
                    @$aResult[$sClass][$sFile] = -1;
                    if ($oModule->checkMetadataVersion('2.0')) {
                        $composerClassLoader = include VENDOR_PATH . 'autoload.php';
                        if (!$composerClassLoader->findFile($sFile)) {
                            @$aResult[$sClass][$sFile] = -2;
                        }
                    } else {
                        if (!file_exists($sModulesDir . '/' . $sFile)) {
                            @$aResult[$sClass][$sFile] = -3;
                        }
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
        $oModule = $this->getModule();

        $aMetadataTemplates = $oModule->getInfo('templates');
        $aDatabaseTemplates = $oModule->getModuleEntries(ModuleList::MODULE_KEY_TEMPLATES);

        $sModulesDir = Registry::getConfig()->getModulesDir();

        $aResult = [];

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
        $oModule = $this->getModule();

        $aMetadataEvents = $oModule->getInfo('events');
        $aDatabaseEvents = $oModule->getModuleEntries(ModuleList::MODULE_KEY_EVENTS);

        $aResult = [];

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
}
