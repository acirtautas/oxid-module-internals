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

use OxidCommunity\ModuleInternals\Core\FixHelper as FixHelper;
use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Module\ModuleCache as ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList as ModuleList;
use OxidEsales\Eshop\Core\Module\Module as Module;

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
     * @return FixHelper
     */
    public function getModuleFixHelper()
    {
        if ($this->_oModuleFixHelper === null) {
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
        if ($this->_oModule === null) {
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
        $this->addTplParam('aExtended', $this->getModule()->checkExtendedClasses());
        $this->addTplParam('aBlocks', $this->getModule()->checkTemplateBlocks());
        $this->addTplParam('aSettings', $this->getModule()->checkModuleSettings());
        $this->addTplParam('aTemplates', $this->getModule()->checkModuleTemplates());

        // valid not for  metadata version 1.*
        if ($this->getModule()->checkMetadataVersion('1.0') || $this->getModule()->checkMetadataVersion('1.1')) {
            $this->addTplParam('aFiles', $this->getModule()->checkModuleFiles());
        }

        // valid  for  metadata version 1.1 and 2.0
        if ($this->getModule()->checkMetadataVersion('1.1') || $this->getModule()->checkMetadataVersion('2.0')) {
            $this->addTplParam('aEvents', $this->getModule()->checkModuleEvents());
            $this->addTplParam('aVersions', $this->getModule()->checkModuleVersions());
        }

        /**
         * @todo check if files is set - should'nt be
         */
        if ($this->getModule()->checkMetadataVersion('2.0'))
            $this->addTplParam('aControllers', $this->getModule()->checkModuleController());


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
     * Fix module controllers.
     */
    public function fixControllers()
    {
        $this->getModuleFixHelper()->fixControllers();
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
}
