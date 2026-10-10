<?php

class RenderFormTest extends ShoppingCartTestCase
{
    public function testHelperReceivesModuleTableIdentifierAndSubmitAction()
    {
        $module = $this->newModule();
        $html = $module->renderForm();
        $this->assertSame('FORM_SENTINEL', $html);
        $helper = HelperForm::$lastInstance;
        $this->assertFalse($helper->show_toolbar);
        $this->assertSame('module', $helper->table);
        $this->assertSame('id_module', $helper->identifier);
        $this->assertSame('submitBlockCart', $helper->submit_action);
    }

    public function testDefaultLanguageComesFromPsLangDefault()
    {
        Configuration::set('PS_LANG_DEFAULT', '6');
        $module = $this->newModule();
        $module->renderForm();
        $this->assertSame(6, HelperForm::$lastInstance->default_form_language);
    }

    public function testEmployeeFormLangFallsBackToZero()
    {
        $module = $this->newModule();
        $module->renderForm();
        $this->assertSame(0, HelperForm::$lastInstance->allow_employee_form_lang);
    }

    public function testEmployeeFormLangUsesConfiguredValue()
    {
        Configuration::set('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 5);
        $module = $this->newModule();
        $module->renderForm();
        $this->assertSame(5, HelperForm::$lastInstance->allow_employee_form_lang);
    }

    public function testCurrentIndexTokenAndTemplateVariables()
    {
        $module = $this->newModule();
        $module->context->language->id = 9;
        $module->renderForm();
        $helper = HelperForm::$lastInstance;
        $this->assertContains('configure=ps_shoppingcart', $helper->currentIndex);
        $this->assertContains('tab_module=front_office_features', $helper->currentIndex);
        $this->assertContains('module_name=ps_shoppingcart', $helper->currentIndex);
        $this->assertSame('TOKEN_SENTINEL', $helper->token);
        $this->assertCount(1, Tools::$getAdminTokenLiteLog);
        $this->assertSame('AdminModules', Tools::$getAdminTokenLiteLog[0]);
        $this->assertSame(array('lang-sentinel'), $helper->tpl_vars['languages']);
        $this->assertSame(9, $helper->tpl_vars['id_language']);
        $this->assertArrayHasKey('PS_BLOCK_CART_AJAX', $helper->tpl_vars['fields_value']);
    }

    public function testFormSchemaDeclaresAjaxSwitch()
    {
        $module = $this->newModule();
        $module->renderForm();
        $form = HelperForm::$lastForms[0]['form'];
        $this->assertCount(1, $form['input']);
        $input = $form['input'][0];
        $this->assertSame('PS_BLOCK_CART_AJAX', $input['name']);
        $this->assertTrue($input['is_bool']);
        $values = array();
        foreach ($input['values'] as $v) {
            $values[] = $v['value'];
        }
        sort($values);
        $this->assertSame(array(0, 1), $values);
        $this->assertSame('TRANS_SENTINEL', $form['submit']['title']);
    }
}
