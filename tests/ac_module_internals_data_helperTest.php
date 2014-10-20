<?php
/**
 * Module internals tools data helper tests.
 *
 * @author Alfonsas Cirtautas
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

    public function testGetInfo()
    {
        $oModule = $this->getMock('oxmodule', array('getInfo'));
        $oModule->method('getInfo')->willReturn('info');

        $oModuleList = $this->getMock('oxmodulelist');

        $helper = new ac_module_internals_data_helper($oModule, $oModuleList);

        $this->assertEquals($helper->getInfo('id'), 'info');
    }
}
