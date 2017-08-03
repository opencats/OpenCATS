<?php
/**
 * CATS
 * Module Utility Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: ModuleUtility.php 3774 2007-11-30 18:17:49Z brian $
 */

/**
 *  Module Utility Library
 *  @package    CATS
 *  @subpackage Library
 */
class ModuleUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Loads a module.
     *
     * @param string module name
     * @return void
     */
    public static function loadModule($moduleName)
    {
        $modules = self::getModules();

        if (!isset($modules[$moduleName]))
        {
            if (class_exists('CommonErrors'))
            {
                CommonErrors::fatal(COMMONERROR_INVALIDMODULE, NULL, $moduleName);
            }
            else
            {
                echo ('Invalid module name \'' . htmlspecialchars($moduleName)
                     . '\'.<br />Is the module installed?!');
                die();
            }
        }

        $moduleClass = $modules[$moduleName][0];

        include_once(
            'modules/' . $moduleName . '/'
            . $moduleClass . '.php'
        );

        if (!eval(Hooks::get('LOAD_MODULE'))) return;

        $module = new $moduleClass();
        $module->handleRequest();
    }

    /**
     * Check each module for a tasks directory which contains events that need
     * to be registered with the Asychroneous Queue Processor.
     */
    public static function registerModuleTasks()
    {
        $modules = self::getModules();

        foreach ($modules as $moduleName => $moduleData)
        {
            $moduleClass = $moduleData[0];

            if (file_exists($taskFile =
                sprintf('./modules/%s/tasks/tasks.php',
                    $moduleName)))
            {
                include_once($taskFile);
            }
        }
    }

    /**
     * Checks whether or not a module requires authentication.
     *
     * @param string module name
     * @return boolean requires authentication
     */
    public static function moduleRequiresAuthentication($moduleName)
    {
        $modules = self::getModules();

        if (!isset($modules[$moduleName]))
        {
            /* Module doesn't exist; take them to the login page if not
             * logged in. If they are logged in, self::loadModule will throw
             * an invalid module error.
             */
            return true;
        }

        $moduleClass = $modules[$moduleName][0];

        include_once(
            'modules/' . $moduleName . '/'
            . $moduleClass . '.php'
        );

        $module = new $moduleClass();

        if (!method_exists($module, 'requiresAuthentication'))
        {
            /* If the module doesn't specify, assume it requires
             * authentication.
             */
            return true;
        }

        return $module->requiresAuthentication();
    }

    /**
     * Returns the modules array.
     *
     * @return array modules array (indexed by module name)
     */
    public static function getModules()
    {
        /* Should already be in the session, if not rescan modules dir and add to
         * current session.
         */
        if (!isset($_SESSION['modules']) || empty($_SESSION['modules']))
        {
            $modules = self::_refreshModuleList();
            $_SESSION['modules'] = $modules;
        }

        /* This shouldn't happen... sanity check. */
        if (empty($_SESSION['modules']))
        {
            self::_fatal('No modules found.');
        }

        return $_SESSION['modules'];
    }

    /**
     * Checks to see if the specified module exists.
     *
     * @param string module name
     * @return boolean module exists
     */
    public static function moduleExists($moduleName)
    {
        $modules = self::getModules();

        foreach ($modules as $name => $data)
        {
            if ($name == $moduleName)
            {
                return true;
            }
        }

        return false;
    }

    /*
     * Rescans module directory
     *
     * @return array modules array (indexed by module name)
     */
    private static function _refreshModuleList()
    {
        /* Modules array looks like this:
         *
         * $modules = array(
         *     'login'    => array('LoginUI',    ''),
         *     'home'     => array('HomeUI',     'Home'),
         *     ...
         *     'calendar' => array('CalendarUI', 'Calendar'),
         *     'settings' => array('SettingsUI', 'Settings'),
         *     'tests'    => array('TestsUI',    '')
         * );
         */

         /* Attempt to load the list of modules from a temporary file. */
        if (file_exists('modules.cache') && !isset($_POST['performMaintenence']) && CACHE_MODULES)
        {
            $modulesCache = unserialize(file_get_contents('modules.cache'));

            $_SESSION['hooks'] = $modulesCache->hooks;

            return $modulesCache->modules;
        }

        $modules = array();
        $moduleDirectories = array();
        $hooks = array();

        $directory = @opendir(MODULES_PATH) or self::_fatal(
            sprintf("Unable to open '%s'.", MODULES_PATH)
        );

        /* Loop through files / directories inside MODULES_PATH. */
        while ($filename = readdir($directory))
        {
            $fullModulePath = MODULES_PATH . $filename;

            /* Ignore files / directories that begin with '.', and any
             * non-directories.
             */
            if ($filename[0] !== '.' && is_dir($fullModulePath))
            {
                $moduleDirectories[] = $fullModulePath;
            }
        }

        closedir($directory);

        /* Get a blocking advisory lock on the database. */
        $db = DatabaseConnection::getInstance();
        $db->getAdvisoryLock('CATSUpdateLock', 120);

        /* FIXME: There has to be a better way to locate the UI filename. */
        foreach ($moduleDirectories as $directoryName)
        {
            $directory = @opendir($directoryName) or self::_fatal(
                sprintf("Unable to open '%s'.", $directoryName)
            );

            while ($filename = readdir($directory))
            {
                $fullFilePath = $directoryName . '/' . $filename;

                /* Search for UI file. */
                if (substr($filename, -6) !== 'UI.php')
                {
                    continue;
                }

                include_once($fullFilePath);

                $moduleName = basename($directoryName);
                $moduleClass = basename(substr($fullFilePath, 0, -4));

                $module = new $moduleClass();
                $modules[$moduleName] = array(
                    $moduleClass,
                    $module->getModuleTabText(),
                    $module->getSubTabsExternal(),
                    $module->getSettingsEntries(),
                    $module->getSettingsUserCategories()
                );

                $moduleHooks = $module->getHooks();
                foreach ($moduleHooks as $name => $data)
                {
                    $hooks[$name][] = $data;
                }

                self::processModuleSchema($moduleName, $module->getSchema());
            }

            closedir($directory);
        }

        $db->releaseAdvisoryLock('CATSUpdateLock');

        /* Is called by installer? */
        if (isset($_POST['performMaintenence']))
        {
            die();
        }

        $_SESSION['hooks'] = $hooks;

        /* Sort the modules. */
        uksort($modules , array('self', '_sortModules'));

        /* Verify that core modules are present. */
        self::_checkCoreModules($modules);

        /* Try to store the modules for future use. */
        if (CACHE_MODULES)
        {
            $modulesCache->modules = $modules;
            $modulesCache->hooks = $hooks;
            @file_put_contents('modules.cache', serialize($modulesCache));
        }

        return $modules;
    }

    /**
     * Verifies that core modules are installed and fatal()s out if not.
     *
     * @param array detected modules
     * @return void
     */
    private static function _checkCoreModules($modules)
    {
        $missing = array();

        foreach ($GLOBALS['coreModules'] as $key => $value)
        {
            if (!isset($modules[$key]))
            {
                $missing[] = $key;
            }
        }

        if (count($missing) > 0)
        {
            $error = 'One or more of CATS\' core modules is missing.<br />';

            foreach ($missing as $module)
            {
                $error .= 'Module "' . $module . '" not found.<br />';
            }

            self::_fatal($error);
        }
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @return void
     */
    private static function _fatal($error)
    {
        $template = new Template();

        $template->assign('errorMessage', $error);
        $template->display('./Error.tpl');

        echo '<!--';
         trigger_error(
             str_replace("\n", " ", 'Fatal Error raised: ' . $error)
         );
        echo '-->';

        die();
    }

    /**
     * Sorts modules based on the order specified in constants.php.
     *
     * If both modules are part of the core module list, do a comparison
     * based on order defined in constants file. If only one of them is
     * a custom module and it is A then always return -1, pushing it
     * down the list. If A is a core module then always push it up the
     * list. This way, core modules get displayed first followed by custom
     * modules.
     *
     * @param module A name
     * @param module B name
     * @return sort order for uksort
     */
    private static function _sortModules($a, $b)
    {
        if (!eval(Hooks::get('SORT_MODULES_RETURN_POS'))) return 1;
        if (!eval(Hooks::get('SORT_MODULES_RETURN_NEG'))) return -1;

        if (isset($GLOBALS['coreModules'][$a]))
        {
            if (isset($GLOBALS['coreModules'][$b]))
            {
                if ($GLOBALS['coreModules'][$a] > $GLOBALS['coreModules'][$b])
                {
                    return 1;
                }

                if ($GLOBALS['coreModules'][$a] == $GLOBALS['coreModules'][$b])
                {
                    return 0;
                }

                return -1;
            }

            return -1;
        }

        return 1;
    }

    /**
     * Returns the schema version numbers (in a result set format) of all
     * installed modules.
     *
     * @return array Multi-dimensional associative result set array of
     *               schema versions data.
     */
    public static function getModuleSchemaVersions()
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                name AS name,
                version AS version
            FROM
                module_schema
            ORDER BY
                name ASC"
        );
        
        return $db->getAllAssoc($sql);
    }

    /**
     * Checks the module's database schema version and makes sure that the
     * schema includes all updates from the module.  If not, it updates the
     * schema as the module has indicated.
     *
     * @param string Module name for which to process schema changes.
     * @param array Module schema updates array.
     * @return void
     */
    private static function processModuleSchema($moduleName, $schema)
    {
        if( ini_get('safe_mode') )
        {
			//don't do anything in safe mode
		}
		else
        {
            /* Don't limit the execution time of queries. */
            set_time_limit(0);
        }

		$executedQuery = false;

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                version AS version
            FROM
                module_schema
            WHERE
                name = %s",
            $db->makeQueryString($moduleName)
        );
        $rs = $db->getAssoc($sql);

        if (!empty($rs))
        {
            $currentVersion = $rs['version'];
        }
        else
        {
            $sql = sprintf(
                "INSERT INTO module_schema (
                    name,
                    version
                )
                VALUES (
                    %s,
                    0
                )",
                $db->makeQueryString($moduleName)
            );
            $db->query($sql);

            $currentVersion = 0;
        }

        /* Get the latest schema revision. */
        $schemaVersions = array_keys($schema);
        if (!empty($schemaVersions))
        {
            $newestVersion = max($schemaVersions);
        }
        else
        {
            $newestVersion = 0;
        }

        /* Do we have any updates to process? */
        if ($newestVersion <= $currentVersion)
        {
            return;
        }

        ksort($schema, SORT_NUMERIC);
        foreach ($schema as $version => $sql)
        {
            if ($version <= $currentVersion)
            {
                continue;
            }

			/* if maintPage, execute 1 query, output the next query and progress, and terminate. */
			global $maintPage;
			if ((isset($maintPage) && $maintPage === true))
			{
				if ($executedQuery == false)
				{
					$executedQuery = true;
				}
				else
				{
					$keys = array_keys($schema);
					rsort($keys, SORT_NUMERIC);
					$maxVersion = $keys[0];
					echo '<script>';
					echo 'setProgressUpdating(decode64("'.base64_encode($sql).'"), '.$version.', '.$maxVersion.', "'.$moduleName.'");';
					echo 'setTimeout("Installpage_maint();", 50);';
					echo '</script>';
					die();
				}
			}

            if (strpos($sql, 'PHP:') === 0)
            {
                /* Strip off the 'PHP:' and execute the code. */
                $PHPCode = substr($sql, 4);
                eval($PHPCode);
            }
            else
            {
                $SQLStatments = explode(';', $sql);

                foreach ($SQLStatments as $SQL)
                {
                    $SQL = trim($SQL);

                   	if (!empty($SQL))
                    {
                        $db->query($SQL);
                    }
                }
            }

            $sql = sprintf(
                "UPDATE
                    module_schema
                SET
                    version = %s
                WHERE
                    name = %s",
                $version,
                $db->makeQueryString($moduleName)
            );
            $rs = $db->query($sql);

            $currentVersion = $version;
        }
    }
}

?>
