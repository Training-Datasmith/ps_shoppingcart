<?php

class DirectoryGuardTest extends ShoppingCartTestCase
{
    /**
     * @dataProvider guardFileProvider
     */
    public function testDirectoryGuardExits($relativePath)
    {
        $full = dirname(__DIR__) . '/' . $relativePath;
        $script = 'include ' . var_export($full, true) . ";\n"
            . "echo 'SENTINEL_AFTER_INCLUDE';\n";
        list($stdout, $stderr, $exitCode) = $this->runPhpChildScript($script);
        $this->assertSame(0, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertNotContains('SENTINEL_AFTER_INCLUDE', $stdout);
        $this->assertNoPhpDiagnostics($stdout, $stderr);
    }

    public function guardFileProvider()
    {
        $rows = array();
        foreach (self::DIRECTORY_GUARDS as $path) {
            $rows[] = array($path);
        }
        return $rows;
    }
}
