<?php

class HookDisplayHeaderTest extends ShoppingCartTestCase
{
    public function testCatalogModeDoesNotRegisterJavascript()
    {
        Configuration::$catalogMode = true;
        Configuration::set('PS_BLOCK_CART_AJAX', 1);
        $module = $this->newModule();
        $result = $module->hookDisplayHeader();
        $this->assertNull($result);
        $this->assertInternalType('array', $module->context->controller->registerJavascriptLog);
        $this->assertCount(0, $module->context->controller->registerJavascriptLog);
    }

    public function testAjaxDisabledDoesNotRegisterJavascript()
    {
        Configuration::$catalogMode = false;
        Configuration::set('PS_BLOCK_CART_AJAX', 0);
        $module = $this->newModule();
        $module->hookDisplayHeader();
        $this->assertCount(0, $module->context->controller->registerJavascriptLog);
    }

    public function testAjaxEnabledRegistersShoppingCartScript()
    {
        Configuration::$catalogMode = false;
        Configuration::set('PS_BLOCK_CART_AJAX', 1);
        $module = $this->newModule();
        $this->assertNull($module->hookDisplayHeader());
        $this->assertCount(1, $module->context->controller->registerJavascriptLog);
        $this->assertSame(
            array('modules-shoppingcart', 'modules/ps_shoppingcart/ps_shoppingcart.js', array('position' => 'bottom', 'priority' => 150)),
            $module->context->controller->registerJavascriptLog[0]
        );
    }
}
