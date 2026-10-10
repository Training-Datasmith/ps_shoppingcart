<?php

class ConfigValuesTest extends ShoppingCartTestCase
{
    public function testPostedOneOverridesStoredZero()
    {
        Configuration::set('PS_BLOCK_CART_AJAX', 0);
        $_POST['PS_BLOCK_CART_AJAX'] = '1';
        $module = $this->newModule();
        $values = $module->getConfigFieldsValues();
        $this->assertSame(true, $values['PS_BLOCK_CART_AJAX']);
    }

    public function testPostedZeroOverridesStoredOne()
    {
        Configuration::set('PS_BLOCK_CART_AJAX', 1);
        $_POST['PS_BLOCK_CART_AJAX'] = '0';
        $module = $this->newModule();
        $values = $module->getConfigFieldsValues();
        $this->assertSame(false, $values['PS_BLOCK_CART_AJAX']);
    }

    public function testMissingRequestUsesStoredConfiguration()
    {
        Configuration::set('PS_BLOCK_CART_AJAX', 1);
        $module = $this->newModule();
        $values = $module->getConfigFieldsValues();
        $this->assertSame(true, $values['PS_BLOCK_CART_AJAX']);
    }

    public function testMissingConfigurationDefaultsToFalse()
    {
        $module = $this->newModule();
        $values = $module->getConfigFieldsValues();
        $this->assertSame(false, $values['PS_BLOCK_CART_AJAX']);
    }
}
