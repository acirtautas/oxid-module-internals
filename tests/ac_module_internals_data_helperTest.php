<?php
/**
 * Module internals tools data helper tests.
 *
 * @author Alfonsas Cirtautas
 *
 * @covers ac_module_internals_data_helper
 */
class ac_module_internals_data_helperTest extends PHPUnit_Framework_TestCase {

    public function testGetModule()
    {
        $oModule     = $this->getMock('oxmodule');
        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModule(), $oModule);
    }

    public function testSetModule()
    {
        $oModule = $this->getMock('oxmodule', array('isCustom'));
        $oModule->method('isCustom')->willReturn(false);

        $oCustomModule = $this->getMock('oxmodule', array('isCustom'));
        $oCustomModule->method('isCustom')->willReturn(true);

        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $helper->setModule($oCustomModule);

        $this->assertTrue($helper->getModule()->isCustom());
    }

    public function testGetModuleList()
    {
        $oModule     = $this->getMock('oxmodule');
        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModuleList(), $oModuleList);
    }

    public function testSetModuleList()
    {
        $oModule = $this->getMock('oxmodule');

        $oModuleList = $this->getMock('oxmodulelist', array('isCustom'));
        $oModuleList->method('isCustom')->willReturn(false);

        $oCustomModuleList = $this->getMock('oxmodulelist', array('isCustom'));
        $oCustomModuleList->method('isCustom')->willReturn(true);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $helper->setModuleList($oCustomModuleList);

        $this->assertTrue($helper->getModuleList()->isCustom());
    }

    public function testSetGetConfig()
    {
        $oModule     = $this->getMock('oxmodule');
        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxconfig');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $helper->setConfig($oConfig);

        $this->assertEquals($helper->getConfig(), $oConfig);
    }

    public function testSetGetDb()
    {
        $oModule     = $this->getMock('oxmodule');
        $oModuleList = $this->getMock('oxmodulelist');

        $oDb = $this->getMock('oxLegacyDb');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $helper->setDb($oDb);

        $this->assertEquals($helper->getDb(), $oDb);
    }

    public function testGetInfo()
    {
        $oModule = $this->getMock('oxmodule', array('getInfo'));
        $oModule->method('getInfo')->willReturn('info');

        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getInfo('id'), 'info');
    }

    public function testGetModuleBlocks()
    {
        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getShopId'));
        $oConfig->method('getShopId')->willReturn('shop-id');

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn('module-themes');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $this->assertEquals($helper->getModuleBlocks(), 'module-themes');
    }

    public function testGetModuleSettings()
    {
        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getShopId'));
        $oConfig->method('getShopId')->willReturn('shop-id');

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module:module-id','shop-id')))
            ->willReturn('module-settings');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $this->assertEquals($helper->getModuleSettings(), 'module-settings');
    }

    public function testGetModuleFiles()
    {
        $aAllModuleFiles = array(
            'module-id'         => array( 'file1', 'file2'),
            'another-module-id' => array( 'file3', 'file4'),
        );

        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleFiles'));
        $oModuleList->method('getModuleFiles')->willReturn($aAllModuleFiles);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModuleFiles(), $aAllModuleFiles['module-id']);
    }

    public function testGetModuleTemplates()
    {
        $aAllModuleTemplates = array(
            'module-id'         => array( 'template1', 'template2'),
            'another-module-id' => array( 'template3', 'template4'),
        );

        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleTemplates'));
        $oModuleList->method('getModuleTemplates')->willReturn($aAllModuleTemplates);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModuleTemplates(), $aAllModuleTemplates['module-id']);
    }

    public function testGetModuleEvents()
    {
        $aAllModuleEvents = array(
            'module-id'         => array( 'event1', 'event2'),
            'another-module-id' => array( 'event3', 'event4'),
        );

        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleEvents'));
        $oModuleList->method('getModuleEvents')->willReturn($aAllModuleEvents);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModuleEvents(), $aAllModuleEvents['module-id']);
    }

    public function testGetModuleVersion()
    {
        $aAllModuleVersions = array(
            'module-id'         => 'version1',
            'another-module-id' => 'version2',
        );

        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleVersions'));
        $oModuleList->method('getModuleVersions')->willReturn($aAllModuleVersions);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModuleVersion(), $aAllModuleVersions['module-id']);
    }

    public function testIsMetadataSupportedFirstRelease()
    {
        $oModule     = $this->getMock('oxmodule');
        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertTrue($helper->isMetadataSupported('0.1'));
        $this->assertTrue($helper->isMetadataSupported('1.0'));

        $this->assertFalse($helper->isMetadataSupported('1.1'));
    }

    public function testIsMetadataSupportedSecondRelease()
    {
        $oModule     = $this->getMock('oxmodule', array('getModuleEvents'));
        $oModuleList = $this->getMock('oxmodulelist', array('getModuleVersions'));

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertTrue($helper->isMetadataSupported('0.1'));
        $this->assertTrue($helper->isMetadataSupported('1.0'));
        $this->assertTrue($helper->isMetadataSupported('1.1'));

        $this->assertFalse($helper->isMetadataSupported('1.2'));
    }

    public function testGetModulePath()
    {
        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath'));
        $oModule->method('getId')->willReturn('module-id');

        $oModule->expects($this->any())
                ->method('getModulePath')
                ->with($this->equalTo('module-id'))
                ->willReturn('module-id-path');

        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModulePath(), 'module-id-path');
    }

    public function testGetModuleId()
    {
        $oModule = $this->getMock('oxmodule', array('getId'));
        $oModule->method('getId')->willReturn('module-id');

        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getModuleId(), 'module-id');
    }

    public function testCheckExtendedClassesOK()
    {
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleExtend = array('shop-class'=>'data/module-class');

        $GlobalExtend = array('shop-class'=>array('data/module-class'));
        $ModulesDir = __DIR__.'/';

        $expectedResults = array('shop-class'=>array('data/module-class' => ac_module_internals_data_helper::STATE_OK));

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getInfo')->with($this->equalTo('extend'))->willReturn($ModuleExtend);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesWithExtendedClass', 'getModulesDir'));
        $oConfig->method('getModulesWithExtendedClass')->willReturn($GlobalExtend);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkExtendedClasses();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckExtendedClassesNotInstalled()
    {
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleExtend = array('shop-class'=>'data/module-class');

        $GlobalExtend = array();
        $ModulesDir = __DIR__.'/';

        $expectedResults = array('shop-class'=>array('data/module-class' => ac_module_internals_data_helper::STATE_WARNING));

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getInfo')->with($this->equalTo('extend'))->willReturn($ModuleExtend);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesWithExtendedClass', 'getModulesDir'));
        $oConfig->method('getModulesWithExtendedClass')->willReturn($GlobalExtend);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkExtendedClasses();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckExtendedClassesNotFound()
    {
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleExtend = array('shop-class'=>'data/module-class-not-found');

        $GlobalExtend = array('shop-class'=>array('data/module-class-not-found'));
        $ModulesDir = __DIR__.'/';

        $expectedResults = array('shop-class'=>array('data/module-class-not-found' => ac_module_internals_data_helper::STATE_FATAL_MODULE));

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getInfo')->with($this->equalTo('extend'))->willReturn($ModuleExtend);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesWithExtendedClass', 'getModulesDir'));
        $oConfig->method('getModulesWithExtendedClass')->willReturn($GlobalExtend);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkExtendedClasses();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckExtendedClassesRedundant()
    {
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleExtend = array();

        $GlobalExtend = array('shop-class'=>array('data/module-class'));
        $ModulesDir = __DIR__.'/';

        $expectedResults = array('shop-class'=>array('data/module-class' => ac_module_internals_data_helper::STATE_ERROR));

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getInfo')->with($this->equalTo('extend'))->willReturn($ModuleExtend);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesWithExtendedClass', 'getModulesDir'));
        $oConfig->method('getModulesWithExtendedClass')->willReturn($GlobalExtend);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkExtendedClasses();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateBlocksOK()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array(
            array('template' => 'template1.tpl', 'block' => 'block1', 'file' => 'file1.tpl'),
        );

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template1.tpl', 'OXBLOCKNAME' => 'block1', 'OXFILE' => 'file1.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = __DIR__.'/data/template1.tpl';
        $ModuleTemplates = array();

        $expectedResults = array(
            'template1.tpl'=>array('file1.tpl' => array('file'=>ac_module_internals_data_helper::STATE_OK)),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateBlocksRedundant()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array();

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template1.tpl', 'OXBLOCKNAME' => 'block1', 'OXFILE' => 'file1.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = __DIR__.'/data/template1.tpl';
        $ModuleTemplates = array();

        $expectedResults = array(
            'template1.tpl'=>array('file1.tpl' => ac_module_internals_data_helper::STATE_ERROR),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateBlocksRedundantNotFount()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array();

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template1.tpl', 'OXBLOCKNAME' => 'block1', 'OXFILE' => 'file2.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = __DIR__.'/data/template1.tpl';
        $ModuleTemplates = array();

        $expectedResults = array(
            'template1.tpl'=>array('file2.tpl' => array('file'=>ac_module_internals_data_helper::STATE_FATAL_SHOP)),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateBlockNotFound()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array(
            array('template' => 'template1.tpl', 'block' => 'block2', 'file' => 'file1.tpl'),
        );

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template1.tpl', 'OXBLOCKNAME' => 'block2', 'OXFILE' => 'file1.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = __DIR__.'/data/template1.tpl';
        $ModuleTemplates = array();

        $expectedResults = array(
            'template1.tpl'=>array('file1.tpl' => array('file'=>ac_module_internals_data_helper::STATE_OK, 'template'=>ac_module_internals_data_helper::STATE_ERROR)),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateFileNotFound()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array(
            array('template' => 'template1.tpl', 'block' => 'block1', 'file' => 'file2.tpl'),
        );

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template1.tpl', 'OXBLOCKNAME' => 'block1', 'OXFILE' => 'file2.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = __DIR__.'/data/template1.tpl';
        $ModuleTemplates = array();

        $expectedResults = array(
            'template1.tpl'=>array('file2.tpl' => array('file'=>ac_module_internals_data_helper::STATE_FATAL_MODULE)),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateModuleFileNotFound()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array(
            array('template' => 'template1.tpl', 'block' => 'block1', 'file' => 'file2.tpl'),
        );

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template1.tpl', 'OXBLOCKNAME' => 'block1', 'OXFILE' => 'file2.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = null;
        $ModuleTemplates = array('template1.tpl'=> 'data/template1.tpl');

        $expectedResults = array(
            'template1.tpl'=>array('file2.tpl' => array('file'=>ac_module_internals_data_helper::STATE_FATAL_MODULE)),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckTemplateTemplateNotFound()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModulePath = 'data';
        $ModuleBlocks = array(
            array('template' => 'template2.tpl', 'block' => 'block1', 'file' => 'file1.tpl'),
        );

        $GlobalBlocks = array(
            array('OXTEMPLATE' => 'template2.tpl', 'OXBLOCKNAME' => 'block1', 'OXFILE' => 'file1.tpl'),
        );

        $ModulesDir = __DIR__.'/';
        $TemplatesDir = '';
        $ModuleTemplates = array();

        $expectedResults = array(
            'template2.tpl'=>array('file1.tpl' => array('file'=>ac_module_internals_data_helper::STATE_OK, 'template' =>ac_module_internals_data_helper::STATE_FATAL_SHOP)),
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getModulePath','getModuleBlocks', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getModulePath')->willReturn($ModulePath);
        $oModule->method('getModuleBlocks')->willReturn($GlobalBlocks);
        $oModule->method('getInfo')->will($this->onConsecutiveCalls($ModuleBlocks, $ModuleTemplates));

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array('getModulesDir', 'getTemplatePath', 'getShopId'));
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);
        $oConfig->method('getTemplatePath')->willReturn($TemplatesDir);
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module-id','shop-id')))
            ->willReturn($GlobalBlocks);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkTemplateBlocks();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleSettingsOK()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleSettings = array(
            array('group' => 'main', 'name' => 'testSetting', 'type' => 'bool', 'value' => 'false'),
        );

        $GlobalSettings = array(
            array('OXVARNAME' => 'testSetting')
        );

        $expectedResults = array('testSetting'=>ac_module_internals_data_helper::STATE_OK);

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('settings'))->willReturn($ModuleSettings);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module:module-id','shop-id')))
            ->willReturn($GlobalSettings);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkModuleSettings();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleSettingsRedundant()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleSettings = array();

        $GlobalSettings = array(
            array('OXVARNAME' => 'testSettingRedundant')
        );

        $expectedResults = array('testSettingRedundant'=>ac_module_internals_data_helper::STATE_ERROR);

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('settings'))->willReturn($ModuleSettings);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module:module-id','shop-id')))
            ->willReturn($GlobalSettings);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkModuleSettings();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleSettingsNotInMetadata()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleSettings = array(
            array('group' => 'main', 'name' => 'testSetting', 'type' => 'bool', 'value' => 'false'),
        );

        $GlobalSettings = array();

        $expectedResults = array('testSetting'=>ac_module_internals_data_helper::STATE_WARNING);

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('settings'))->willReturn($ModuleSettings);

        $oModuleList = $this->getMock('oxmodulelist');

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $oDb = $this->getMock('oxLegacyDb', array('getAll'));
        $oDb->expects($this->any())
            ->method('getAll')
            ->with($this->anything(), $this->equalTo( array('module:module-id','shop-id')))
            ->willReturn($GlobalSettings);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);
        $helper->setDb($oDb);

        $checkResults  = $helper->checkModuleSettings();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleFilesOK()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleFiles = array(
            'module-class' => 'module-class.php',
        );

        $GlobalFiles = array('module-id'=>array('module-class' => 'module-class.php'));
        $ModulesDir = __DIR__.'/data/';

        $expectedResults = array('module-class'=>array('module-class.php' => ac_module_internals_data_helper::STATE_OK));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('files'))->willReturn($ModuleFiles);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleFiles'));
        $oModuleList->method('getModuleFiles')->willReturn( $GlobalFiles );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId', 'getModulesDir'));
        $oConfig->method('getShopId')->willReturn($ShopId);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleFiles();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleFilesMissing()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleFiles = array(
            'module-class' => 'module-class-missing.php',
        );

        $GlobalFiles = array();
        $ModulesDir = __DIR__.'/data/';

        $expectedResults = array('module-class'=>array('module-class-missing.php' => ac_module_internals_data_helper::STATE_FATAL_MODULE));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('files'))->willReturn($ModuleFiles);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleFiles'));
        $oModuleList->method('getModuleFiles')->willReturn( $GlobalFiles );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId', 'getModulesDir'));
        $oConfig->method('getShopId')->willReturn($ShopId);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleFiles();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleFilesRedundant()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleFiles = array();

        $GlobalFiles = array('module-id'=>array('module-class' => 'missing-module-class.php'));
        $ModulesDir  = __DIR__.'/data/';

        $expectedResults = array('module-class'=>array('missing-module-class.php' => ac_module_internals_data_helper::STATE_FATAL_SHOP));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('files'))->willReturn($ModuleFiles);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleFiles'));
        $oModuleList->method('getModuleFiles')->willReturn( $GlobalFiles );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId', 'getModulesDir'));
        $oConfig->method('getShopId')->willReturn($ShopId);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleFiles();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleTemplatesOK()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleTemplates = array(
            'template1.tpl' => 'template1.tpl',
        );

        $GlobalTemplates = array('module-id'=>array('template1.tpl' => 'template1.tpl',));
        $ModulesDir = __DIR__.'/data/';

        $expectedResults = array('template1.tpl'=>array('template1.tpl' => ac_module_internals_data_helper::STATE_OK));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('templates'))->willReturn($ModuleTemplates);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleTemplates'));
        $oModuleList->method('getModuleTemplates')->willReturn( $GlobalTemplates );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId', 'getModulesDir'));
        $oConfig->method('getShopId')->willReturn($ShopId);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleTemplates();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleTemplatesMissing()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleTemplates = array(
            'template1.tpl' => 'template1-missing.tpl',
        );

        $GlobalTemplates = array('module-id'=>array('template1.tpl' => 'template1-missing.tpl',));
        $ModulesDir = __DIR__.'/data/';

        $expectedResults = array('template1.tpl'=>array('template1-missing.tpl' => ac_module_internals_data_helper::STATE_FATAL_MODULE));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('templates'))->willReturn($ModuleTemplates);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleTemplates'));
        $oModuleList->method('getModuleTemplates')->willReturn( $GlobalTemplates );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId', 'getModulesDir'));
        $oConfig->method('getShopId')->willReturn($ShopId);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleTemplates();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleTemplatesRedundant()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleTemplates = array();

        $GlobalTemplates = array('module-id'=>array('template1.tpl' => 'template1-missing.tpl',));
        $ModulesDir = __DIR__.'/data/';

        $expectedResults = array('template1.tpl'=>array('template1-missing.tpl' => ac_module_internals_data_helper::STATE_FATAL_SHOP));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('templates'))->willReturn($ModuleTemplates);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleTemplates'));
        $oModuleList->method('getModuleTemplates')->willReturn( $GlobalTemplates );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId', 'getModulesDir'));
        $oConfig->method('getShopId')->willReturn($ShopId);
        $oConfig->method('getModulesDir')->willReturn($ModulesDir);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleTemplates();

        $this->assertEquals($expectedResults, $checkResults);
    }


    public function testCheckModuleEventsOK()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleEvents = array(
            'onEvent'=> 'module-class::onEvent',
        );

        $GlobalEvents = array('module-id'=>array('onEvent'=> 'module-class::onEvent',));

        $expectedResults = array('onEvent'=> array('module-class::onEvent' => ac_module_internals_data_helper::STATE_OK));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('events'))->willReturn($ModuleEvents);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleEvents'));
        $oModuleList->method('getModuleEvents')->willReturn( $GlobalEvents );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleEvents();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleEventsRedundant()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleEvents = array();

        $GlobalEvents = array('module-id'=>array('onEvent'=> 'module-class::onEvent',));

        $expectedResults = array('onEvent'=> array('module-class::onEvent' => ac_module_internals_data_helper::STATE_ERROR));

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('events'))->willReturn($ModuleEvents);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleEvents'));
        $oModuleList->method('getModuleEvents')->willReturn( $GlobalEvents );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleEvents();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleVersionsOK()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleVersion = '1.1';

        $GlobalVersions = array('module-id'=>'1.1');

        $expectedResults = array('1.1'=> ac_module_internals_data_helper::STATE_OK);

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('version'))->willReturn($ModuleVersion);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleVersions'));
        $oModuleList->method('getModuleVersions')->willReturn( $GlobalVersions );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleVersions();

        $this->assertEquals($expectedResults, $checkResults);
    }

    public function testCheckModuleVersionsMismatch()
    {
        $ShopId  = 'shop-id';
        $ModuleId  = 'module-id';
        $ModuleVersion = '1.1';

        $GlobalVersions = array('module-id'=>'1.0');

        $expectedResults = array(
            '1.1'=> ac_module_internals_data_helper::STATE_WARNING,
            '1.0'=> ac_module_internals_data_helper::STATE_ERROR,
        );

        $oModule = $this->getMock('oxmodule', array('getId', 'getInfo'));
        $oModule->method('getId')->willReturn($ModuleId);
        $oModule->method('getInfo')->with($this->equalTo('version'))->willReturn($ModuleVersion);

        $oModuleList = $this->getMock('oxmodulelist', array('getModuleVersions'));
        $oModuleList->method('getModuleVersions')->willReturn( $GlobalVersions );

        $oConfig = $this->getMock('oxConfig', array( 'getShopId'));
        $oConfig->method('getShopId')->willReturn($ShopId);

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);
        $helper->setConfig($oConfig);

        $checkResults  = $helper->checkModuleVersions();

        $this->assertEquals($expectedResults, $checkResults);
    }

}
