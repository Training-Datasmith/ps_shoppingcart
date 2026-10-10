<?php

class UninstallPrestaShop16ModuleTest extends ShoppingCartTestCase
{
    public function testDoesNotLoadInstanceWhenLegacyModuleIsNotInstalled()
    {
        Module::$isInstalledMap = array('blockcart' => false);
        $module = $this->newModule();
        $module->uninstallPrestaShop16Module();
        $this->assertCount(0, Module::$getInstanceByNameLog);
        $this->assertCount(0, Module::$uninstallLog);
    }

    public function testDoesNotUninstallWhenInstalledButInstanceMissing()
    {
        Module::$isInstalledMap = array('blockcart' => true);
        Module::$instancesByName = array();
        $module = $this->newModule();
        $module->uninstallPrestaShop16Module();
        $this->assertCount(1, Module::$getInstanceByNameLog);
        $this->assertCount(0, Module::$uninstallLog);
    }

    public function testParentUninstallRunsOnLegacyInstanceNotOverride()
    {
        $legacy = new LegacyBlockcart();
        Module::$isInstalledMap = array('blockcart' => true);
        Module::$instancesByName = array('blockcart' => $legacy);
        $module = $this->newModule();
        $module->uninstallPrestaShop16Module();
        $this->assertSame(array(array('parent', $legacy)), Module::$uninstallLog);
    }
}
