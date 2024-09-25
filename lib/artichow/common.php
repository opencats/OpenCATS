<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

/*
 * Get the minimum of an array and ignore non numeric values
 */
function array_min($array)
{
    if (is_array($array) and count($array) > 0) {
        do {
            $min = array_pop($array);
            if (is_numeric($min === false)) {
                $min = null;
            }
        } while (count($array) > 0 and $min === null);

        if ($min !== null) {
            $min = (float) $min;
        }

        foreach ($array as $value) {
            if (is_numeric($value) and (float) $value < $min) {
                $min = (float) $value;
            }
        }

        return $min;
    }

    return null;
}

/*
 * Get the maximum of an array and ignore non numeric values
 */
function array_max($array)
{
    if (is_array($array) and count($array) > 0) {
        do {
            $max = array_pop($array);
            if (is_numeric($max === false)) {
                $max = null;
            }
        } while (count($array) > 0 and $max === null);

        if ($max !== null) {
            $max = (float) $max;
        }

        foreach ($array as $value) {
            if (is_numeric($value) and (float) $value > $max) {
                $max = (float) $value;
            }
        }

        return $max;
    }

    return null;
}

/*
 * Register a class with the prefix in configuration file
 */
function registerClass($class, $abstract = false)
{
    if (ARTICHOW_PREFIX === 'aw') {
        return;
    }


    if ($abstract) {
        $abstract = 'abstract';
    } else {
        $abstract = '';
    }


    eval($abstract . " class " . ARTICHOW_PREFIX . $class . " extends aw" . $class . " { }");
}

/*
 * Register an interface with the prefix in configuration file
 */
function registerInterface($interface)
{
    if (ARTICHOW_PREFIX === 'aw') {
        return;
    }


    eval("interface " . ARTICHOW_PREFIX . $interface . " extends aw" . $interface . " { }");
}
