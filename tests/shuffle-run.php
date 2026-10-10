<?php

$repoRoot = dirname(__DIR__);
$files = array(
    $repoRoot . '/tests/AjaxControllerTest.php',
    $repoRoot . '/tests/ConfigValuesTest.php',
    $repoRoot . '/tests/ConfigXmlTest.php',
    $repoRoot . '/tests/DirectoryGuardTest.php',
    $repoRoot . '/tests/GetContentTest.php',
    $repoRoot . '/tests/HookDisplayHeaderTest.php',
    $repoRoot . '/tests/InstallTest.php',
    $repoRoot . '/tests/ModuleMetadataTest.php',
    $repoRoot . '/tests/RenderFormTest.php',
    $repoRoot . '/tests/RenderModalTest.php',
    $repoRoot . '/tests/RenderWidgetTest.php',
    $repoRoot . '/tests/SuiteManifestTest.php',
    $repoRoot . '/tests/UninstallPrestaShop16ModuleTest.php',
    $repoRoot . '/tests/Upgrade2005Test.php',
    $repoRoot . '/tests/WidgetVariablesTest.php',
);

mt_srand(20261010);
$count = count($files);
for ($i = $count - 1; $i > 0; --$i) {
    $j = mt_rand(0, $i);
    $tmp = $files[$i];
    $files[$i] = $files[$j];
    $files[$j] = $tmp;
}

$outDir = getenv('SHUFFLE_XML_DIR');
if ($outDir === false || $outDir === '') {
    fwrite(STDERR, "SHUFFLE_XML_DIR environment variable is required\n");
    exit(1);
}
$xmlPath = rtrim($outDir, '/') . '/phpunit-shuffled.xml';
$bootstrap = $repoRoot . '/tests/bootstrap.php';

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<phpunit bootstrap="' . htmlspecialchars($bootstrap, ENT_QUOTES, 'UTF-8') . "\"\n"
    . "         colors=\"false\"\n"
    . "         convertErrorsToExceptions=\"true\"\n"
    . "         convertNoticesToExceptions=\"true\"\n"
    . "         convertWarningsToExceptions=\"true\"\n"
    . "         beStrictAboutTestsThatDoNotTestAnything=\"true\"\n"
    . "         beStrictAboutOutputDuringTests=\"true\"\n"
    . "         beStrictAboutTestSize=\"true\"\n"
    . "         beStrictAboutTodoAnnotatedTests=\"true\">\n"
    . "    <testsuites>\n"
    . "        <testsuite name=\"module-tests\">\n";

foreach ($files as $file) {
    $xml .= '            <file>' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "</file>\n";
}

$xml .= "        </testsuite>\n"
    . "    </testsuites>\n"
    . "</phpunit>\n";

file_put_contents($xmlPath, $xml);
fwrite(STDOUT, $xmlPath);
