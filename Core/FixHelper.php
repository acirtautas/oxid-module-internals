<?php
namespace OxCom\ModuleInternals\Core;

/**
 * Module internals tools.
 *
 * @author Saulius Cepauskas
 */

/**
 * Class ac_module_internals_helper
 *
 * Fix helper service: fix module states and configuration.
 */
class FixHelper
{
    /** @var oxModule */
    protected $_oModule;

    /** @var oxModuleList */
    protected $_oModuleList;

    /** @var oxModuleCache */
    protected $_oModuleCache;

    /**
     * Injects helper parameters
     *
     * @param oxModule $oModule
     * @param oxModuleList $oModuleList
     */
    public function __construct(oxModule $oModule, oxModuleList $oModuleList, oxModuleCache $oModuleCache)
    {
        $this->_oModule = $oModule;
        $this->_oModuleList = $oModuleList;
        $this->_oModuleCache = $oModuleCache;
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
     * @return oxModuleCache
     */
    public function getModuleCache()
    {
        return $this->_oModuleCache;
    }

    /**
     * @param oxModuleCache $oModuleCache
     */
    public function setModuleCache(oxModuleCache $oModuleCache)
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
     * @param int $iLang
     * @return string
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
        $sVersion = $this->getInfo('version');
        $aVersions = (array)oxRegistry::getConfig()->getConfigParam('aModuleVersions');
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
        $aExtend = $this->getInfo('extend');
        $aInstalledModules = oxRegistry::getConfig()->getModulesWithExtendedClass();

        $sModulePath = $this->getModulePath();

        // Remove extended modules by path
        if ($sModulePath && is_array($aInstalledModules)) {
            foreach ($aInstalledModules as $sClassName => $mModuleName) {
                if (is_array($mModuleName)) {
                    foreach ($mModuleName as $sKey => $sModuleName) {
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
        $aFiles = (array)oxRegistry::getConfig()->getConfigParam('aModuleFiles');

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

        $aTemplates = (array)oxRegistry::getConfig()->getConfigParam('aModuleTemplates');
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
        $oConfig = oxRegistry::getConfig();
        $aModuleBlocks = $this->getInfo('blocks');
        $sShopId = $oConfig->getShopId();
        $oDb = oxDb::getDb();

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
                $sOxId = oxRegistry::get('oxUtilsObject')->generateUId();
                $sTemplate = $aValue['template'];
                $iPosition = $aValue['position'] ? $aValue['position'] : 1;
                $sBlock = $aValue['block'];
                $sFile = $aValue['file'];

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
        $oConfig = oxRegistry::getConfig();
        $sShopId = $oConfig->getShopId();
        $oDb = oxDb::getDb();

        // Cleanup !!!
        $oDb->execute(
            'DELETE FROM oxconfig WHERE oxmodule = ? AND oxshopid = ?',
            array(sprintf('module:%s', $this->getModuleId()), $sShopId)
        );
        $oDb->execute('DELETE FROM oxconfigdisplay WHERE oxcfgmodule = ?', array($this->getModuleId()));

        if (is_array($aModuleSettings)) {

            foreach ($aModuleSettings as $aValue) {
                $sOxId = oxRegistry::get('oxUtilsObject')->generateUId();

                $sModule = 'module:' . $this->getModuleId();
                $sName = $aValue['name'];
                $sType = $aValue['type'];
                $sValue = is_null($oConfig->getConfigParam($sName)) ? $aValue['value'] : $oConfig->getConfigParam($sName);
                $sGroup = $aValue['group'];

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
        $aEvents = (array)oxRegistry::getConfig()->getConfigParam('aModuleEvents');

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
        if ( is_array( $aAllModuleArray ) && is_array( $aAddModuleArray ) ) {
            foreach ( $aAddModuleArray as $sClass => $aModuleChain ) {
                if ( !is_array( $aModuleChain ) ) {
                    $aModuleChain = array( $aModuleChain );
                }
                if ( isset( $aAllModuleArray[$sClass] ) ) {
                    foreach ( $aModuleChain as $sModule ) {
                        if ( !in_array( $sModule, $aAllModuleArray[$sClass] ) ) {
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
     * @param string $sVariableName config name
     * @param string $sVariableValue config value
     * @param string $sVariableType config type
     *
     * @return null
     */
    protected function _saveToConfig($sVariableName, $sVariableValue, $sVariableType = 'aarr')
    {
        $oConfig = oxRegistry::getConfig();
        $oConfig->setConfigParam($sVariableName, $sVariableValue);
        $oConfig->saveShopConfVar($sVariableType, $sVariableName, $sVariableValue);
    }
}
