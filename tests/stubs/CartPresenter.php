<?php

namespace PrestaShop\PrestaShop\Adapter\Presenter\Cart;

class CartPresenter
{
    public static $presentLog = array();
    public static $presentResult = array('products' => array(), 'source' => 'presenter');

    public function present($cart)
    {
        self::$presentLog[] = $cart;
        return self::$presentResult;
    }
}
