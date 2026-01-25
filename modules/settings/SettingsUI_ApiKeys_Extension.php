<?php
/**
 * OpenCATS API Settings Module
 *
 * Admin interface for managing API keys (sandbox accounts).
 * Accessible via: index.php?m=settings&a=apiKeys
 *
 * NOTE: This extends the existing Settings module.
 * Add these actions to modules/settings/SettingsUI.php
 *
 * @package    OpenCATS
 * @subpackage Settings
 * @license    CPAL-1.0
 */

// ============================================================
// ADD THESE METHODS TO: modules/settings/SettingsUI.php
// ============================================================

/**
 * API Keys Management Page
 * URL: index.php?m=settings&a=apiKeys
 */
public function apiKeys()
{
    // Check admin access
    if ($this->_accessLevel < ACCESS_LEVEL_SA) {
        CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        return;
    }

    include_once('./lib/ApiKeys.php');
    $apiKeys = new ApiKeys($this->_siteID);

    // Handle form submissions
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $message = '';
    $error = '';

    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $description = isset($_POST['description']) ? trim($_POST['description']) : '';
                $userID = $this->_userID;
                
                $result = $apiKeys->createSimple($userID, $description);
                
                if ($result) {
                    // Store credentials in session for one-time display
                    $_SESSION['new_api_credentials'] = $result;
                    $message = 'API Key created successfully!';
                }
            }
            break;

        case 'deactivate':
            $keyID = isset($_GET['keyID']) ? intval($_GET['keyID']) : 0;
            if ($keyID && $apiKeys->deactivate($keyID)) {
                $message = 'API Key deactivated.';
            }
            break;

        case 'activate':
            $keyID = isset($_GET['keyID']) ? intval($_GET['keyID']) : 0;
            if ($keyID && $apiKeys->activate($keyID)) {
                $message = 'API Key activated.';
            }
            break;

        case 'delete':
            $keyID = isset($_GET['keyID']) ? intval($_GET['keyID']) : 0;
            if ($keyID && $apiKeys->delete($keyID)) {
                $message = 'API Key deleted.';
            }
            break;

        case 'regenerate':
            $keyID = isset($_GET['keyID']) ? intval($_GET['keyID']) : 0;
            if ($keyID) {
                $result = $apiKeys->regenerateSecret($keyID);
                if ($result) {
                    $_SESSION['regenerated_secret'] = $result['api_secret'];
                    $message = 'Secret regenerated. Copy it now!';
                }
            }
            break;
    }

    // Get all API keys
    $allKeys = $apiKeys->getAll();

    // Check for new credentials to display
    $newCredentials = null;
    if (isset($_SESSION['new_api_credentials'])) {
        $newCredentials = $_SESSION['new_api_credentials'];
        unset($_SESSION['new_api_credentials']);
    }

    $regeneratedSecret = null;
    if (isset($_SESSION['regenerated_secret'])) {
        $regeneratedSecret = $_SESSION['regenerated_secret'];
        unset($_SESSION['regenerated_secret']);
    }

    // Assign to template
    $this->_template->assign('apiKeys', $allKeys);
    $this->_template->assign('newCredentials', $newCredentials);
    $this->_template->assign('regeneratedSecret', $regeneratedSecret);
    $this->_template->assign('message', $message);
    $this->_template->assign('error', $error);
    $this->_template->assign('active', $this);
    $this->_template->display('./modules/settings/ApiKeys.tpl');
}


// ============================================================
// ALSO ADD TO handleRequest() switch statement:
// ============================================================
/*
case 'apiKeys':
    $this->apiKeys();
    break;
*/
