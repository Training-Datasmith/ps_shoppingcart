<?php

class RenderWidgetTest extends ShoppingCartTestCase
{
    public function testCatalogModeReturnsEmptyStringWithoutFetching()
    {
        Configuration::$catalogMode = true;
        $module = $this->newModule();
        $html = $module->renderWidget('hook', array());
        $this->assertSame('', $html);
        $this->assertCount(0, Module::$fetchLog);
    }

    public function testAssignsWidgetVariablesAndFetchesWidgetTemplate()
    {
        $module = $this->newModule();
        $module->context->smarty->assign('cart', array('products' => array()));
        $html = $module->renderWidget('displayNav2', array('x' => 1));
        $this->assertSame('FETCH_SENTINEL', $html);
        $this->assertCount(1, Module::$fetchLog);
        $this->assertSame('module:ps_shoppingcart/ps_shoppingcart.tpl', Module::$fetchLog[0]);
        $keys = array_keys($module->smarty->getTemplateVars());
        $this->assertContains('cart', $keys);
        $this->assertContains('refresh_url', $keys);
        $this->assertContains('cart_url', $keys);
    }
}
