<?php

namespace OxCom\ModuleInternals\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Module\ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class ac_module_internals_helper
 *
 * Fix helper service: fix module states and configuration.
 */
class FixHelper
{
    /** @var Module */
    protected $_oModule;

    /** @var ModuleList */
    protected $_oModuleList;

    /** @var ModuleCache */
    protected $_oModuleCache;

    /**
     * Injects helper parameters
     *
     * @param Module      $oModule
     * @param ModuleList  $oModuleList
     * @param ModuleCache $oModuleCache
     */
    public function __construct(Module $oModule, ModuleList $oModuleList, ModuleCache $oModuleCache)
    {
        $this->_oModule      = $oModule;
        $this->_oModuleList  = $oModuleList;
        $this->_oModuleCache = $oModuleCache;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->_oModule;
    }

    /**
     * @param Module $oModule
     */
    public function setModule(Module $oModule)
    {
        $this->_oModule = $oModule;
    }

    /**
     * @return ModuleList
     */
    public function getModuleList()
    {
        return $this->_oModuleList;
    }

    /**
     * @param ModuleList $oModuleList
     */
    public function setModuleList(ModuleList $oModuleList)
    {
        $this->_oModuleList = $oModuleList;
    }

    /**
     * @return ModuleCache
     */
    public function getModuleCache()
    {
        return $this->_oModuleCache;
    }

    /**
     * @param ModuleCache $oModuleCache
     */
    public function setModuleCache(ModuleCache $oModuleCache)
    {
        $this->_oModuleCache = $oModuleCache;
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
     * Returns module info
     *
     * @param string $sName
     * @param int    $iLang
     *
     * @return array
     */
    public function getInfo($sName, $iLang = null)
    {
        return $this->getModule()->getInfo($sName, $iLang);
    }

    /**
     * Fixes module version
     */
    public function fixVersion()
    {
        $sVersion  = $this->getInfo('version');
        $aVersions = (array)Registry::getConfig()->getConfigParam('aModuleVersions');
        if (is_array($aVersions)) {
            $aVersions[$this->getModuleId()] = $sVersion;
        }

        $this->_saveToConfig('aModuleVersions', $aVersions);
        $this->_clearCache();
    }

    /**
     * Fixes extension chain
     */
    public function fixExtend()
    {
        $aExtend           = $this->getInfo('extend');
        $aInstalledModules = Registry::getConfig()->getModulesWithExtendedClass();

        $sModulePath = $this->getModulePath();

        // Remove extended modules by path
        if ($sModulePath && is_array($aInstalledModules)) {
            foreach ($aInstalledModules as $sClassName => $mModuleName) {
                if (is_array($mModuleName)) {
                    foreach ($mModuleName as $sKey => $sModuleName) {
                        /**
                         * @todo metadata version 2.0 - version 6 donät have modulepath in classname
                         */
                        if (strpos($sModuleName, $sModulePath . '/') === 0) {
                            unset($aInstalledModules[$sClassName][$sKey]);
                        }
                    }
                }
            }
        }

        $aModules = $this->_mergeModuleArrays($aInstalledModules, $aExtend);
        $aModules = $this->getModuleList()->buildModuleChains($aModules);

        $this->_saveToConfig('aModules', $aModules);
        $this->_clearCache();
    }

    /**
     * Files files config
     */
    public function fixFiles()
    {
        $aModuleFiles = $this->getInfo('files');
        $aFiles       = (array)Registry::getConfig()->getConfigParam('aModuleFiles');

        if (is_array($aModuleFiles)) {
            $aFiles[$this->getModuleId()] = array_change_key_case($aModuleFiles, CASE_LOWER);
        }

        $this->_saveToConfig('aModuleFiles', $aFiles);
        $this->_clearCache();
    }

    /**
     * Fixes templates config
     */
    public function fixTemplates()
    {
        $aModuleTemplates = $this->getInfo('templates');

        $aTemplates = (array)Registry::getConfig()->getConfigParam('aModuleTemplates');
        if (is_array($aModuleTemplates)) {
            $aTemplates[$this->getModuleId()] = $aModuleTemplates;
        }

        $this->_saveToConfig('aModuleTemplates', $aTemplates);
        $this->_clearCache();
    }

    /**
     * Fixes blocks config
     */
    public function fixBlocks()
    {
        $oConfig       = Registry::getConfig();
        $aModuleBlocks = $this->getInfo('blocks');
        $sShopId       = $oConfig->getShopId();
        $oDb           = DatabaseProvider::getDb();

        // Cleanup !!!
        $oDb->execute(
            'DELETE FROM oxtplblocks WHERE oxmodule = ? AND oxshopid = ?',
            array($this->getModuleId(), $sShopId)
        );

        if (is_array($aModuleBlocks)) {

            $sSql = '
                INSERT INTO `oxtplblocks`
                (`OXID`, `OXACTIVE`, `OXSHOPID`, `OXTEMPLATE`, `OXBLOCKNAME`, `OXPOS`, `OXFILE`, `OXMODULE`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ';

            foreach ($aModuleBlocks as $aValue) {
                $sOxId     = Registry::get('oxUtilsObject')->generateUId();
                $sTemplate = $aValue['template'];
                $iPosition = $aValue['position'] ? $aValue['position'] : 1;
                $sBlock    = $aValue['block'];
                $sFile     = $aValue['file'];

                $oDb->execute(
                    $sSql,
                    array($sOxId, 1, $sShopId, $sTemplate, $sBlock, $iPosition, $sFile, $this->getModuleId())
                );
            }
        }

        $this->_clearCache();
    }

    /**
     * Fixes settings config
     */
    public function fixSettings()
    {
        $aModuleSettings = $this->getInfo('settings');
        $oConfig         = Registry::getConfig();
        $sShopId         = $oConfig->getShopId();
        $oDb             = DatabaseProvider::getDb();

        // Cleanup !!!
        $oDb->execute(
            'DELETE FROM oxconfig WHERE oxmodule = ? AND oxshopid = ?',
            array(sprintf('module:%s', $this->getModuleId()), $sShopId)
        );
        $oDb->execute('DELETE FROM oxconfigdisplay WHERE oxcfgmodule = ?', array($this->getModuleId()));

        if (is_array($aModuleSettings)) {

            foreach ($aModuleSettings as $aValue) {
                $sOxId = Registry::get('oxUtilsObject')->generateUId();

                $sModule = 'module:' . $this->getModuleId();
                $sName   = $aValue['name'];
                $sType   = $aValue['type'];
                $sValue  = is_null($oConfig->getConfigParam($sName)) ? $aValue['value'] : $oConfig->getConfigParam(
                    $sName
                );
                $sGroup  = $aValue['group'];

                $sConstraints = '';
                if ($aValue['constraints']) {
                    $sConstraints = $aValue['constraints'];
                } elseif ($aValue['constrains']) {
                    $sConstraints = $aValue['constrains'];
                }

                $iPosition = $aValue['position'] ? $aValue['position'] : 1;

                $oConfig->setConfigParam($sName, $sValue);
                $oConfig->saveShopConfVar($sType, $sName, $sValue, $sShopId, $sModule);

                $sInsertSql = '
                    INSERT INTO `oxconfigdisplay`
                    (`OXID`, `OXCFGMODULE`, `OXCFGVARNAME`, `OXGROUPING`, `OXVARCONSTRAINT`, `OXPOS`)
                    VALUES
                    (?, ?, ?, ?, ?, ?)
                ';

                $oDb->execute($sInsertSql, array($sOxId, $sModule, $sName, $sGroup, $sConstraints, $iPosition));
            }
        }

        $this->_clearCache();
    }

    /**
     * Fixes events config
     */
    public function fixEvents()
    {
        $aModuleEvents = $this->getInfo('events');
        $aEvents       = (array)Registry::getConfig()->getConfigParam('aModuleEvents');

        if (is_array($aEvents)) {
            $aEvents[$this->getModuleId()] = $aModuleEvents;
        }

        $this->_saveToConfig('aModuleEvents', $aEvents);
        $this->_clearCache();
    }

    /**
     * Clears module related cache
     */
    protected function _clearCache()
    {
        $this->getModuleCache()->resetCache();
    }

    /**
     * Merge two nested module arrays together so that the values of
     * $aAddModuleArray are appended to the end of the $aAllModuleArray
     *
     * @param array $aAllModuleArray All Module array (nested format)
     * @param array $aAddModuleArray Added Module array (nested format)
     *
     * @return array
     */
    protected function _mergeModuleArrays($aAllModuleArray, $aAddModuleArray)
    {
        if (is_array($aAllModuleArray) && is_array($aAddModuleArray)) {
            foreach ($aAddModuleArray as $sClass => $aModuleChain) {
                if (!is_array($aModuleChain)) {
                    $aModuleChain = array($aModuleChain);
                }
                if (isset($aAllModuleArray[$sClass])) {
                    foreach ($aModuleChain as $sModule) {
                        if (!in_array($sModule, $aAllModuleArray[$sClass])) {
                            $aAllModuleArray[$sClass][] = $sModule;
                        }
                    }
                } else {
                    $aAllModuleArray[$sClass] = $aModuleChain;
                }
            }
        }

        return $aAllModuleArray;
    }

    /**
     * Save module parameters to shop config
     *
     * @param string       $sVariableName  config name
     * @param string|array $sVariableValue config value
     * @param string       $sVariableType  config type
     */
    protected function _saveToConfig($sVariableName, $sVariableValue, $sVariableType = 'aarr')
    {
        $oConfig = Registry::getConfig();
        $oConfig->setConfigParam($sVariableName, $sVariableValue);
        $oConfig->saveShopConfVar($sVariableType, $sVariableName, $sVariableValue);
    }
}
