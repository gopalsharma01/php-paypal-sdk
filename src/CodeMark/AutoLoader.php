<?php

/**
 * PaypalSdk autoloader class
 *
 * @package   CodeMark
 * @author    Gopal Sharma
 * @copyright Copyright 2018, INDIA
 */

namespace CodeMark;

/**
 * Handles to loading all classes.
 *
 * This will handle to load all Package classes
 *
 * @package   CodeMark
 * @since     1
 */

class AutoLoader
{
    /**
     * Directory with src files
     *
     * @var string
     */
    private static $_DIR;

    /**
     * Class file extension
     */
    const EXT = '.php';

    /**
     * Init the autoloader
     *
     * @throws Exception
     * @return void
     */
    public static function init()
    {
        //self::checkEnv();

        self::$_DIR = __DIR__ . DIRECTORY_SEPARATOR;

        spl_autoload_register(__NAMESPACE__ . '\AutoLoader::load');
    }

    /**
     * Handle loading of an unknown class
     *
     * This will only handle class from its own namespace and ignore all others.
     *
     * This allows multiple autoloaders to be used in a nested fashion.
     *
     * @param string $className - name of class to be loaded
     *
     * @return void
     */
    public static function load($className)
    {
        $namespace = __NAMESPACE__ . '\\';
        $length    = strlen($namespace);

        if (0 !== strpos($className, $namespace)) {
            return;
        }

        // init() must have been called before
        assert(self::$_DIR !== null);

        require self::$_DIR . substr($className, $length) . self::EXTENSION;
    }

    /**
     * Check the current environment
     *
     * This will check whether the current environment is compatible with the
     * PHP-PAYPAL-SDK.
     *
     * @throws ClientException
     * @return void
     */
    private static function checkEnv()
    {
        list($high, $low) = explode('.', PHP_VERSION);

        if ((int) $high < 5 || ((int) $high === 5 && (int) $low < 6)) {
            throw new ClientException('Incompatible PHP environment. Expecting PHP 5.3 or higher');
        }
    }
}

//class_alias(Autoloader::class, '\triagens\ArangoDb\Autoloader');
