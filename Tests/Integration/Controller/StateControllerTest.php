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

namespace OxidCommunity\ModuleInternals\Tests\Integration\Controller;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidCommunity\ModuleInternals\Controller\Admin\State;
use OxidEsales\Eshop\Core\Module\Module as Module;
use OxidCommunity\ModuleInternals\Core\DataHelper as DataHelper;
use OxidCommunity\ModuleInternals\Core\FixHelper as FixHelper;

/**
 *
 */
class StateControllerTest extends UnitTestCase
{

    /**
     *
     */
    public function testGetModuleDataProviderHelper()
    {
        $stateController = $this->getMock(State::class, ['getModule']);
        $stateController
            ->expects($this->any())
            ->method('getModule')
            ->will($this->returnValue(oxNew(Module::class)));

        $moduleDataProviderHelper = $stateController->getModuleDataProviderHelper();

        $this->assertInstanceOf(DataHelper::class, $moduleDataProviderHelper, 'class not as expected');
    }

    /**
     *
     */
    public function testGetModule()
    {
        $moduleId = 'moduleinternals';
        $this->setRequestParameter('oxid', $moduleId);
        $stateController = oxNew(State::class);

        $module = $stateController->getModule();

        $this->assertInstanceOf(Module::class, $module, 'class not as expected');
        $this->assertEquals($module->getId(), $moduleId);
    }

    /**
     *
     */
    public function testGetModuleFixHelper()
    {
        $moduleId = 'moduleinternals';
        $this->setRequestParameter('oxid', $moduleId);
        $stateController = oxNew(State::class);

        $module = $stateController->getModuleFixHelper();

        $this->assertInstanceOf(FixHelper::class, $module, 'class not as expected');
    }
}