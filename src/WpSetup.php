<?php
declare(strict_types=1);

/**
 * WordPress Setup Configuration Class
 * 
 * A lightweight, standalone WordPress configuration manager
 * inspired by Roots\WPConfig but with zero dependencies
 * 
 * @package    Alicom13\WpSetup
 * @author     alicom13
 * @version    1.0.1
 * @license    MIT
 * @link       https://github.com/alicom13/wp-setup
 */

namespace Alicom13\WpSetup;

use InvalidArgumentException;
use RuntimeException;

class WpSetup
{
    /**
     * @var array Stored configuration values
     */
    private static array $config = [];

    /**
     * @var array Configuration that should be defined as constants
     */
    private static array $constants = [];

    /**
     * @var bool Track if constants have been applied
     */
    private static bool $applied = false;

    /**
     * Define a configuration value as WordPress constant
     *
     * @param string $key
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public static function define(string $key, $value): void
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Configuration key must be a non-empty string');
        }

        if (self::$applied) {
            throw new RuntimeException(
                "Cannot define '{$key}' after constants have been applied. Call define() before apply()."
            );
        }

        self::$constants[$key] = $value;
    }

    /**
     * Get a configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    /**
     * Set a temporary configuration value (without defining as constant)
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value): void
    {
        self::$config[$key] = $value;
    }

    /**
     * Apply all defined configurations as WordPress constants
     *
     * @return array Applied constants
     */
    public static function apply(): array
    {
        $applied = [];

        foreach (self::$constants as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
                $applied[$key] = $value;
            }
        }

        self::$applied = true;
        return $applied;
    }

    /**
     * Check if a configuration is defined
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$constants[$key]) || isset(self::$config[$key]);
    }

    /**
     * Check if a constant is already defined in PHP
     *
     * @param string $key
     * @return bool
     */
    public static function isDefined(string $key): bool
    {
        return defined($key);
    }

    /**
     * Get all defined constants that will be applied
     *
     * @return array
     */
    public static function getAllConstants(): array
    {
        return self::$constants;
    }

    /**
     * Get all configuration values
     *
     * @return array
     */
    public static function getAllConfig(): array
    {
        return self::$config;
    }

    /**
     * Check if constants have been applied
     *
     * @return bool
     */
    public static function isApplied(): bool
    {
        return self::$applied;
    }

    /**
     * Clear all configurations (useful for testing)
     */
    public static function clear(): void
    {
        self::$config = [];
        self::$constants = [];
        self::$applied = false;
    }

    /**
     * Load configuration from array
     *
     * @param array $config
     */
    public static function loadFromArray(array $config): void
    {
        foreach ($config as $key => $value) {
            self::define($key, $value);
        }
    }

    /**
     * Load configuration from JSON file
     *
     * @param string $filePath
     * @return bool
     * @throws RuntimeException
     */
    public static function loadFromJson(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $json = file_get_contents($filePath);
        if ($json === false) {
            throw new RuntimeException("Unable to read configuration file: {$filePath}");
        }

        $config = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON configuration file: ' . json_last_error_msg());
        }

        if (!is_array($config)) {
            throw new RuntimeException('Configuration file must contain a JSON object');
        }

        self::loadFromArray($config);
        return true;
    }

    /**
     * Bulk define constants from environment variables with prefix
     *
     * @param string $prefix Filter environment variables by prefix
     */
    public static function loadFromEnv(string $prefix = 'WP_'): void
    {
        foreach ($_ENV as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                self::define($key, $value);
            }
        }
    }

    /**
     * Get the number of constants waiting to be applied
     *
     * @return int
     */
    public static function count(): int
    {
        return count(self::$constants);
    }

    /**
     * Remove a configuration before applying
     *
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        if (isset(self::$constants[$key])) {
            unset(self::$constants[$key]);
            return true;
        }
        
        if (isset(self::$config[$key])) {
            unset(self::$config[$key]);
            return true;
        }

        return false;
    }
}

/**
 * Setup - Short alias for WpSetup
 * 
 * Usage: Alicom13\WpSetup\Setup::define()
 */
class Setup extends WpSetup
{
    // Class alias for convenience
    // Now you can use: use Alicom13\WpSetup\Setup;
}
