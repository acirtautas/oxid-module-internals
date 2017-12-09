<?php

namespace OxCom\ModuleInternals\Tests\Integration\Controller;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxCom\ModuleInternals\Controller\Admin\State;
use OxidEsales\Eshop\Core\Module\Module as Module;
use OxCom\ModuleInternals\Core\DataHelper as DataHelper;
use OxCom\ModuleInternals\Core\FixHelper as FixHelper;

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