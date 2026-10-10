<?php

class ModuleMetadataTest extends ShoppingCartTestCase
{
    public function testConstructorSetsModuleIdentity()
    {
        $module = $this->newModule();
        $this->assertSame('ps_shoppingcart', $module->name);
        $this->assertSame('front_office_features', $module->tab);
        $this->assertSame('3.0.0', $module->version);
        $this->assertSame('PrestaShop', $module->author);
        $this->assertSame(0, $module->need_instance);
        $this->assertTrue($module->bootstrap);
        $this->assertSame(array('ajax'), $module->controllers);
    }

    public function testConstructorUsesTranslationSentinelForLabels()
    {
        $module = $this->newModule();
        $this->assertSame('TRANS_SENTINEL', $module->displayName);
        $this->assertSame('TRANS_SENTINEL', $module->description);
        $this->assertGreaterThanOrEqual(2, count(Module::$transLog));
        $domains = array();
        foreach (Module::$transLog as $entry) {
            $domains[] = $entry[1];
        }
        $this->assertContains('Modules.Shoppingcart.Admin', $domains);
    }

    public function testVersionCompliancyUsesFloorAndRuntimeConstant()
    {
        $module = $this->newModule();
        $this->assertSame('1.7.5.0', $module->ps_versions_compliancy['min']);
        $this->assertSame('1.7.8.0', $module->ps_versions_compliancy['max']);
    }

    public function testImplementsWidgetInterfaceAndLegacyModuleConstant()
    {
        $module = $this->newModule();
        $this->assertInstanceOf('PrestaShop\\PrestaShop\\Core\\Module\\WidgetInterface', $module);
        $this->assertSame('blockcart', Ps_Shoppingcart::PS_16_EQUIVALENT_MODULE);
    }

    public function testFileExitsWhenPsVersionConstantMissing()
    {
        $moduleFile = dirname(__DIR__) . '/ps_shoppingcart.php';
        $script = 'include ' . var_export($moduleFile, true) . ";\n"
            . "echo 'SENTINEL_AFTER_INCLUDE';\n";
        list($stdout, $stderr, $exitCode) = $this->runPhpChildScript($script);
        $this->assertSame(0, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertNotContains('SENTINEL_AFTER_INCLUDE', $stdout);
        $this->assertNoPhpDiagnostics($stdout, $stderr);
    }
}
