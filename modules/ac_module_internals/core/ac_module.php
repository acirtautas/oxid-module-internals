<?php
/**
 * Module internals tools.
 *
 * @author Alfonsas Cirtautas
 */

/**
 * Extended module handler class, for easies module data manipulation.
 */
class ac_module extends ac_module_parent // oxModule
{
    /**
     * Set template extend to database, do cleanup before.
     *
     * @param string $sModuleId     Module ID
     * @param array  $aModuleExtend Extend data array from metadata
     */
    public function setModuleExtend($sModuleId, $aModuleExtend)
    {
        $oConfig     = oxRegistry::getConfig();

        $aInstalledModules = $this->getAllModules();
        $aDisabledModules  = $this->getDisabledModules();

        $sModulePath = $this->getModulePath($sModuleId);

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

        $aModules = $this->mergeModuleArrays($aInstalledModules, $aModuleExtend);
        $aModules = $this->buildModuleChains($aModules);

        $oConfig->setConfigParam('aModules', $aModules);
        $oConfig->saveShopConfVar('aarr', 'aModules', $aModules);
    }

    /**
     * Get template blocks defined in database.
     *
     * @param $sModuleId
     *
     * @return array
     */
    public function getModuleBlocks($sModuleId)
    {
        if (!$sModuleId) {
            return array();
        }

        $sShopId = $this->getConfig()->getShopId();

        $aBlocks = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getAll("SELECT * FROM oxtplblocks WHERE oxmodule = '$sModuleId' AND oxshopid = '$sShopId' ");

        return $aBlocks;
    }

    /**
     * Set template blocks to database, do cleanup before.
     *
     * @param string $sModuleId     Module ID
     * @param array  $aModuleBlocks Block data array from metadata
     */
    public function setModuleBlocks($sModuleId, $aModuleBlocks)
    {
        $sShopId = $this->getConfig()->getShopId();

        // Cleanup !!!
        oxDb::getDb()->execute("DELETE FROM oxtplblocks WHERE oxmodule = '$sModuleId' AND oxshopid = '$sShopId'");

        $this->_addTemplateBlocks($aModuleBlocks, $sModuleId);
    }

    /**
     * Get module settings defined in database.
     *
     * @param $sModuleId
     *
     * @return array
     */
    public function getModuleSettings($sModuleId)
    {
        if (!$sModuleId) {
            return array();
        }

        $sShopId = $this->getConfig()->getShopId();

        $aSettings = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getAll("SELECT * FROM oxconfig WHERE oxmodule = 'module:$sModuleId' AND oxshopid = '$sShopId'");

        return $aSettings;
    }

    /**
     * Set settings to database, do cleanup before.
     *
     * @param string $sModuleId       Module ID
     * @param array  $aModuleSettings Setting data array from metadata
     */
    public function setModuleSettings($sModuleId, $aModuleSettings)
    {
        $sShopId = $this->getConfig()->getShopId();

        // Cleanup !!!
        oxDb::getDb()->execute("DELETE FROM oxconfig WHERE oxmodule = 'module:$sModuleId' AND oxshopid = '$sShopId'");
        oxDb::getDb()->execute("DELETE FROM oxconfigdisplay WHERE oxcfgmodule = '$sModuleId'");

        $this->_addModuleSettings($aModuleSettings, $sModuleId);
    }

    /**
     * Get module files defined in configuration.
     *
     * @param null $sModuleId
     *
     * @return array
     */
    public function getModuleFiles($sModuleId = null)
    {
        $aModuleFiles = parent::getModuleFiles();
        if (!is_null($sModuleId)) {
            $aModuleFiles = $aModuleFiles[$sModuleId];
        }

        return $aModuleFiles;
    }

    /**
     * Set files to database,
     *
     * @param string $sModuleId    Module ID
     * @param array  $aModuleFiles File data array from metadata
     */
    public function setModuleFiles($sModuleId, $aModuleFiles)
    {
        $this->_addModuleFiles($aModuleFiles, $sModuleId);
    }

    /**
     * Get module templates defined in configuration.
     *
     * @param null $sModuleId
     *
     * @return array
     */
    public function getModuleTemplates($sModuleId = null)
    {
        $aModuleTemplates = parent::getModuleTemplates();
        if (!is_null($sModuleId)) {
            $aModuleTemplates = $aModuleTemplates[$sModuleId];
        }

        return $aModuleTemplates;
    }

    /**
     * Set templates to database,
     *
     * @param string $sModuleId        Module ID
     * @param array  $aModuleTemplates Template data array from metadata
     */
    public function setModuleTemplates($sModuleId, $aModuleTemplates)
    {
        $this->_addTemplateFiles($aModuleTemplates, $sModuleId);
    }

    /**
     * Get module events defined in configuration.
     *
     * @param null $sModuleId
     *
     * @return array
     */
    public function getModuleEvents($sModuleId = null)
    {
        $aModuleEvents = parent::getModuleEvents();
        if (!is_null($sModuleId)) {
            $aModuleEvents = $aModuleEvents[$sModuleId];
        }

        return $aModuleEvents;
    }

    /**
     * Set events to database,
     *
     * @param string $sModuleId     Module ID
     * @param array  $aModuleEvents Event data array from metadata
     */
    public function setModuleEvents($sModuleId, $aModuleEvents)
    {
        $this->_addModuleEvents($aModuleEvents, $sModuleId);
    }

    /**
     * Get module versions defined in configuration.
     *
     * @param null $sModuleId
     *
     * @return array
     */
    public function getModuleVersion($sModuleId)
    {
        $aModuleVersions = parent::getModuleVersions();
        $sModuleVersion  = $aModuleVersions[$sModuleId];

        return $sModuleVersion;
    }

    /**
     * Set version to database,
     *
     * @param string $sModuleId      Module ID
     * @param string $sModuleVersion Version from metadata
     */
    public function setModuleVersion($sModuleId, $sModuleVersion)
    {
        $this->_addModuleVersion($sModuleVersion, $sModuleId);
    }

    /**
     * Reset module cache.
     */
    public function resetCache()
    {
        $this->_resetCache();
    }
}