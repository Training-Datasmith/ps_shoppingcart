<?php

class AjaxControllerTest extends ShoppingCartTestCase
{
    public function testSslDefaultsToTrue()
    {
        $controller = new Ps_ShoppingcartAjaxModuleFrontController();
        $this->assertTrue($controller->ssl);
    }

    public function testAddToCartReturnsPreviewAndModalJson()
    {
        $result = $this->runAjaxWithPost(
            array(
                'action' => 'add-to-cart',
                'id_product' => '4',
                'id_product_attribute' => '5',
                'id_customization' => '6',
            )
        );
        $this->assertSame(0, $result['exitCode']);
        $this->assertNoPhpDiagnostics($result['stdout'], $result['stderr']);
        $decoded = json_decode($result['stdout'], true);
        $this->assertInternalType('array', $decoded);
        $this->assertSame('PREVIEW_RENDER_SENTINEL', $decoded['preview']);
        $this->assertSame('MODAL_RENDER_SENTINEL', $decoded['modal']);
        $this->assertSame(array(4, 5, 6), $result['state']['renderModal']);
        $this->assertSame(array(null, array()), $result['state']['renderWidget']);
        $this->assertSame(1, $result['parentInitCount']);
        $this->assertNotContains('BUFFER_SENTINEL', $result['stdout']);
    }

    public function testAddToCartCastsIdentifiersToInt()
    {
        $result = $this->runAjaxWithPost(
            array(
                'action' => 'add-to-cart',
                'id_product' => '8',
                'id_product_attribute' => '2',
                'id_customization' => '3',
            )
        );
        $this->assertSame(0, $result['exitCode']);
        $this->assertNoPhpDiagnostics($result['stdout'], $result['stderr']);
        $args = $result['state']['renderModal'];
        $this->assertSame(8, $args[0]);
        $this->assertSame(2, $args[1]);
        $this->assertSame(3, $args[2]);
    }

    public function testOtherActionOmitsModal()
    {
        $result = $this->runAjaxWithPost(array(), array('action' => 'refresh'));
        $this->assertSame(0, $result['exitCode']);
        $this->assertNoPhpDiagnostics($result['stdout'], $result['stderr']);
        $decoded = json_decode($result['stdout'], true);
        $this->assertNull($decoded['modal']);
        $this->assertSame('PREVIEW_RENDER_SENTINEL', $decoded['preview']);
    }

    public function testOutputBufferIsDiscarded()
    {
        $result = $this->runAjaxWithPost(
            array(
                'action' => 'add-to-cart',
                'id_product' => '1',
                'id_product_attribute' => '0',
                'id_customization' => '0',
            )
        );
        $this->assertSame(0, $result['exitCode']);
        $this->assertNoPhpDiagnostics($result['stdout'], $result['stderr']);
        $this->assertNotContains('BUFFER_SENTINEL', $result['stdout']);
        $this->assertInternalType('array', json_decode($result['stdout'], true));
    }
}
