<?php
/**
 * OSATS
 */

/**
 *  Module Utility Library
 *  @package    OSATS
 *  @subpackage Library
 */
class ModuleUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


   public function stripslashes_deep($value)
	{
    return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	}

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
		
		//get $moduleName and $moduleClass from the db now.... and replace this code Jamin
        include_once('modules/' . $moduleName . '/' . $moduleClass . '.php');

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

        $moduleClass = $modules[$moduleName][0];//rewritten by Jamin

        include_once('modules/' . $moduleName . '/' . $moduleClass . '.php');

        $module = new $moduleClass();
//change this to be called out of the db. using $modules[$moduleName[# in array that is same as db row]] - Jamin
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

    /* Rewritten by Jamin.  Using DB instead of searching filesystem, etc.
     *
     * @return array modules array (indexed by module name)
     */
    private static function _refreshModuleList()
    {
        $modules = array();
        $hooks = array();
        /* Get a blocking advisory lock on the database. */
        $db = DatabaseConnection::getInstance();
        $db->getAdvisoryLock('OSATSUpdateLock', 120);
      	//this is only until I finish the entire rewrite, then I will pair things up. Jamin
		include('./dbconfig.php');
		$myServer = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
		$myDB = mysql_select_db(DATABASE_NAME);
		$sql = mysql_query("SELECT * FROM moduleinfo ORDER BY ordernum ASC");
		//$num_rows = mysql_num_rows($sql);
		
		while ($myrow = mysql_fetch_array($sql))
		{ 
			$moduleName = strtolower($myrow['name']);
			$moduleClass = $myrow['class'];
			//echo $moduleClass; -This is for testing only. - Jamin 2-19-2010
			include ('./modules/' . $moduleName . "/" . $moduleClass . ".php");
			$module = new $moduleClass();
			//$modules[] = $myrow['class']; 
			$modules[$moduleName] = array(
				$moduleClass,
				$myrow['tabtext'],
				//$myrow['subtabs'],
				$module->getSubTabsExternal(),
                $module->getSettingsEntries(),
                $module->getSettingsUserCategories(),
				//$myrow['setentries'],
				//$myrow['usercatagories'],
				$myrow['visible']);	
				
			$moduleHooks = $module->getHooks();
            foreach ($moduleHooks as $name => $data)
                {
                    	$hooks[$name][] = $data;
                }

             //self::processModuleSchema($moduleName, $module->getSchema());

		} 		

        $db->releaseAdvisoryLock('OSATSUpdateLock');

        /* Is called by installer? */
        if (isset($_POST['performMaintenence']))
        {
            die();
        }

        $_SESSION['hooks'] = $hooks;
        return $modules;
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
     * 
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
        set_time_limit(0);

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