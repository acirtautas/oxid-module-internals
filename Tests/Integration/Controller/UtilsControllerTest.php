<?php

namespace OxCom\ModuleInternals\Tests\Integration\Controller;

use OxidEsales\TestingLibrary\UnitTestCase;

class UtilsControllerTest extends UnitTestCase
{
    /**
     * Test module getter.
     */
    public function testGetModule()
    {
        $moduleId = 'moduleinternals';
        $utilsController = $this->getMock(\OxCom\ModuleInternals\Controller\Admin\Utils::class, ['getEditObjectId']);
        $utilsController->expects($this->any())->method('getEditObjectId')->will($this->returnValue($moduleId));

        $module = $utilsController->getModule();

        $this->assertTrue(is_a($module, \OxidEsales\Eshop\Core\Module\Module::class), 'class not as expected');
        $this->assertEquals($moduleId, $module->getId(), 'id not as expected');
    }

    /**
     * Test module cache getter.
     */
    public function testGetModulePath()
    {
        $moduleId = 'moduleinternals';
        $utilsController = $this->getMock(\OxCom\ModuleInternals\Controller\Admin\Utils::class, ['getEditObjectId']);
        $utilsController->expects($this->any())->method('getEditObjectId')->will($this->returnValue($moduleId));

        $module = $utilsController->getModuleCache();

        $this->assertTrue(is_a($module, \OxidEsales\Eshop\Core\Module\ModuleCache::class));
    }

    /**
     * Test getter for module installer.
     */
    public function testGetModuleInstaller()
    {
        $utilsController = oxNew(\OxCom\ModuleInternals\Controller\Admin\Utils::class);

        $module = $utilsController->getModuleInstaller();

        $this->assertTrue(is_a($module, \OxidEsales\Eshop\Core\Module\ModuleInstaller::class));
    }

    /**
     * Test resetting cache.
     */
    public function testResetCache()
    {
        $moduleId = 'moduleinternals';
        $module = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);
        $module->load($moduleId);

        $moduleCache = $this->getMock(\OxidEsales\Eshop\Core\Module\ModuleCache::class, ['resetCache'], [$module]);
        $moduleCache->expects($this->any())->method('resetCache');

        $utilsController = $this->getMock(\OxCom\ModuleInternals\Controller\Admin\Utils::class, ['getModuleCache', 'getEditObjectId']);
        $utilsController->expects($this->any())->method('getEditObjectId')->will($this->returnValue($moduleId));
        $utilsController->expects($this->any())->method('getModuleCache')->will($this->returnValue($moduleCache));

        $utilsController->reset_cache();
    }
}