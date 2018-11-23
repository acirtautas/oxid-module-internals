<?php

namespace OxidCommunity\ModuleInternals\Core;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\SettingsHandler;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Module\ModuleInstaller;
use OxidEsales\Eshop\Core\Module\ModuleCache;
use OxidEsales\Eshop\Core\Exception\ModuleValidationException;
use OxidProfessionalServices\OxidConsole\Core\Module\ModuleExtensionCleanerDebug;
use Symfony\Component\Console\Output\OutputInterface;
use OxidEsales\Eshop\Core\Module\ModuleVariablesLocator;
/**
 * Module state fixer
 */
class ModuleStateFixer extends ModuleInstaller
{

    public function __construct($cache = null, $cleaner = null){
        $cleaner = oxNew(ModuleExtensionCleanerDebug::class);
        parent::__construct($cache, $cleaner);
    }



    /** @var OutputInterface $_debugOutput */
    protected $_debugOutput;

    /** @var OutputInterface $_debugOutput */
    protected $output;

    protected $needCacheClear = false;
    protected $initialCacheClearDone = false;
    /**
     * @var null|Module $module
     */
    protected $module = null;

    /**
     * Fix module states task runs version, extend, files, templates, blocks,
     * settings and events information fix tasks
     *
     * @param Module      $module
     * @param Config|null $oConfig If not passed uses default base shop config
     */
    public function fix($module, $oConfig = null)
    {
        if ($oConfig !== null) {
            $this->setConfig($oConfig);
        }

        $moduleId = $module->getId();

        if (!$this->initialCacheClearDone) {
            //clearing some cache to be sure that fix runs not against a stale cache
            ModuleVariablesLocator::resetModuleVariables();
            if (extension_loaded('apc') && ini_get('apc.enabled')) {
                apc_clear_cache();
            }
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->output->writeln("initial cache cleared");
            }
            $this->initialCacheClearDone = true;
        }
        $this->module = $module;
        $this->needCacheClear = false;
        $this->restoreModuleInformation($module, $moduleId);
        if ($this->needCacheClear){
            $this->resetModuleCache($module);
            $this->output->writeln("cache cleared for $moduleId");
        }
    }


    //public function resetModuleCache($module = null){
    //  parent::resetCache()
    //}


    /**
     * Add module template files to config for smarty.
     *
     * @param array  $aModuleTemplates Module templates array
     * @param string $sModuleId        Module id
     */
    protected function _addTemplateFiles($aModuleTemplates, $sModuleId)
    {
        $aTemplates = (array) $this->getConfig()->getConfigParam('aModuleTemplates');
        $old = isset($aTemplates[$sModuleId]) ? $aTemplates[$sModuleId] : null;
        if (is_array($aModuleTemplates)) {
            $diff = $this->diff($old,$aModuleTemplates);
            if ($diff) {
                $this->output->writeLn("$sModuleId fixing templates:"  . $old === null ? ' everything ' :  var_export($diff, true));
                $aTemplates[$sModuleId] = $aModuleTemplates;
                $this->_saveToConfig('aModuleTemplates', $aTemplates);
                $this->needCacheClear = true;
            }
        } else {
            if ($old) {
                $this->output->writeLn("$sModuleId unregister templates:");
                $this->_deleteTemplateFiles($sModuleId);
                $this->needCacheClear = true;
            }
        }
    }


    /**
     * Add module files to config for auto loader.
     *
     * @param array  $aModuleFiles Module files array
     * @param string $sModuleId    Module id
     */
    protected function _addModuleFiles($aModuleFiles, $sModuleId)
    {
        $aFiles = (array) $this->getConfig()->getConfigParam('aModuleFiles');

        $old =  isset($aFiles[$sModuleId]) ? $aFiles[$sModuleId] : null;
        if ($aModuleFiles !== null) {
            $aModuleFiles = array_change_key_case($aModuleFiles, CASE_LOWER);
        }

        if (is_array($aModuleFiles)) {
            $diff = $this->diff($old,$aModuleFiles);
            if ($diff) {
                $this->output->writeLn("$sModuleId fixing files:" . $old === null ? ' everything' : var_export($diff, true));
                $aFiles[$sModuleId] = $aModuleFiles;
                $this->_saveToConfig('aModuleFiles', $aFiles);
                $this->needCacheClear = true;
            }
        } else {
            if ($old) {
                $this->output->writeLn("$sModuleId unregister files");
                $this->_deleteModuleFiles($sModuleId);
                $this->needCacheClear = true;
            }
        }

    }


    /**
     * Add module events to config.
     *
     * @param array  $aModuleEvents Module events
     * @param string $sModuleId     Module id
     */
    protected function _addModuleEvents($aModuleEvents, $sModuleId)
    {
        $aEvents = (array) $this->getConfig()->getConfigParam('aModuleEvents');
        $old =  isset($aEvents[$sModuleId]) ? $aEvents[$sModuleId] : null;
        if (is_array($aModuleEvents) && count($aModuleEvents)) {
            $diff = $this->diff($old,$aModuleEvents);
            if ($diff) {
                $aEvents[$sModuleId] = $aModuleEvents;
                $this->output->writeLn("$sModuleId fixing module events:" . $old == null ? ' everything ' : var_export($diff, true));
                $this->_saveToConfig('aModuleEvents', $aEvents);
                $this->needCacheClear = true;
            }
        } else {
            if ($old) {
                $this->output->writeLn("$sModuleId unregister events");
                $this->_deleteModuleEvents($sModuleId);
                $this->needCacheClear = true;
            }
        }

    }

    /**
     * Add module id with extensions to config.
     *
     * @param array  $moduleExtensions Module version
     * @param string $moduleId         Module id
     */
    protected function _addModuleExtensions($moduleExtensions, $moduleId)
    {
        $extensions = (array) $this->getConfig()->getConfigParam('aModuleExtensions');
        $old = isset($extensions[$moduleId]) ? $extensions[$moduleId] : null;
        $old = (array) $old;
        $new = $moduleExtensions === null ? [] : array_values($moduleExtensions);
        if (is_array($moduleExtensions)) {
            $diff = $this->diff($old, $new);
            if ($diff) {
                $extensions[$moduleId] = array_values($moduleExtensions);
                $this->output->writeLn("$moduleId fixing module extensions:" . $old === null ? ' everything ' : var_export($diff, true));
                $this->_saveToConfig('aModuleExtensions', $extensions);
                $this->needCacheClear = true;
            }
        } else {
            $this->output->writeLn("$moduleId unregister module extensions");
            $this->needCacheClear = true;
            $this->_saveToConfig('aModuleExtensions', []);
        }
    }

    /**
     * Add module version to config.
     *
     * @param string $sModuleVersion Module version
     * @param string $sModuleId      Module id
     */
    protected function _addModuleVersion($sModuleVersion, $sModuleId)
    {
        $aVersions = (array) $this->getConfig()->getConfigParam('aModuleVersions');
        $old =  isset($aVersions[$sModuleId]) ? $aVersions[$sModuleId] : '';
        if (is_array($aVersions)) {
            $aVersions[$sModuleId] = $sModuleVersion;
            if ($old !== $sModuleVersion) {
                $this->output->writeLn("$sModuleId fixing module version from $old to $sModuleVersion");
                $aEvents[$sModuleId] = $sModuleVersion;
                $this->_saveToConfig('aModuleVersions', $aVersions);
                $this->needCacheClear = true;
            }
        } else {
            if ($old) {
                $this->output->writeLn("$sModuleId unregister module version");
                $this->_deleteModuleVersions($sModuleId);
                $this->needCacheClear = true;
            }
        }

    }

    /**
     * compares 2 assoc arrays
     * true if there is something changed
     * @param $array1
     * @param $array2
     * @return bool
     */
    protected function diff($array1,$array2){
        if ($array1 === null) {
            if ($array2 === null) {
                return false; //indicate no diff
            }
            return $array2; //full array2 is new
        }
        if ($array2 === null) {
            //indicate that diff is there  (so return a true value) but everthing should be droped
            return 'null';
        }
        $diff = array_merge(array_diff_assoc($array1,$array2),array_diff_assoc($array2,$array1));
        return $diff;
    }


    /**
     * Code taken from OxidEsales\EshopCommunity\Core\Module::activate
     *
     * @param Module $module
     * @param string $moduleId
     *
     * @throws \OxidEsales\Eshop\Core\Exception\StandardException
     */
    private function restoreModuleInformation($module, $moduleId)
    {
        $this->_addExtensions($module);
        $metaDataVersion = $module->getMetaDataVersion();
        $metaDataVersion = $metaDataVersion == '' ? $metaDataVersion = "1.0" : $metaDataVersion;
        if (version_compare($metaDataVersion, '2.0', '<')) {
            $this->_addModuleFiles($module->getInfo("files"), $moduleId);
        }
        $this->_addTemplateBlocks($module->getInfo("blocks"), $moduleId);
        $this->_addTemplateFiles($module->getInfo("templates"), $moduleId);
        $this->_addModuleSettings($module->getInfo("settings"), $moduleId);
        $this->_addModuleVersion($module->getInfo("version"), $moduleId);
        $this->_addModuleExtensions($module->getExtensions(), $moduleId);
        $this->_addModuleEvents($module->getInfo("events"), $moduleId);

        if (version_compare($metaDataVersion, '2.0', '>=')) {
            try {
                $this->setModuleControllers($module->getControllers(), $moduleId, $module);
            } catch (ModuleValidationException $exception) {
                print "[ERROR]: duplicate controllers:" . $exception->getMessage() ."\n";
            }
        }
    }

    /**
     * Get config tables specific module id
     *
     * @param string $moduleId
     * @return string
     */
    protected function getModuleConfigId($moduleId)
    {
        return 'module:' . $moduleId;
    }

    /**
     * Adds settings to database.
     *
     * @param array  $moduleSettings Module settings array
     * @param string $moduleId       Module id
     */
    protected function _addModuleSettings($moduleSettings, $moduleId)
    {
        $config = $this->getConfig();
        $shopId = $config->getShopId();
        if (is_array($moduleSettings)) {
            foreach ($moduleSettings as $setting) {

                $module = $this->getModuleConfigId($moduleId);
                $name = $setting["name"];
                $type = $setting["type"];

                $value = is_null($config->getConfigParam($name)) ? $setting["value"] : $config->getConfigParam($name);

                $changed = $config->saveShopConfVar($type, $name, $value, $shopId, $module);
                if ($changed) {
                    $this->output->writeln("$moduleId: setting for '$name' fixed'");
                    $this->needCacheClear = $this->needCacheClear || $changed;
                }
            }
        }
    }


    /**
     * Add controllers map for a given module Id to config
     *
     * @param array  $moduleControllers Map of controller ids and class names
     * @param string $moduleId          The Id of the module
     */
    protected function setModuleControllers($moduleControllers, $moduleId, $module)
    {
        $classProviderStorage = $this->getClassProviderStorage();
        $dbMap = $classProviderStorage->get();

        $controllersForThatModuleInDb = isset($dbMap[$moduleId]) ? $dbMap[$moduleId] : [];

        $duplicatedKeys = array_intersect_key(array_change_key_case($moduleControllers, CASE_LOWER), $controllersForThatModuleInDb);

        if (array_diff_assoc($moduleControllers,$duplicatedKeys)) {
            $this->output->writeLn("$moduleId fix module ModuleControllers");
            $this->deleteModuleControllers($moduleId);
            $this->resetModuleCache($module);
            $this->validateModuleMetadataControllersOnActivation($moduleControllers);

            $classProviderStorage = $this->getClassProviderStorage();

            $classProviderStorage->add($moduleId, $moduleControllers);
            $this->needCacheClear = true;
        }

    }


    /**
     * Reset module cache
     *
     * @param Module $module
     */
    private function resetModuleCache($module)
    {
        $moduleCache = oxNew(ModuleCache::class, $module);
        $moduleCache->resetCache();
    }

    /**
     * @param $o OutputInterface
     */
    public function setDebugOutput($o)
    {
        $this->_debugOutput = $o;
    }

    /**
     * @param $o OutputInterface
     */
    public function setOutput($o)
    {
        $this->output = $o;
        $this->getModuleCleaner()->setOutput($o);
    }


    /**
     * Add extension to module
     *
     * @param \OxidEsales\Eshop\Core\Module\Module $module
     */
    protected function _addExtensions(\OxidEsales\Eshop\Core\Module\Module $module)
    {
        $aModulesDefault = $this->getConfig()->getConfigParam('aModules');
        $aModules = $this->getModulesWithExtendedClass();
        $aModules = $this->_removeNotUsedExtensions($aModules, $module);


        if ($module->hasExtendClass()) {
            $this->validateMetadataExtendSection($module);
            $aAddModules = $module->getExtensions();
            $aModules = $this->_mergeModuleArrays($aModules, $aAddModules);
        }

        $aModules = $this->buildModuleChains($aModules);
        if ($aModulesDefault != $aModules) {
            $this->needCacheClear = true;
            $onlyInAfterFix = array_diff($aModules, $aModulesDefault);
            $onlyInBeforeFix = array_diff($aModulesDefault, $aModules);
            $this->output->writeLn("[INFO] fixing " . $module->getId());
            foreach ($onlyInAfterFix as $core => $ext) {
                if ($oldChain = $onlyInBeforeFix[$core]) {
                    $newExt = substr($ext, strlen($oldChain));
                    if (!$newExt) {
                        //$newExt = substr($ext, strlen($oldChain));
                        $this->output->writeLn("[INFO] remove ext for $core");
                        $this->output->writeLn("[INFO] old: $oldChain");
                        $this->output->writeLn("[INFO] new: $ext");
                        //$this->_debugOutput->writeLn("[ERROR] extension chain is corrupted for this module");
                        //return;
                        continue;
                    } else {
                        $this->output->writeLn("[INFO] append $core => ...$newExt");
                    }
                    unset($onlyInBeforeFix[$core]);
                } else {
                    $this->output->writeLn("[INFO] add $core => $ext");
                }
            }
            foreach ($onlyInBeforeFix as $core => $ext) {
                $this->output->writeLn("[INFO] remove $core => $ext");
            }
            $this->_saveToConfig('aModules', $aModules);
        }
    }



    /**
     * Add module templates to database.
     *
     * @deprecated please use setTemplateBlocks this method will be removed because
     * the combination of deleting and adding does unnessery writes and so it does not scale
     * also it's more likely to get race conditions (in the moment the blocks are deleted)
     *
     * @param array  $moduleBlocks Module blocks array
     * @param string $moduleId     Module id
     */
    protected function _addTemplateBlocks($moduleBlocks, $moduleId)
    {
        /*
        $shopid = $this->getConfig()->getShopId();
        $moduleBlocksInDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll("SELECT `OXPOS` as `position`,`OXTHEME` as theme, `OXTEMPLATE` as template, `OXBLOCKNAME` as block, `OXFILE` as file FROM oxtplblocks WHERE oxmodule = '$moduleId' AND oxshopid = '$shopid' AND `OXACTIVE` = 1 order by `OXPOS`");
        check and set $this->needCacheClear = true;
        */

        $this->setTemplateBlocks($moduleBlocks, $moduleId);
    }

    /**
     * Set module templates in the database.
     * we do not use delete and add combination because
     * the combination of deleting and adding does unnessery writes and so it does not scale
     * also it's more likely to get race conditions (in the moment the blocks are deleted)
     * @todo extract oxtplblocks query to ModuleTemplateBlockRepository
     *
     * @param array  $moduleBlocks Module blocks array
     * @param string $moduleId     Module id
     */
    protected function setTemplateBlocks($moduleBlocks, $moduleId)
    {
        if (!is_array($moduleBlocks)) {
            $moduleBlocks = array();
        }
        $shopId = $this->getConfig()->getShopId();
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $knownBlocks = ['dummy']; // Start with a dummy value to prevent having an empty list in the NOT IN statement.
        $rowsEffected = 0;
        foreach ($moduleBlocks as $moduleBlock) {
            $blockId = md5($moduleId . json_encode($moduleBlock) . $shopId);
            $knownBlocks[] = $blockId;

            $template = $moduleBlock["template"];
            $position = isset($moduleBlock['position']) && is_numeric($moduleBlock['position']) ?
                intval($moduleBlock['position']) : 1;

            $block = $moduleBlock["block"];
            $filePath = $moduleBlock["file"];
            $theme = isset($moduleBlock['theme']) ? $moduleBlock['theme'] : '';

            $sql = "INSERT INTO `oxtplblocks` (`OXID`, `OXACTIVE`, `OXSHOPID`, `OXTHEME`, `OXTEMPLATE`, `OXBLOCKNAME`, `OXPOS`, `OXFILE`, `OXMODULE`)
                     VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                      `OXID` = VALUES(OXID),
                      `OXACTIVE` = VALUES(OXACTIVE),
                      `OXSHOPID` = VALUES(OXSHOPID),
                      `OXTHEME` = VALUES(OXTHEME),
                      `OXTEMPLATE` = VALUES(OXTEMPLATE),
                      `OXBLOCKNAME` = VALUES(OXBLOCKNAME),
                      `OXPOS` = VALUES(OXPOS),
                      `OXFILE` = VALUES(OXFILE),
                      `OXMODULE` = VALUES(OXMODULE)";

            $rowsEffected += $db->execute($sql, array(
                $blockId,
                $shopId,
                $theme,
                $template,
                $block,
                $position,
                $filePath,
                $moduleId
            ));
        }

        $listOfKnownBlocks = join(',', $db->quoteArray($knownBlocks));
        $deleteblocks = "DELETE FROM oxtplblocks WHERE OXSHOPID = ? AND OXMODULE = ? AND OXID NOT IN ({$listOfKnownBlocks});";

        $rowsEffected += $db->execute(
            $deleteblocks,
            array($shopId, $moduleId)
        );

        if ($rowsEffected) {
            $this->needCacheClear = true;
        }
    }
}