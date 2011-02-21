<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

/*
 * Path to Artichow
 */

define('ARTICHOW', dirname(__FILE__));


/*
 * Path to TrueType fonts
 * Don't change the value of the constant ARTICHOW_FONT, only the value of $path (due to a PHP bug)
 */

define('ARTICHOW_FONT', ARTICHOW.DIRECTORY_SEPARATOR.'font');


/*
 * Patterns directory
 */

define('ARTICHOW_PATTERN', ARTICHOW.DIRECTORY_SEPARATOR.'patterns');


/*
 * Images directory
 */

define('ARTICHOW_IMAGE', ARTICHOW.DIRECTORY_SEPARATOR.'images');


/*
 * Enable/disable cache support
 */
define('ARTICHOW_CACHE', TRUE);

/*
 * Prefix for class names
 * No prefix by default
 */
define('ARTICHOW_PREFIX', '');

/*
 * Trigger errors when use of a deprecated feature
 */
define('ARTICHOW_DEPRECATED', TRUE);

/*
 * Fonts to use
 */
$fonts = array(
	'Tuffy',
	'TuffyBold',
	'TuffyBoldItalic',
	'TuffyItalic'
);

?>