<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EnvironmentSmokeTest extends TestCase
{
    public function test_php_runtime_is_supported(): void
    {
        $this->assertTrue(version_compare(PHP_VERSION, '8.2.0', '>='));
    }

    public function test_required_extensions_are_loaded(): void
    {
        foreach (['pdo', 'mbstring', 'openssl', 'json', 'gd', 'zip'] as $extension) {
            $this->assertTrue(extension_loaded($extension), "Missing PHP extension: {$extension}");
        }
    }
}
