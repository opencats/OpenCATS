<?php
/*
   * OSATS
   * Open Source License Applies
*/

/* The module's class name must always have the same name as the file it is
 * contained in, and it must always end in UI. We recommend "ModuleNameUI",
 * (in camel-caps) for example, JobOrdersUI or CandidatesUI.
 */
class AboutUI extends UserInterface
{
    
    public function __construct()
    {
        
        parent::__construct();

        $this->_authenticationRequired = true;
		$this->_moduleDirectory = 'about';
        $this->_moduleName = 'about';
        $this->_moduleTabText = 'about';
        $this->_subTabs = array();
    }

    public function handleRequest()
    {
        $action = $this->getAction();
        switch ($action)
        {
            case 'About':
            default:
                if ($this->isPostBack())
                {
                    $this->onabout();
                }
                else
                {
                    $this->about();
                }
                break;
        }
    }

    private function about()
    {
        /* Default values. */
        $helloHTML = '';
        $name = '';
        $this->_template->assign('helloHTML', $helloHTML);
        $this->_template->assign('name', $name);
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/about/about.tpl');
    }

    private function onabout()
    {
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
        $this->_template->assign('helloHTML', $helloHTML);
        $this->_template->assign('name', $name);
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/about/about.tpl');
    }
}

?>