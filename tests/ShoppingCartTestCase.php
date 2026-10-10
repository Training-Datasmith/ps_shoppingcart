<?php

abstract class ShoppingCartTestCase extends PHPUnit_Framework_TestCase
{
    const EXPECTED_TEST_COUNT = 57;

    const EXPECTED_TEST_FILES = array(
        'tests/AjaxControllerTest.php',
        'tests/ConfigValuesTest.php',
        'tests/ConfigXmlTest.php',
        'tests/DirectoryGuardTest.php',
        'tests/GetContentTest.php',
        'tests/HookDisplayHeaderTest.php',
        'tests/InstallTest.php',
        'tests/ModuleMetadataTest.php',
        'tests/RenderFormTest.php',
        'tests/RenderModalTest.php',
        'tests/RenderWidgetTest.php',
        'tests/SuiteManifestTest.php',
        'tests/UninstallPrestaShop16ModuleTest.php',
        'tests/Upgrade2005Test.php',
        'tests/WidgetVariablesTest.php',
    );

    const DIRECTORY_GUARDS = array(
        'controllers/front/index.php',
        'controllers/index.php',
        'img/icon/index.php',
        'img/index.php',
        'index.php',
        'tests/index.php',
        'tests/phpstan/index.php',
        'translations/index.php',
    );

    const PHP_DIAGNOSTIC_PATTERN = '/(Notice:|Warning:|Deprecated:|Strict Standards:|Parse error:|Fatal error:|Catchable fatal error:|Recoverable fatal error:)/';

    protected function setUp()
    {
        parent::setUp();
        $this->resetTestState();
    }

    protected function tearDown()
    {
        $this->resetTestState();
        parent::tearDown();
    }

    protected function resetTestState()
    {
        $_GET = array();
        $_POST = array();
        $_REQUEST = array();
        Module::resetStatics();
        ModuleFrontController::resetStatics();
        Configuration::reset();
    }

    protected function newModule()
    {
        return new Ps_Shoppingcart();
    }

    protected function assertNoPhpDiagnostics($stdout, $stderr)
    {
        $this->assertNotRegExp(self::PHP_DIAGNOSTIC_PATTERN, $stdout, 'stdout contains PHP diagnostic');
        $this->assertNotRegExp(self::PHP_DIAGNOSTIC_PATTERN, $stderr, 'stderr contains PHP diagnostic');
    }

    /**
     * @return array stdout, stderr, exitCode, tmpDir
     */
    protected function runPhpChildScript($scriptBody)
    {
        $tmpDir = sys_get_temp_dir() . '/ps-cart-child-' . uniqid('', true);
        mkdir($tmpDir);
        $scriptFile = $tmpDir . '/child.php';
        $full = "<?php\nerror_reporting(-1);\nini_set('display_errors', 'stderr');\n" . $scriptBody;
        file_put_contents($scriptFile, $full);

        $exitCodeFile = $tmpDir . '/exitcode.txt';
        $cmd = 'php -d error_reporting=-1 -d display_errors=stderr -d display_startup_errors=1 '
            . '-d log_errors=0 -d output_buffering=0 '
            . escapeshellarg($scriptFile) . ' 2>' . escapeshellarg($tmpDir . '/stderr.txt')
            . '; echo $? > ' . escapeshellarg($exitCodeFile);
        $stdout = shell_exec($cmd);
        if ($stdout === null) {
            $stdout = '';
        }
        $stderr = is_file($tmpDir . '/stderr.txt') ? file_get_contents($tmpDir . '/stderr.txt') : '';
        $exitCode = is_file($exitCodeFile) ? (int) trim(file_get_contents($exitCodeFile)) : 1;

        return array($stdout, $stderr, $exitCode, $tmpDir);
    }

    protected function runAjaxWithPost(array $post, array $get = array())
    {
        $stateFile = sys_get_temp_dir() . '/ajax-state-' . uniqid('', true) . '.json';
        $shutdownFile = sys_get_temp_dir() . '/ajax-shutdown-' . uniqid('', true) . '.txt';
        $tmpDir = sys_get_temp_dir() . '/ajax-child-' . uniqid('', true);
        mkdir($tmpDir);
        $moduleDir = dirname(__DIR__);

        $script = sprintf(
            "<?php\nerror_reporting(-1);\nini_set('display_errors', 'stderr');\n"
            . "define('_PS_VERSION_', '1.7.8.0');\n"
            . "define('_PS_MODULE_DIR_', '/tmp/ps-modules/');\n"
            . "require_once %s;\n"
            . "require_once %s;\n"
            . "require_once %s;\n"
            . "require_once %s;\n"
            . "require_once %s;\n"
            . "require_once %s;\n"
            . "\$_GET = %s;\n\$_POST = %s;\n\$_REQUEST = array_merge(\$_GET, \$_POST);\n"
            . "Ps_ShoppingcartAjaxTestModule::\$ajaxStateFile = %s;\n"
            . "register_shutdown_function(function () {\n"
            . "    file_put_contents(%s, (string) ModuleFrontController::\$initContentCount);\n"
            . "});\n"
            . "ob_start();\n"
            . "echo 'BUFFER_SENTINEL';\n"
            . "\$controller = new Ps_ShoppingcartAjaxModuleFrontController();\n"
            . "\$controller->module = new Ps_ShoppingcartAjaxTestModule();\n"
            . "\$controller->initContent();\n",
            var_export($moduleDir . '/tests/stubs/WidgetInterface.php', true),
            var_export($moduleDir . '/tests/stubs/CartPresenter.php', true),
            var_export($moduleDir . '/tests/stubs/PrestaShopDouble.php', true),
            var_export($moduleDir . '/ps_shoppingcart.php', true),
            var_export($moduleDir . '/tests/stubs/AjaxTestModule.php', true),
            var_export($moduleDir . '/controllers/front/ajax.php', true),
            var_export($get, true),
            var_export($post, true),
            var_export($stateFile, true),
            var_export($shutdownFile, true)
        );

        $scriptFile = $tmpDir . '/run.php';
        file_put_contents($scriptFile, $script);
        $exitCodeFile = $tmpDir . '/exitcode.txt';
        $cmd = 'php -d error_reporting=-1 -d display_errors=stderr -d display_startup_errors=1 '
            . '-d log_errors=0 -d output_buffering=0 '
            . escapeshellarg($scriptFile) . ' 2>' . escapeshellarg($tmpDir . '/stderr.txt')
            . '; echo $? > ' . escapeshellarg($exitCodeFile);
        $stdout = shell_exec($cmd);
        if ($stdout === null) {
            $stdout = '';
        }
        $stderr = file_get_contents($tmpDir . '/stderr.txt');
        $exitCode = is_file($exitCodeFile) ? (int) trim(file_get_contents($exitCodeFile)) : 1;
        $state = is_file($stateFile) ? json_decode(file_get_contents($stateFile), true) : array();
        $parentCount = is_file($shutdownFile) ? (int) file_get_contents($shutdownFile) : 0;

        return array(
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exitCode' => $exitCode,
            'state' => is_array($state) ? $state : array(),
            'parentInitCount' => $parentCount,
        );
    }
}
