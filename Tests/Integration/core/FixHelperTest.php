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

namespace OxidCommunity\ModuleInternals\Tests\Integration\core;

use OxidCommunity\ModuleInternals\Core\FixHelper;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Module\ModuleCache;
use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\TestingLibrary\UnitTestCase;

/**
 * Class FixHelperTest:
 */
class FixHelperTest extends UnitTestCase
{

    /**
     *
     */
    public function testFixVersion()
    {
        $moduleId = 'moduleinternals';
        $this->setConfigParam('aModuleVersions', [$moduleId => '0.1.0']);
        $fixHelper = $this->createFixHelper($moduleId);

        $fixHelper->fixVersion();

        $this->assertInstanceOf(FixHelper::class, $fixHelper);
        $this->assertEquals($this->getConfigParam('aModuleVersions'), [$moduleId => '0.4.0']);
    }

    /**
     *
     */
    public function testFixExtend()
    {
        $moduleId = 'cleartmp';
        $fixHelper = $this->createFixHelper($moduleId);

        $fixHelper->fixExtend();

        $this->assertEquals($this->getConfigParam('aModuleExtend'), ['a' => 'b']);
    }


    /**
     * @param $moduleId
     *
     * @return object
     */
    protected function createFixHelper($moduleId)
    {
        $module = oxNew(Module::class);
        $module->load($moduleId);
        $moduleList = oxNew(ModuleList::class);
        $ModuleCache = oxNew(ModuleCache::class, $module);
        $fixHelper = oxNew(FixHelper::class, $module, $moduleList, $ModuleCache);

        return $fixHelper;
    }

}