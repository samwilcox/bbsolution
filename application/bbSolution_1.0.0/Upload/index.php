<?php

/**
 * bbSolution
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @license   http://www.bb-solution.org/license.php
 * @version   CSV: $Id:$
 */

/**
 * -- USER DEFINEABLE SETTINGS BELOW --
 */

/**
 * ROOT PATH
 * The absolute or relative path to where this installation is located.
 * Usually you don't need to change this setting, as it works on most
 * web servers.
 */
 $root_path = dirname(__FILE__) . '/';
 
/**
 * PHP EXTENSION
 * The PHP file extension that this installation will be using. If you
 * change this setting, you must rename all .php files in this
 * distribution to this new setting.
 */
 $php_extension = 'php';
 
/**
 * PHP ERROR REPORTING
 * If you are debugging, by uncommenting the error reporting below,
 * you will have access to useful debug information. Each error flag
 * is seperated by the pipe "|" character.
 */
 error_reporting( E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_RECOVERABLE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_USER_WARNING );
 
/**
 * -- END USER DEFINABLE SETTINGS --
 */

// Make sure we have a slash at the end of the root path
if ( substr( $root_path, strlen( $root_path ) - 1, strlen( $root_path ) ) != '/' )
{
    $root_path = $root_path . '/';
}

// Define the needed constants
define( 'THIS_VERSION', '1.0.0' );
define( 'PHP_EXT', $php_extension );
define( 'BBS', true );
define( 'ROOT_PATH', $root_path );



?>