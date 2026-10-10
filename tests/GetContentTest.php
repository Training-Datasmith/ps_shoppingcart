<?php

class GetContentTest extends ShoppingCartTestCase
{
    public function testNoSubmitRendersFormWithoutSaving()
    {
        $module = $this->newModule();
        $html = $module->getContent();
        $this->assertSame('FORM_SENTINEL', $html);
        $this->assertCount(0, Configuration::$updateLog);
        $this->assertCount(0, Tools::$redirectLog);
        $this->assertCount(0, Module::$displayErrorLog);
    }

    public function testSubmitOneSavesIntegerAndRedirectsToConfirmation()
    {
        $_POST['submitBlockCart'] = '1';
        $_POST['PS_BLOCK_CART_AJAX'] = '1';
        $module = $this->newModule();
        try {
            $module->getContent();
            $this->fail('Expected redirect');
        } catch (TestAdminRedirect $e) {
            $this->assertStringStartsWith('LINK_ADMIN_SENTINEL', $e->url);
            $this->assertContains('configure=ps_shoppingcart', $e->url);
            $this->assertContains('conf=6', $e->url);
        }
        $this->assertSame(array(array('AdminModules')), TestLink::$adminLinkLog);
        $this->assertCount(1, Configuration::$updateLog);
        $this->assertSame(array('PS_BLOCK_CART_AJAX', 1), Configuration::$updateLog[0]);
    }

    public function testSubmitZeroSavesIntegerAndRedirectsToConfirmation()
    {
        $_POST['submitBlockCart'] = '1';
        $_POST['PS_BLOCK_CART_AJAX'] = '0';
        $module = $this->newModule();
        try {
            $module->getContent();
            $this->fail('Expected redirect');
        } catch (TestAdminRedirect $e) {
            $this->assertStringStartsWith('LINK_ADMIN_SENTINEL', $e->url);
            $this->assertContains('conf=6', $e->url);
        }
        $this->assertSame(array(array('AdminModules')), TestLink::$adminLinkLog);
        $this->assertSame(array('PS_BLOCK_CART_AJAX', 0), Configuration::$updateLog[0]);
    }

    public function testSubmitTwoShowsErrorAndDoesNotSave()
    {
        Configuration::set('PS_BLOCK_CART_AJAX', 1);
        $_POST['submitBlockCart'] = '1';
        $_POST['PS_BLOCK_CART_AJAX'] = '2';
        $module = $this->newModule();
        $html = $module->getContent();
        $this->assertCount(1, Module::$displayErrorLog);
        $this->assertContains('ERROR_OUTPUT_SENTINEL', $html);
        $this->assertContains('FORM_SENTINEL', $html);
        $this->assertCount(0, Configuration::$updateLog);
        $this->assertCount(0, Tools::$redirectLog);
        $this->assertSame(1, Configuration::get('PS_BLOCK_CART_AJAX'));
    }

    public function testSubmitGarbageShowsErrorAndDoesNotSave()
    {
        Configuration::set('PS_BLOCK_CART_AJAX', 1);
        $_POST['submitBlockCart'] = '1';
        $_POST['PS_BLOCK_CART_AJAX'] = 'garbage';
        $module = $this->newModule();
        $html = $module->getContent();
        $this->assertCount(1, Module::$displayErrorLog);
        $this->assertContains('ERROR_OUTPUT_SENTINEL', $html);
        $this->assertCount(0, Configuration::$updateLog);
        $this->assertCount(0, Tools::$redirectLog);
        $this->assertSame(1, Configuration::get('PS_BLOCK_CART_AJAX'));
    }
}
