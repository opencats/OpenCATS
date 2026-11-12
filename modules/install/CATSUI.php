<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

include_once(LEGACY_ROOT . '/modules/install/Schema.php');

class CATSUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'install';
        $this->_moduleName = 'install';
        $this->_schema = CATSSchema::get();
    }

    public function handleRequest()
    {
        if ($this->getAction() !== 'maint')
        {
            return;
        }

        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn())
        {
            CATSUtility::transferRelativeURI('m=login');
            die();
        }

        if ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA)
        {
            header('HTTP/1.1 403 Forbidden');
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }

        if (!SchemaMigrationStatus::hasPendingInstallMigrations())
        {
            CATSUtility::transferRelativeURI('');
            die();
        }

        $this->_template->assign(
            'csrfToken',
            $_SESSION['CATS']->getCSRFToken()
        );
        $this->_template->display('./modules/install/Maintenance.tpl');
    }
}

?>
