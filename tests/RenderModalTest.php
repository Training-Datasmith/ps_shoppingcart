<?php

class RenderModalTest extends ShoppingCartTestCase
{
    private function seedPresentedCart(Ps_Shoppingcart $module, array $products)
    {
        $module->context->smarty->assign('cart', array('products' => $products));
    }

    public function testSelectsProductMatchingAllThreeIdentifiers()
    {
        $module = $this->newModule();
        $row = array(
            'id_product' => '5',
            'id_product_attribute' => '1',
            'id_customization' => '0',
            'name' => 'Match',
        );
        $this->seedPresentedCart($module, array($row));

        $html = $module->renderModal(5, 1, 0);

        $this->assertSame('FETCH_SENTINEL', $html);
        $this->assertSame($row, $module->smarty->getTemplateVars('product'));
        $this->assertSame('module:ps_shoppingcart/modal.tpl', Module::$fetchLog[0]);
    }

    public function testProductIdMismatchSelectsValidRow()
    {
        $module = $this->newModule();
        $near = array(
            'id_product' => '9',
            'id_product_attribute' => '1',
            'id_customization' => '0',
            'name' => 'Near',
        );
        $valid = array(
            'id_product' => '5',
            'id_product_attribute' => '1',
            'id_customization' => '0',
            'name' => 'Valid',
        );
        $this->seedPresentedCart($module, array($near, $valid));

        $module->renderModal(5, 1, 0);

        $this->assertSame($valid, $module->smarty->getTemplateVars('product'));
    }

    public function testAttributeMismatchSelectsValidRow()
    {
        $module = $this->newModule();
        $near = array(
            'id_product' => '5',
            'id_product_attribute' => '9',
            'id_customization' => '0',
            'name' => 'Near',
        );
        $valid = array(
            'id_product' => '5',
            'id_product_attribute' => '1',
            'id_customization' => '0',
            'name' => 'Valid',
        );
        $this->seedPresentedCart($module, array($near, $valid));

        $module->renderModal(5, 1, 0);

        $this->assertSame($valid, $module->smarty->getTemplateVars('product'));
    }

    public function testCustomizationMismatchSelectsValidRow()
    {
        $module = $this->newModule();
        $near = array(
            'id_product' => '5',
            'id_product_attribute' => '1',
            'id_customization' => '9',
            'name' => 'Near',
        );
        $valid = array(
            'id_product' => '5',
            'id_product_attribute' => '1',
            'id_customization' => '0',
            'name' => 'Valid',
        );
        $this->seedPresentedCart($module, array($near, $valid));

        $module->renderModal(5, 1, 0);

        $this->assertSame($valid, $module->smarty->getTemplateVars('product'));
    }

    public function testPresenterUsedWhenSmartyCartEmptyForModal()
    {
        $module = $this->newModule();
        $module->context->smarty->assign('cart', array());
        $row = array(
            'id_product' => '3',
            'id_product_attribute' => '0',
            'id_customization' => '0',
            'name' => 'FromPresenter',
        );
        \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentResult = array(
            'products' => array($row),
            'source' => 'presenter',
        );

        $module->renderModal(3, 0, 0);

        $this->assertCount(1, \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentLog);
        $this->assertSame($row, $module->smarty->getTemplateVars('product'));
    }
}
