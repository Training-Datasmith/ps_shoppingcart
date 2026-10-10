<?php

class Ps_ShoppingcartAjaxTestModule extends Ps_Shoppingcart
{
    public static $ajaxStateFile;

    public function renderModal($id_product, $id_product_attribute, $id_customization)
    {
        $payload = array(
            'renderModal' => array($id_product, $id_product_attribute, $id_customization),
        );
        if (self::$ajaxStateFile) {
            file_put_contents(self::$ajaxStateFile, json_encode($payload));
        }

        return 'MODAL_RENDER_SENTINEL';
    }

    public function renderWidget($hookName, array $params)
    {
        $existing = array();
        if (self::$ajaxStateFile && is_file(self::$ajaxStateFile)) {
            $existing = json_decode(file_get_contents(self::$ajaxStateFile), true);
            if (!is_array($existing)) {
                $existing = array();
            }
        }
        $existing['renderWidget'] = array($hookName, $params);
        if (self::$ajaxStateFile) {
            file_put_contents(self::$ajaxStateFile, json_encode($existing));
        }

        return 'PREVIEW_RENDER_SENTINEL';
    }
}
