<?php

class Upgrade2005Test extends ShoppingCartTestCase
{
    public function testUnregistersHeaderAndRegistersDisplayHeader()
    {
        $module = $this->newModule();
        $this->assertTrue(upgrade_module_2_0_5($module));
        $this->assertContains('header', Module::$unregisterHookLog);
        $this->assertContains('displayHeader', Module::$registerHookLog);
    }
}
