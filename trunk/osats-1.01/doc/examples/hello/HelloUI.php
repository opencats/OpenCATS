<?php
/*
   * OSATS
   * Open Source License Applies
*/

/* The module's class name must always have the same name as the file it is
 * contained in, and it must always end in UI. We recommend "ModuleNameUI",
 * (in camel-caps) for example, JobOrdersUI or CandidatesUI.
 */
class HelloUI extends UserInterface
{
    /* The __construct() method MUST ALWAYS be present in your module. This is
     * a "magic" PHP method that is called automatically whenever the module is
     * initialized, as well as when the module is passed-by during the module
     * detection process.
     */
    public function __construct()
    {
        /* This line MUST ALWAYS be present in your module's constructor.
         * This allows the Module API to perform its own internal
         * initialization whenever your module is initialized or passed-by.
         */
        parent::__construct();

        /* If this is set to true, the user will be required to be logged in
         * to use the module.
         *
         * For example, the Candidates module (and all of the other modules
         * that provide tabs) require login. If your module is going to expose
         * web pages to the public without login, you can change this setting.
         */
        $this->_authenticationRequired = true;

        /* This is the directory within modules/ that this module is contained
         * in. This should be the same as $this->_moduleName.
         */
        $this->_moduleDirectory = 'hello';

        /* The lowercase module name of the module (used for m=modulename in
         * URLs, etc.). This should be the same as $this->_moduleDirectory.
         */
        $this->_moduleName = 'hello';

        /* This is the text displayed on the module's tab. Set this to '' for
         * a module without a tab.
         */
        $this->_moduleTabText = 'Hello';

        /* An array of sub-tab names and URLs for any sub-tabs your module
         * will provide. See the Clients module for an example.
         */
        $this->_subTabs = array();
    }


    /* $this->handleRequest() is called whenever a URL is visited that
     * contains m=hello. This method MUST ALWAYS be present in your module!
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
            case 'hello':
            default:
                /* The UserInterface::isPostBack() method returns true if
                 * "postback=postback" is specified in the POSTDATA for a form
                 * submission. This is used to pair actions that display forms
                 * with actions that submit forms.
                 *
                 * For example, when the user first visits the hello module,
                 * the 'hello' action is used, and there is no POSTDATA (and,
                 * of course, no "postback=postback" in it); therefor the
                 * $this->hello() method is called, which displays the "say
                 * hello" form. Whenever the submit button is clicked, the form
                 * submits POSTDATA to /index.php?m=hello&a=hello. Inside the
                 * POSTDATA, postback=postback is specified (a hidden form
                 * field), therefor $this->isPostBack() will return true, and
                 * $this->onHello() will be called instead.
                 */
                if ($this->isPostBack())
                {
                    $this->onHello();
                }
                else
                {
                    $this->hello();
                }
                break;
        }
    }

    /* This method is called by $this->handleRequest() when the module's tab is
     * visited, or when /index.php?m=hello&a=hello is loaded.
     */
    private function hello()
    {
        /* Default values. */
        $helloHTML = '';
        $name = '';

        /* $this->_template->assign() assigns a value to a variable that can be
         * used from within the template.
         */
        $this->_template->assign('helloHTML', $helloHTML);
        $this->_template->assign('name', $name);

        /* This allows the template to access the module's interface. */
        $this->_template->assign('active', $this);

        /* $this->_template->display() loads and displays a template file. */
        $this->_template->display('./modules/hello/Hello.tpl');
    }

    /* This method is called by $this->handleRequest() when the form displayed
     * by $this->hello() is submitted.
     */
    private function onHello()
    {
        /* $this->getTrimmedInput() returns the value of a variable from $_GET
         * or $_POST, with leading and trailing whitespace removed. If the
         * variable doesn't exist, '' will be returned.
         */
        $name = $this->getTrimmedInput('helloName', $_POST);

        if (empty($name))
        {
            $helloHTML = '';
        }
        else
        {
            $helloHTML = 'Hello <span id="helloNameSpan">' . $name .
                '</span>. Congratulations on writing your first module!';
        }

        /* $this->_template->assign() assigns a value to a variable that can be
         * used from within the template.
         */
        $this->_template->assign('helloHTML', $helloHTML);
        $this->_template->assign('name', $name);

        /* This allows the template to access the module's interface. */
        $this->_template->assign('active', $this);

        /* $this->_template->display() loads and displays a template file. */
        $this->_template->display('./modules/hello/Hello.tpl');
    }
}

?>