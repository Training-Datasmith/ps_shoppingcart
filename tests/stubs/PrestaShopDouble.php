<?php

class TestAdminRedirect extends Exception
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
        parent::__construct('redirect');
    }
}

class TestSmarty
{
    private $vars = array();

    public function getTemplateVars($name = null)
    {
        if ($name === null) {
            return $this->vars;
        }
        return array_key_exists($name, $this->vars) ? $this->vars[$name] : null;
    }

    public function assign($tpl_var, $value = null)
    {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$tpl_var] = $value;
        }
    }

}

class TestFrontController
{
    public $registerJavascriptLog = array();

    public function registerJavascript($id, $path, $params = array())
    {
        $this->registerJavascriptLog[] = array($id, $path, $params);
    }

    public function getLanguages()
    {
        return array('lang-sentinel');
    }
}

class TestLink
{
    public static $pageLinkLog = array();
    public static $moduleLinkLog = array();
    public static $adminLinkLog = array();

    public function getPageLink()
    {
        TestLink::$pageLinkLog[] = func_get_args();
        return 'LINK_PAGE_SENTINEL';
    }

    public function getModuleLink()
    {
        TestLink::$moduleLinkLog[] = func_get_args();
        return 'LINK_MODULE_SENTINEL';
    }

    public function getAdminLink($controller, $withToken = true)
    {
        TestLink::$adminLinkLog[] = func_get_args();
        return 'LINK_ADMIN_SENTINEL';
    }
}

class Configuration
{
    private static $values = array();
    public static $catalogMode = false;
    public static $updateLog = array();

    public static function reset()
    {
        self::$values = array();
        self::$catalogMode = false;
        self::$updateLog = array();
    }

    public static function get($key)
    {
        return array_key_exists($key, self::$values) ? self::$values[$key] : false;
    }

    public static function set($key, $value)
    {
        self::$values[$key] = $value;
    }

    public static function updateValue($key, $value)
    {
        self::$updateLog[] = array($key, $value);
        if (isset(self::$updateReturn)) {
            return self::$updateReturn;
        }
        self::$values[$key] = $value;
        return true;
    }

    public static $updateReturn = null;

    public static function isCatalogMode()
    {
        return self::$catalogMode;
    }
}

class Tools
{
    public static $redirectLog = array();
    public static $getAdminTokenLiteLog = array();

    public static function getValue($key, $default_value = false)
    {
        if (empty($key) || !is_string($key)) {
            return false;
        }
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default_value;
    }

    public static function isSubmit($submit)
    {
        return isset($_POST[$submit]) || isset($_POST[$submit . '_x']) || isset($_POST[$submit . '_y'])
            || isset($_GET[$submit]) || isset($_GET[$submit . '_x']) || isset($_GET[$submit . '_y']);
    }

    public static function redirectAdmin($url)
    {
        self::$redirectLog[] = $url;
        throw new TestAdminRedirect($url);
    }

    public static function getAdminTokenLite($tab)
    {
        self::$getAdminTokenLiteLog[] = $tab;
        return 'TOKEN_SENTINEL';
    }
}

class Language
{
    public $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }
}

class HelperForm
{
    public static $lastInstance;

    public $show_toolbar;
    public $table;
    public $identifier;
    public $submit_action;
    public $default_form_language;
    public $allow_employee_form_lang;
    public $currentIndex;
    public $token;
    public $tpl_vars = array();

    public static $lastForms = array();

    public function generateForm($forms)
    {
        self::$lastForms = $forms;
        self::$lastInstance = $this;
        return 'FORM_SENTINEL';
    }
}

class Module
{
    public $id = 42;
    public $name;
    public $table = 'module';
    public $identifier = 'id_module';
    public $context;
    public $smarty;
    public $tab;
    public $local_path;

    public static $installReturn = true;
    public static $registerHookLog = array();
    public static $registerHookReturns = array();
    public static $unregisterHookLog = array();
    public static $isInstalledMap = array();
    public static $instancesByName = array();
    public static $displayErrorLog = array();
    public static $fetchLog = array();
    public static $uninstallLog = array();

    public function __construct()
    {
        $this->context = new \stdClass();
        $this->context->smarty = new TestSmarty();
        $this->context->link = new TestLink();
        $this->context->controller = new TestFrontController();
        $this->context->language = new \stdClass();
        $this->context->language->id = 1;
        $this->context->cart = new \stdClass();
        $this->context->cart->id = 99;
        $this->smarty = new TestSmarty();
        $this->local_path = _PS_MODULE_DIR_ . 'ps_shoppingcart/';
    }

    public static $transLog = array();

    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        self::$transLog[] = array($id, $domain);
        return 'TRANS_SENTINEL';
    }

    public function install()
    {
        return self::$installReturn;
    }

    public function uninstall()
    {
        self::$uninstallLog[] = array('parent', $this);
        return true;
    }

    public function registerHook($hook_name)
    {
        self::$registerHookLog[] = $hook_name;
        if (isset(self::$registerHookReturns[$hook_name])) {
            return self::$registerHookReturns[$hook_name];
        }
        return true;
    }

    public function unregisterHook($hook_name)
    {
        self::$unregisterHookLog[] = $hook_name;
        return true;
    }

    public static function isInstalled($module_name)
    {
        return !empty(self::$isInstalledMap[$module_name]);
    }

    public static $getInstanceByNameLog = array();

    public static function getInstanceByName($module_name)
    {
        self::$getInstanceByNameLog[] = $module_name;
        return isset(self::$instancesByName[$module_name])
            ? self::$instancesByName[$module_name]
            : false;
    }

    public function displayError($error)
    {
        self::$displayErrorLog[] = $error;
        return 'ERROR_OUTPUT_SENTINEL';
    }

    public function fetch($templatePath)
    {
        self::$fetchLog[] = $templatePath;
        return 'FETCH_SENTINEL';
    }

    public static function resetStatics()
    {
        self::$installReturn = true;
        self::$registerHookLog = array();
        self::$registerHookReturns = array();
        self::$unregisterHookLog = array();
        self::$isInstalledMap = array();
        self::$instancesByName = array();
        self::$displayErrorLog = array();
        self::$fetchLog = array();
        self::$uninstallLog = array();
        self::$getInstanceByNameLog = array();
        self::$transLog = array();
        HelperForm::$lastInstance = null;
        TestLink::$pageLinkLog = array();
        TestLink::$moduleLinkLog = array();
        TestLink::$adminLinkLog = array();
        Tools::$redirectLog = array();
        Tools::$getAdminTokenLiteLog = array();
        Configuration::reset();
        Configuration::$updateReturn = null;
        HelperForm::$lastForms = array();
        \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentLog = array();
        \PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter::$presentResult = array(
            'products' => array(),
            'source' => 'presenter',
        );
    }
}

class LegacyBlockcart extends Module
{
    public function uninstall()
    {
        self::$uninstallLog[] = array('child', $this);
        return false;
    }
}

class ModuleFrontController
{
    public static $initContentCount = 0;

    public function initContent()
    {
        ++self::$initContentCount;
    }

    public static function resetStatics()
    {
        self::$initContentCount = 0;
    }
}
