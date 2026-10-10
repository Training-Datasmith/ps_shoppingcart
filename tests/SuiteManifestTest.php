<?php

class SuiteManifestTest extends ShoppingCartTestCase
{
    public function testHardCodedFileListMatchesRepository()
    {
        $found = array();
        foreach (glob(dirname(__DIR__) . '/tests/*Test.php') as $path) {
            $found[] = 'tests/' . basename($path);
        }
        sort($found);
        $this->assertSame(self::EXPECTED_TEST_FILES, $found);
        $this->assertSame(self::EXPECTED_TEST_COUNT, $this->countPlannedTests());
        foreach (self::DIRECTORY_GUARDS as $relative) {
            $this->assertFileExists(dirname(__DIR__) . '/' . $relative);
        }
    }

    private function countPlannedTests()
    {
        $count = 0;
        foreach (self::EXPECTED_TEST_FILES as $file) {
            $class = basename($file, '.php');
            $ref = new ReflectionClass($class);
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (strpos($method->getName(), 'test') === 0) {
                    if ($method->getName() === 'testDirectoryGuardExits') {
                        $count += count(self::DIRECTORY_GUARDS);
                    } else {
                        ++$count;
                    }
                }
            }
        }
        return $count;
    }
}
