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

namespace OxidCommunity\ModuleInternals\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Module\Module as Module;

/**
 * Module internals tools.
 *
 * @author Oxid Community
 */

/**
 * Module metadata content retrieving.
 */
class Metadata extends AdminController
{

    /**
     * @var string
     */
    public $sTemplate = 'metadata.tpl';

    /**
     * @return string
     */
    public function render()
    {
        $sModuleId = $this->getEditObjectId();

        $this->addTplParam('oxid', $sModuleId);

        /** @var $oModule Module */
        $oModule = oxNew(Module::class);
        $oModule->load($sModuleId);

        if ($oModule->hasMetadata()) {
            $sModulePath = $oModule->getModulePath($sModuleId);
            $sMetadataPath = $this->getConfig()->getModulesDir() . $sModulePath . "/metadata.php";
            $sRawMetadata = highlight_file($sMetadataPath, 1);
            $this->addTplParam('sRawMetadata', $sRawMetadata);
        }

        return $this->sTemplate;
    }
}