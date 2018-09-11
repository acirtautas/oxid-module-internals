<?php

namespace OxidCommunity\ModuleInternals\Controller;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Module\ModuleList as ModuleList;
use OxidEsales\Eshop\Core\Module\Module as Module;
use OxidEsales\Eshop\Core\SeoEncoder;

class CheckConsistency
{
    /**
     * @var string
     */
    public $sTemplate = 'checkconsistency.tpl';

    /** @var oxModule */
    protected $_oModule;

    /** @var ac_module_internals_data_helper */
    protected $_oModuleDataProviderHelper;

    /** @var ac_module_internals_fix_helper */
    protected $_oModuleFixHelper;

    protected $_sModId;

    public function init()
    {
        $oConfig  = Registry::get(Config::class);

        $redirectUrl = $oConfig->getShopUrl();
        $sKey = $oConfig->getRequestParameter('key');

        //todo: Exeception / Logging
        if((bool)$oConfig->getConfigParam('blACActiveCompleteCheck') == false )
        {
            Registry::getUtils()->redirect($redirectUrl, false, 403);
        }

        //todo: Exeception / Logging
        if($sKey != $oConfig->getConfigParam('sACActiveCompleteKey'))
        {
            Registry::getUtils()->redirect($redirectUrl, false, 403);
        }
    }

    /**
     * @throws oxSystemComponentException
     */
    public function render()
    {
        $oConfig  = Registry::get(Config::class);
        $aModules = $this->_getActiveModules($oConfig->getConfigParam('aDisabledModules'),$oConfig->getConfigParam('aModulePaths'));

        $aModuleChecks = array();
        $oModule = oxNew(Module::class);
        foreach($aModules as $sModId => $sTitle)
        {
            $aModule = array();
            $oModule->load($sModId);
            $oHelper = $this->getModuleDataProviderHelper($oModule);

            $aModule['title'] = $sModId." - ".$sTitle;
            $aModule['oxid'] = $oHelper->getModuleId();
            $aModule['aExtended'] = $oHelper->checkExtendedClasses();
            $aModule['aBlocks'] = $oHelper->checkTemplateBlocks();
            $aModule['aSettings'] = $oHelper->checkModuleSettings();
            $aModule['aFiles'] = $oHelper->checkModuleFiles();
            $aModule['aTemplates'] = $oHelper->checkModuleTemplates();

            $this->_sModId  = '';
            $aModuleChecks[$sModId] = $aModule;
        }

        $this->_aViewData['aModules'] = $aModuleChecks;
        $this->_aViewData['sState'] = array(
                -3 => 'sfatals',
                -2 => 'sfatalm',
                -1 => 'serror',
                0 => 'swarning',
                1 => 'sok'
        );

        return $this->sTemplate;
    }

    /**
     * @param array $aDisabledModules
     * @param array $aModulePaths
     *
     * @return array
     * @throws oxSystemComponentException
     */
    protected function _getActiveModules(array $aDisabledModules, array $aModulePaths)
    {

        $oConfig  = Registry::get(Config::class);
        $aActiveModules = array();
        $aModulePaths = array_flip($aModulePaths);
        $aActiveModules = array_diff($aModulePaths,$aDisabledModules);

        $aTmpActiveModules = array_flip($aActiveModules);

        $aActiveModules = array();
        $oModule = oxNew(Module::class);
        $oSeoEncoder = oxNew(SeoEncoder::class);
        foreach($aTmpActiveModules as $sKey => $sValue)
        {
            $oModule->load($sKey);

            //Version einbinden
            $aVersions = $oConfig->getConfigParam('aModuleVersions');
            $sTitle = $oModule->getTitle().' - v'.$aVersions[$oModule->getId()];
            $aActiveModules[$sKey] = utf8_encode($oSeoEncoder->encodeString(strip_tags($sTitle)));
        }

        $sModulesDir = $oConfig->getModulesDir();

        $oModuleList = oxNew(ModuleList::Class);
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);

        $aTmpModules = $aActiveModules;
        $aActiveModules = array();

        /* Sortieren, nach der Anzeige im Admin zum einfacheren Vergleich*/
        foreach($aModules as $oModule)
        {
            if(array_key_exists($oModule->getId(),$aTmpModules))
            {
                $aActiveModules[$oModule->getId()] = $aTmpModules[$oModule->getId()];
            }
        }

        return $aActiveModules;
    }

    /**
     * Get active module object.
     *
     * @return Module
     */
    public function getModule()
    {
        if ($this->_oModule === null) {
            $sModuleId = $this->_sModId;
            echo "--".$sModuleId;
            $this->_aViewData['oxid'] = $sModuleId;

            $this->_oModule = oxNew(Module::class);
            $this->_oModule->load($sModuleId);
        }

        return $this->_oModule;
    }

    public function getEditObjectId()
    {
        return $this->_sModId;
    }

    /**
     * @param $oModule
     *
     * @return ac_module_internals_data_helper
     */
    public function getModuleDataProviderHelper($oModule)
    {
        if ($this->_oModuleDataProviderHelper === null) {
            $this->_oModuleDataProviderHelper = oxNew('ac_module_internals_data_helper', $oModule, oxNew('oxModuleList'));
        }

        return $this->_oModuleDataProviderHelper;
    }
}