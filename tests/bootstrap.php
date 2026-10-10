<?php

define('_PS_VERSION_', '1.7.8.0');
define('_PS_MODULE_DIR_', '/tmp/ps-modules/');

require_once __DIR__ . '/ShoppingCartTestCase.php';
require_once __DIR__ . '/stubs/WidgetInterface.php';
require_once __DIR__ . '/stubs/CartPresenter.php';
require_once __DIR__ . '/stubs/PrestaShopDouble.php';

require_once dirname(__DIR__) . '/ps_shoppingcart.php';
require_once dirname(__DIR__) . '/upgrade/upgrade-2.0.5.php';
require_once dirname(__DIR__) . '/controllers/front/ajax.php';
require_once __DIR__ . '/stubs/AjaxTestModule.php';
