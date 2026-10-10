<?php

class ConfigXmlTest extends ShoppingCartTestCase
{
    public function testConfigXmlNameAndVersionMatchModule()
    {
        $module = $this->newModule();
        $xml = file_get_contents(dirname(__DIR__) . '/config.xml');
        $this->assertContains('<name>' . $module->name . '</name>', $xml);
        $this->assertContains(
            '<version><![CDATA[' . $module->version . ']]></version>',
            $xml
        );
    }
}
