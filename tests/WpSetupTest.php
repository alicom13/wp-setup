<?php
declare(strict_types=1);

namespace Alicom13\WpSetup\Tests;

use PHPUnit\Framework\TestCase;
use Alicom13\WpSetup\WpSetup;
use InvalidArgumentException;
use RuntimeException;

class WpSetupTest extends TestCase
{
    protected function tearDown(): void
    {
        WpSetup::clear();
    }

    public function testDefineAndGet(): void
    {
        WpSetup::define('WP_TEST_CONSTANT', 'test_value');
        $this->assertTrue(WpSetup::has('WP_TEST_CONSTANT'));
    }

    public function testDefineWithEmptyKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WpSetup::define('', 'value');
    }

    public function testDefineAfterApplyThrowsException(): void
    {
        WpSetup::define('WP_FIRST', 'value');
        WpSetup::apply();

        $this->expectException(RuntimeException::class);
        WpSetup::define('WP_SECOND', 'value');
    }

    public function testApplyReturnsAppliedConstants(): void
    {
        WpSetup::define('WP_DEBUG', true);
        WpSetup::define('WP_HOME', 'https://example.com');
        
        $applied = WpSetup::apply();
        
        $this->assertCount(2, $applied);
        $this->assertArrayHasKey('WP_DEBUG', $applied);
        $this->assertTrue($applied['WP_DEBUG']);
    }

    public function testLoadFromArray(): void
    {
        $config = [
            'WP_DEBUG' => true,
            'WP_MEMORY_LIMIT' => '256M'
        ];

        WpSetup::loadFromArray($config);
        
        $this->assertTrue(WpSetup::has('WP_DEBUG'));
        $this->assertTrue(WpSetup::has('WP_MEMORY_LIMIT'));
    }
}
