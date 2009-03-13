<?php
/*
   * OSATS
   * GNU License
   *
   *
*/

/* Each module can contain its own database schema updates. The schema is
 * returned to OSATS as an array of keys and values through SkypeSchema's
 * constructor. For more details on the module schema updater, read the
 * comments in the included Schema.php.
 *
 * This module will add a field called skype_enabled to every site record,
 * with a default value of 1 (enabled).
 */
include_once('modules/skype/Schema.php');

/* The module's class name must always have the same name as the file it is
 * contained in, and it must always end in UI. We recommend "ModuleNameUI",
 * (in camel-caps) for example, JobOrdersUI or CandidatesUI.
 */
class SkypeUI extends UserInterface
{
    /* The __construct() method MUST ALWAYS be present in your module. This is
     * a "magic" PHP method that is called automatically whenever the module is
     * initialized, as well as when the module is passed-by during the module
     * detection process.
     */
    public function __construct()
    {
        /* This line MUST ALWAYS be present in your module's constructor.
         * This allows the OSATS Module API to perform its own internal
         * initialization whenever your module is initialized or passed-by.
         */
        parent::__construct();

        /* If this is set to true, the user will be required to be logged in
         * to use the module. This is irrelevant for this module, because the
         * module doesn't do anything but alter other modules.
         *
         * For example, the Candidates module (and all of the other modules
         * that provide tabs) require login. If your module is going to expose
         * web pages to the public without login, you can change this setting.
         */
        $this->_authenticationRequired = true;

        /* This is the directory within modules/ that this module is contained
         * in. This should be the same as $this->_moduleName.
         */
        $this->_moduleDirectory = 'skype';

        /* The lowercase module name of the module (used for m=modulename in
         * URLs, etc.). This should be the same as $this->_moduleDirectory.
         */
        $this->_moduleName = 'skype';

        /* This is the text displayed on the module's tab. Set this to '' for
         * a module without a tab.
         */
        $this->_moduleTabText = '';

        /* An array of sub-tab names and URLs for any sub-tabs your module
         * will provide. See the Clients module for an example.
         */
        $this->_subTabs = array();

        /* An array of hooks provided by the module. This can be set to array()
         * if your module does not provide any hooks or filters. Unlike the
         * MOTD module, the Skype module's hooks are larger. We will define the
         * hooks using an external method (below).
         */
        $this->_hooks = $this->defineHooks();

        /* You can define a special Settings page for your module using the
         * variable $this->_settingsEntries. This will add a entry on the
         * Administration page of the Settings module for Skype links, which
         * points to ?m=skype&a=options.
         */
        $this->_settingsEntries = array(
            array(
                'Skype Options',
                osatutil::getIndexName() . '?m=skype&amp;a=settings',
                ACCESS_LEVEL_SA,
                'Change preferences for Skype links.'
            )
        );

        /* Load the Skype schema updates from the external file
         * modules/skype/Schema.php (included above). If your module does not
         * provide any schema changes, you may omit this line.
         */
        $this->_schema = SkypeSchema::get();
    }

    /* This method is called by __construct() and is not specific to the OSATS
     * API. It is the recommended way to define any hooks or filters that your
     * module will provide.
     */
    public function defineHooks()
    {
        /* We need to decide what parts of OSATS the Skype module will modify.
         * In modules/candidates/CandidatesUI.php, prior to the candidate
         * details being displayed, the hook CANDIDATE_SHOW is evaluated by
         * this line:
         *
         *     if (!eval(Hooks::get('CANDIDATE_SHOW'))) return;
         *
         * We need to add a hook to CANDIDATE_SHOW in order to add a filter to
         * the template before it is displayed. The basic way to do this is to
         * add code similar to this:
         *
         *     $this->_template->addFilter(\'
         *          include_once("modules/mymodule/MyModuleUI.php");
         *          $html = MyModuleUI::my_filter_method($html);
         *     \');
         *
         * Note the escaping of single quotes!
         *
         * When the template engine evaluates the filter, it will evaluate and
         * execute whatever code is passed to addFilter(). Inside the filter
         * code, the variable $html contains the HTML data that the page is
         * supposed to output. Modifying the $html variable will modify the HTML
         * output. By using the above method, the template engine will include
         * our module and call my_filter_method() and replace the output of the
         * page with whatever our my_filter_method() returns.
         *
         * We will also add a hook to create a filter for the Show Client and
         * Show Contact pages, as well as the Candidates, Clients, and Contacts
         * main listing pages.
         */

        /* Candidate doesn't include SkypeUI, so if we want to call a Skype
         * method from the Candidate module we must include SkypeUI.php first.
         */
        $skypeFilter = '
            $this->_template->addFilter(\'
                include_once("modules/skype/SkypeUI.php");
                $html = SkypeUI::filter_makePhoneNumbersSkypeLinks($html);
            \', 0);
        ';
        return array(
            /* Places to add filters. */
            'CANDIDATE_SHOW' => $skypeFilter,
            'CLIENTS_SHOW' => $skypeFilter,
            'CONTACTS_SHOW' => $skypeFilter,
            'CANDIDATE_LIST_BY_VIEW' => $skypeFilter,
            'CLIENTS_LIST_BY_VIEW' => $skypeFilter,
            'CONTACTS_LIST_BY_VIEW' => $skypeFilter
        );
    }

    /* $this->handleRequest() is called whenever a URL is visited that
     * contains m=skype. This method MUST ALWAYS be present in your module!
     */
    public function handleRequest()
    {
        /* The UserInterface::getAction() method returns the action specified
         * in the URL (...&a=myAction). Never access $_GET['a'] manually.
         */
        $action = $this->getAction();

        /* Determine the action that was specified. The 'default' action is
         * triggered whenever a module is visited without an action.
         */
        switch ($action)
        {
            case 'settings':
                if ($this->isPostBack())
                {
                    $this->onSettings();
                }
                else
                {
                    $this->settings();
                }
                break;

            default:
                break;
        }
    }

    /* This method is called by $this->handleRequest() to show the Settings
     * page for the Skype module.
     */
    public function settings()
    {
        /* Check and see what our current skype settings from the
         * database are.
         */
        $skypePreference = SkypeLibrary::getSkypePreference();

        /* Convert the 1/0 setting from the database into a PHP boolean. */
        if ($skypePreference['skypeEnabled'] == 1)
        {
            $skypeEnabled = true;
        }
        else
        {
            $skypeEnabled = false;
        }

        /* Store the current preference as a property on the template for
         * Settings.tpl to access.
         */
        $this->_template->assign('skypeEnabled', $skypeEnabled);

        /* This allows the template to access the module's interface. */
        $this->_template->assign('active', $this);

        /* Display the template. */
        $this->_template->display('./modules/skype/Settings.tpl');
    }

    /* This method is called by $this->handleRequest() to save the settings
     * for the skype module, then return to the Administration page.
     */
    public function onSettings()
    {
        /* See if the checkbox was checked when the form was saved. */
        $skypeCheck = $this->isChecked('skypeCheck', $_POST);

        /* We have to turn the PHP boolean values back into 1 / 0 for storage
         * in the database.
         */
        if ($skypeCheck)
        {
            $skypeValue = 1;
        }
        else
        {
            $skypeValue = 0;
        }

        /* Store the preferences in the database (see below). */
        SkypeLibrary::setSkypePreference($skypeValue);

        /* Output a header redirect to the browser to load the Administration
         * page.
         */
        osatutil::transferRelativeURI('m=settings&a=administration');
    }

    /* This is a filter method which is used earlier in the module. It is
     * passed a variable containing all of the HTML output from the template
     * engine before it is displayed to the browser.
     *
     * Modifying $html modifies the output of the template.
     *
     * The purpose of this filter is to replace all phone numbers in the
     * format xxx-xxx-xxxx with Skype links.
     */
    public static function filter_makePhoneNumbersSkypeLinks($html)
    {
        /* Are Skype links enabled? Check the database. */
        $skypePreference = SkypeLibrary::getSkypePreference();

        /* Only execute the filter if Skype links are enabled. */
        if ($skypePreference['skypeEnabled'] == 1 &&
            StringUtility::containsPhoneNumber($html))
        {
            /* Use the OSATS String Utility library to extract all of the phone
             * numbers from the template output.
             */
            $phoneNumbers = StringUtility::extractAllPhoneNumbers($html);

            /* Replace each phone number with a Skype link. */
            foreach ($phoneNumbers as $phoneNumber)
            {
                $link = '<a href="skype://+1'
                      . preg_replace('/[^0-9]+/', '', $phoneNumber['formatted'])
                      . '">' . $phoneNumber['formatted'] . '</a>';
                $html = str_replace($phoneNumber['formatted'], $link, $html);
            }
        }

        return $html;
    }
}

/* This class contains all the database functionality required for this
 * module. These functions could be placed in an outside file (such as
 * Library.php) without disrupting the OSATS API. We HIGHLY recommend that
 * database functionality is kept separate from the main module code (as
 * is shown in this example module).
 */
class SkypeLibrary
{
    /* Returns a array with one key ('skypeEnabled') which is '1' by
     * default.
     */
    public static function getSkypePreference()
    {
        /* Get an instance of DatabaseConnection. This is how we interface
         * with the OSATS database.
         */
        $db = DatabaseConnection::getInstance();

        /* Format the SQL query. */
        $sql = sprintf(
            "SELECT
                skype_enabled as skypeEnabled
             FROM
                site
            WHERE
                site_id = %s",
            $_SESSION['OSATS']->getSiteID()
        );

        /* This method executes the SQL query and returns an associative
         * array containing the results.
         */
        return $db->getAssoc($sql);
    }

    /* Sets the field skype_enabled in the site table. */
    public static function setSkypePreference($value)
    {
        /* Get an instance of DatabaseConnection. This is how we interface
         * with the OSATS database.
         */
        $db = DatabaseConnection::getInstance();

        /* Format the SQL query. */
        $sql = sprintf(
            "UPDATE
                site
            SET
                skype_enabled = %s
            WHERE
                site_id = %s",
            $db->makeQueryID($value),
            $_SESSION['OSATS']->getSiteID()
        );

        /* This method executes the SQL query. */
        return $db->query($sql);
    }
}

?>