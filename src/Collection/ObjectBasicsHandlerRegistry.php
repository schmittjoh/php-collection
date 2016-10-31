<?php

namespace Collection;

use Collection\ObjectBasicsHandler\IdentityHandler;

/**
 * Registry for handlers that provide ObjectBasics functionality for classes.
 *
 * You want to register a handler if you cannot implement the ObjectBasics interface, for example
 * because a class is provided by a third-party package, or built into PHP.
 *
 * @author Artyom Sukharev <aly.casus@gmail.com>, J. M. Schmitt
 */
abstract class ObjectBasicsHandlerRegistry
{
    private static $handlers = [
        'DateTime' => 'Collection\\ObjectBasicsHandler\\DateTimeHandler',
    ];
    private static $defaultObjectHandler;

    private static $aliases = [];

    /**
     * Defines an alias.
     *
     * $aliasClass must be a sub-type (extend or implement) $handlingClass; otherwise you will run into trouble.
     *
     * Aliases can only be one level deep,
     *
     *    i.e. aliasClass -> handlingClass                      is supported,
     *    but  aliasClass -> anotherAliasClass -> handlingClass is not.
     *
     * @param string $handlingClass The class that should be aliased, i.e. MyDateTime
     * @param string $aliasClass    The class that should be used instead, i.e. DateTime
     */
    public static function addAliasFor($handlingClass, $aliasClass)
    {
        self::$aliases[$handlingClass] = $aliasClass;
    }

    public static function addHandlerFor($handlingClass, $handlerInstanceOrClassName)
    {
        if (!$handlerInstanceOrClassName instanceof ObjectBasicsHandler && !is_string($handlerInstanceOrClassName)) {
            throw new \LogicException('$handler must be an instance of ObjectBasicsHandler, or a string referring to the handlers class.');
        }

        self::$handlers[$handlingClass] = $handlerInstanceOrClassName;
    }

    public static function getHandler($className)
    {
        if (isset(self::$aliases[$className])) {
            $className = self::$aliases[$className];
        }

        if (!isset(self::$handlers[$className])) {
            if (self::$defaultObjectHandler === null) {
                self::$defaultObjectHandler = new IdentityHandler();
            }

            return self::$defaultObjectHandler;
        }

        if (self::$handlers[$className] instanceof ObjectBasicsHandler) {
            return self::$handlers[$className];
        }

        if (is_string(self::$handlers[$className])) {
            $handlerClass = self::$handlers[$className];

            return self::$handlers[$className] = new $handlerClass();
        }

        throw new \LogicException(
            sprintf(
                'Unknown handler type ("%s") for class "%s" - should never be reached.',
                gettype(self::$handlers[$className]),
                $className
            )
        );
    }

    private final function __construct()
    {
    }
}