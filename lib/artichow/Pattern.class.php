<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

require_once __DIR__ . "/Graph.class.php";

/**
 * All patterns must derivate from this class
 *
 * @package Artichow
 */
abstract class awPattern
{
    /**
     * Pattern arguments
     *
     * @var array
     */
    protected $args = [];

    /**
     * Load a pattern
     *
     * @param string $pattern Pattern name
     * @return Component
     */
    public static function get($pattern)
    {
        $file = ARTICHOW_PATTERN . DIRECTORY_SEPARATOR . $pattern . '.php';

        if (is_file($file)) {
            require_once $file;

            $class = $pattern . 'Pattern';

            if (class_exists($class)) {
                return new $class();
            } else {
                trigger_error("Class '" . $class . "' does not exist", E_USER_ERROR);
            }
        } else {
            trigger_error("Pattern '" . $pattern . "' does not exist", E_USER_ERROR);
        }
    }

    /**
     * Change pattern argument
     *
     * @param string $name Argument name
     */
    public function setArg($name, mixed $value)
    {
        if (is_string($name)) {
            $this->args[$name] = $value;
        }
    }

    /**
     * Get an argument
     *
     * @param string $name
     * @return mixed Argument value
     */
    protected function getArg($name, mixed $default = null)
    {
        if (array_key_exists($name, $this->args)) {
            return $this->args[$name];
        } else {
            return $default;
        }
    }

    /**
     * Change several arguments
     *
     * @param array $args New arguments
     */
    public function setArgs($args)
    {
        if (is_array($args)) {
            foreach ($args as $name => $value) {
                $this->setArg($name, $value);
            }
        }
    }
}

registerClass('Pattern', true);
