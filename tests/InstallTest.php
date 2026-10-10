<?php

class InstallTest extends ShoppingCartTestCase
{
    public function testInstallRegistersHooksAndEnablesAjax()
    {
        Module::$isInstalledMap = array('blockcart' => false);
        $module = $this->newModule();
        $this->assertTrue($module->install());
        $this->assertContains('displayHeader', Module::$registerHookLog);
        $this->assertContains('displayNav2', Module::$registerHookLog);
        $this->assertContains(array('PS_BLOCK_CART_AJAX', 1), Configuration::$updateLog);
    }

    public function testInstallStopsWhenParentInstallFails()
    {
        Module::$installReturn = false;
        $module = $this->newModule();
        $this->assertFalse($module->install());
        $this->assertCount(0, Module::$registerHookLog);
        $this->assertCount(0, Configuration::$updateLog);
    }

    public function testInstallStopsWhenDisplayHeaderHookFails()
    {
        Module::$registerHookReturns = array('displayHeader' => false);
        $module = $this->newModule();
        $this->assertFalse($module->install());
        $this->assertContains('displayHeader', Module::$registerHookLog);
        $this->assertNotContains('displayNav2', Module::$registerHookLog);
        $this->assertCount(0, Configuration::$updateLog);
    }

    public function testInstallStopsWhenDisplayNav2HookFails()
    {
        Module::$registerHookReturns = array('displayNav2' => false);
        $module = $this->newModule();
        $this->assertFalse($module->install());
        $this->assertContains('displayNav2', Module::$registerHookLog);
        $this->assertCount(0, Configuration::$updateLog);
    }

    public function testInstallStopsWhenConfigurationUpdateFails()
    {
        Configuration::$updateReturn = false;
        $module = $this->newModule();
        $this->assertFalse($module->install());
        $this->assertCount(1, Configuration::$updateLog);
    }

    public function testInstallUninstallsLegacyModuleBeforeParentInstall()
    {
        $legacy = new LegacyBlockcart();
        Module::$isInstalledMap = array('blockcart' => true);
        Module::$instancesByName = array('blockcart' => $legacy);
        Module::$installReturn = true;
        $module = $this->newModule();
        $this->assertTrue($module->install());
        $this->assertContains(array('parent', $legacy), Module::$uninstallLog);
        $this->assertNotContains(array('child', $legacy), Module::$uninstallLog);
    }
}
