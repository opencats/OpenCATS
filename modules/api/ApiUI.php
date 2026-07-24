<?php
/*
 * CandidATS / OpenCATS — Minimal REST API module
 *
 * Adds a token-authenticated JSON API on top of the existing module
 * system, reusing the core Candidates library so behavior matches what
 * the web UI does (validation, duplicate checks, resume storage).
 *
 * Routes (all via index.php?m=api&a=...):
 *
 *   POST  /index.php?m=api&a=addCandidate
 *       multipart/form-data: firstName, lastName, email1, resume (file)
 *       -> creates candidate, attaches + parses resume, returns JSON
 *
 *   GET   /index.php?m=api&a=getCandidate&candidateID=123
 *       -> returns stored candidate fields as JSON
 *
 *   POST  /index.php?m=api&a=uploadResume&candidateID=123
 *       multipart/form-data: resume (file)
 *       -> attaches an additional resume to an existing candidate
 *
 * Auth: header  "Authorization: Bearer <API_KEY>"
 *       API_KEY is read from config/api.php (see that file's comments).
 *
 * This module deliberately bypasses session-based login
 * (_authenticationRequired = false) because it's meant for
 * machine-to-machine calls, not browser sessions. Instead every request
 * is checked against the static API key below. Treat that key like a
 * password — anyone with it has candidates.add-level access.
 */

include_once(LEGACY_ROOT . '/lib/Candidates.php');
include_once(LEGACY_ROOT . '/lib/AddressParser.php');
include_once(LEGACY_ROOT . '/lib/Attachments.php'); /* AttachmentCreator lives here */

class ApiUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleDirectory = 'api';
        $this->_moduleName = 'api';
        $this->_moduleTabText = '';
        $this->_subTabs = array();
    }

    public function handleRequest()
    {
        header('Content-Type: application/json');

        if (!$this->_checkApiKey())
        {
            http_response_code(401);
            echo json_encode(array('error' => 'Invalid or missing API key.'));
            return;
        }

        $action = $this->getAction();

        try
        {
            switch ($action)
            {
                case 'addCandidate':
                    $this->addCandidate();
                    break;

                case 'getCandidate':
                    $this->getCandidate();
                    break;

                case 'uploadResume':
                    $this->uploadResume();
                    break;

                default:
                    http_response_code(404);
                    echo json_encode(array('error' => 'Unknown action: ' . $action));
            }
        }
        catch (Exception $e)
        {
            http_response_code(500);
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    /* ------------------------------------------------------------ */
    /* Auth                                                          */
    /* ------------------------------------------------------------ */

    private function _checkApiKey()
    {
        $configPath = LEGACY_ROOT . '/config/api.php';
        if (!file_exists($configPath))
        {
            return false; /* Not configured -> API stays closed by default. */
        }
        include $configPath; /* Defines $API_KEY */

        $headers = function_exists('getallheaders') ? getallheaders() : array();
        $authHeader = '';
        foreach ($headers as $name => $value)
        {
            if (strtolower($name) === 'authorization')
            {
                $authHeader = $value;
                break;
            }
        }

        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0)
        {
            return false;
        }

        $providedKey = substr($authHeader, 7);
        return isset($API_KEY) && hash_equals($API_KEY, $providedKey);
    }

    /* ------------------------------------------------------------ */
    /* Actions                                                       */
    /* ------------------------------------------------------------ */

    private function addCandidate()
    {
        $candidates = new Candidates();

        $firstName  = isset($_POST['firstName']) ? $_POST['firstName'] : '';
        $lastName   = isset($_POST['lastName']) ? $_POST['lastName'] : '';
        $email1     = isset($_POST['email1']) ? $_POST['email1'] : '';

        if (empty($firstName) || empty($lastName))
        {
            http_response_code(400);
            echo json_encode(array('error' => 'firstName and lastName are required.'));
            return;
        }

        /*
         * Signature verified directly against lib/Candidates.php:
         * add($firstName, $middleName, $lastName, $email1, $email2,
         *     $phoneHome, $phoneCell, $phoneWork, $address, $address2,
         *     $city, $state, $zip, $source, $keySkills, $dateAvailable,
         *     $currentEmployer, $canRelocate, $currentPay, $desiredPay,
         *     $notes, $webSite, $bestTimeToCall, $enteredBy, $owner,
         *     $gender = '', $race = '', $veteran = '', $disability = '',
         *     $skipHistory = false, $country = '')
         */
        /*
         * This module bypasses session login (token auth instead), so
         * there's no logged-in $_SESSION['CATS'] user to attribute the
         * candidate to. Set this to a real user ID from your `user`
         * table (e.g. the automation account you create in Admin ->
         * Users) so candidates created via the API show a sane
         * "entered by" / "owner" in the UI instead of ID 0.
         */
        $currentUserID = 1; // TODO: replace with your automation user's actual userID

        $candidateID = $candidates->add(
            $firstName,
            '',                                                          // middleName
            $lastName,
            $email1,
            '',                                                          // email2
            isset($_POST['phoneHome']) ? $_POST['phoneHome'] : '',
            isset($_POST['phoneCell']) ? $_POST['phoneCell'] : '',
            isset($_POST['phoneWork']) ? $_POST['phoneWork'] : '',
            isset($_POST['address']) ? $_POST['address'] : '',
            '',                                                          // address2
            isset($_POST['city']) ? $_POST['city'] : '',
            isset($_POST['state']) ? $_POST['state'] : '',
            isset($_POST['zip']) ? $_POST['zip'] : '',
            0,                                                           // source
            isset($_POST['keySkills']) ? $_POST['keySkills'] : '',
            '0000-00-00',                                                // dateAvailable
            isset($_POST['currentEmployer']) ? $_POST['currentEmployer'] : '',
            '',                                                          // canRelocate
            '',                                                          // currentPay
            '',                                                          // desiredPay
            '',                                                          // notes
            '',                                                          // webSite
            '',                                                          // bestTimeToCall
            $currentUserID,                                              // enteredBy
            $currentUserID                                               // owner
        );

        $resumeInfo = null;
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK)
        {
            $resumeInfo = $this->_attachResume($candidateID, $_FILES['resume']);
        }

        echo json_encode(array(
            'candidateID' => $candidateID,
            'resume'      => $resumeInfo,
        ));
    }

    private function getCandidate()
    {
        $candidateID = isset($_GET['candidateID']) ? (int) $_GET['candidateID'] : 0;
        if ($candidateID <= 0)
        {
            http_response_code(400);
            echo json_encode(array('error' => 'candidateID is required.'));
            return;
        }

        $candidates = new Candidates();
        $row = $candidates->get($candidateID);

        if (!$row)
        {
            http_response_code(404);
            echo json_encode(array('error' => 'Candidate not found.'));
            return;
        }

        /* Trim to the fields you actually want exposed externally. */
        echo json_encode(array(
            'candidateID' => $candidateID,
            'firstName'   => $row['first_name'],
            'lastName'    => $row['last_name'],
            'email1'      => $row['email1'],
            'phoneHome'   => $row['phone_home'],
            'keySkills'   => $row['key_skills'],
            'resumes'     => $candidates->getResumes($candidateID),
        ));
    }

    private function uploadResume()
    {
        $candidateID = isset($_GET['candidateID']) ? (int) $_GET['candidateID'] : 0;
        if ($candidateID <= 0 || !isset($_FILES['resume']))
        {
            http_response_code(400);
            echo json_encode(array('error' => 'candidateID and resume file are required.'));
            return;
        }

        $resumeInfo = $this->_attachResume($candidateID, $_FILES['resume']);
        echo json_encode(array('candidateID' => $candidateID, 'resume' => $resumeInfo));
    }

    /* ------------------------------------------------------------ */
    /* Helpers                                                       */
    /* ------------------------------------------------------------ */

    /**
     * Attaches an uploaded resume using AttachmentCreator::createFromUpload(),
     * the SAME call modules/candidates/CandidatesUI.php uses for a manual
     * upload through the web UI - handles storage, duplicate detection,
     * and text extraction (pdftotext / antiword / etc.) identically.
     * Verified against lib/Attachments.php.
     *
     * createFromUpload() reads $_FILES by field name directly, so this
     * only works from within the same request that received the
     * multipart upload.
     */
    private function _attachResume($candidateID, $file)
    {
        $attachmentCreator = new AttachmentCreator();
        $attachmentCreator->createFromUpload(
            DATA_ITEM_CANDIDATE,
            $candidateID,
            'resume',   // must match the multipart field name in the request
            false,      // isProfileImage
            true        // extractText - runs the same parser the UI uses
        );

        if ($attachmentCreator->isError())
        {
            return array('error' => $attachmentCreator->getError());
        }

        return array(
            'duplicatesOccurred'    => $attachmentCreator->duplicatesOccurred(),
            'isTextExtractionError' => $attachmentCreator->isTextExtractionError(),
            'textExtractionError'   => $attachmentCreator->getTextExtractionError(),
            'extractedText'         => $attachmentCreator->getExtractedText(),
            'filename'              => $file['name'],
        );
    }
}
