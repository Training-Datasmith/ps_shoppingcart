<?php

class WidgetVariablesTest extends ShoppingCartTestCase
{
    public function testUsesPresentedSmartyCartAndBuildsBothUrls()
    {
        $module = $this->newModule();
        $cart = array('products' => array(array('id_product' => 1)), 'marker' => 'smarty-cart');
        $module->context->smarty->assign('cart', $cart);
        $module->context->language->id = 7;

        $vars = $module->getWidgetVariables('displayNav2', array('foo' => 'bar'));

        $this->assertSame($cart, $vars['cart']);
        $this->assertCount(0, \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentLog);
        $this->assertCount(1, TestLink::$moduleLinkLog);
        $this->assertSame(
            array('ps_shoppingcart', 'ajax', array(), null, null, null, true),
            TestLink::$moduleLinkLog[0]
        );
        $this->assertCount(1, TestLink::$pageLinkLog);
        $this->assertSame(
            array('cart', null, 7, array('action' => 'show'), false, null, true),
            TestLink::$pageLinkLog[0]
        );
        $this->assertSame('LINK_MODULE_SENTINEL', $vars['refresh_url']);
        $this->assertSame('LINK_PAGE_SENTINEL', $vars['cart_url']);
    }

    public function testEmptySmartyCartUsesCartPresenter()
    {
        $module = $this->newModule();
        $module->context->smarty->assign('cart', array());
        $presented = array('products' => array(), 'source' => 'presenter');
        \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentResult = $presented;

        $vars = $module->getWidgetVariables(null, array());

        $this->assertSame($presented, $vars['cart']);
        $this->assertCount(1, \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentLog);
        $this->assertSame($module->context->cart, \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentLog[0]);
    }
}
