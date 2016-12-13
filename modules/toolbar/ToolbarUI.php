<?php
/*
 * CATS
 * Firefox Toolbar Module
 *
 * Copyright (C) 2007 Cognizo Technologies, Inc.
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
 * $Id: ToolbarUI.php 3691 2007-11-26 18:12:48Z brian $
 */

include_once('./lib/SystemInfo.php');
include_once('./lib/Mailer.php');
include_once('./lib/Site.php');
include_once('./lib/Candidates.php');
include_once('./lib/DocumentToText.php');
include_once('./lib/License.php');

/* Toolbar library version. Increment to notify toolbars of an update. */
define('TOOLBAR_LIB_VERSION', 33);


class ToolbarUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = false;
        $this->_moduleName = 'toolbar';
        $this->_moduleDirectory = 'toolbar';
    }

    public function handleRequest()
    {
        //$this->authenticate();

        $action = $this->getAction();

        switch ($action)
        {
			case 'storeGenericResume':
                $this->storeGenericResume();
                break;
				
            case 'attemptLogin':
                $this->attemptLogin();
                break;

            case 'getRemoteVersion':
                $this->getRemoteVersion();
                break;

            case 'getJavaScriptLib':
                $this->getJavaScriptLibLegacy();
                break;

            case 'authenticate':
                $this->authenticate();
                break;
			
			case 'authenticate_and_check':
				$this->authenticate_and_check();
				break;
			
            case 'checkEmailIsInSystem':
                $this->checkEmailIsInSystem();
                break;

            case 'storeMonsterResumeText':
                $this->storeMonsterResumeText();
                break;

            case 'getLicenseKey':
                $this->getLicenseKey();
                break;
        }
    }
	
	private function authenticate_and_check() {
		$result = 1;
        $siteID = 1;
        $siteName = '';
        $username = $this->getTrimmedInput('CATSUser', $_GET);
        $password = $this->getTrimmedInput('CATSPassword', $_GET);
        //if(!$_SESSION['CATS']->isLoggedIn()) {
            $_SESSION['CATS']->processLogin($username, $password); //force to authenticate each time
        //}

        if (!$_SESSION['CATS']->isLoggedIn()) {
            $result = 0; //invalid login
        } else {
			if($this->checkEmailExists($_GET["emailaddress"])==true) {
				$result = 2; // authenticated but candiate already exists
			} else
				$result = 1; // authenticated and checked
		}		
        echo($result);		
		flush();
	}
	
	private function storeGenericResume() {
		$this->_authenticate();
		$storedID = -1; //default, invalid
		if($this->checkEmailExists($_GET["emailaddress"])==true) {
			$storedID = -2; //email already exists
		} else {
			switch(strtoupper($_GET["site"])) {
				case "GMAIL.COM":
					//store gmail resume
					$storedID = $this->storeGMailFile($_GET["ftype"]);					
					break;
				case "MONSTER.CA":
					//parse monster resume
					$storedID = $this->storeMonsterResume();
					break;
				case "WORKPOLIS.CA":
					//parse workpolis resume
					$storedID = $this->storeWorkpolisResume();
					break;
				case "LINKEDIN.COM":
					$storedID = $this->storeLinkedInResume();
					break;
			}
		}
		$reply = $storedID."|".strtoupper($_GET["site"]);
		//if gmail, reserve third response for the type of the file that was just uploaded, and the file type
		if($_GET["site"] == "GMail.com") {
			$reply .= "|". $_GET["ftype"] . "|" . $_GET["resume_type"];
		}
		echo($reply);		
		flush();
	}
	
	private function storeGMailFile($ftype) {
		$resumeText = base64_decode(file_get_contents("php://input")); //our gmail attachment resume was previously base64 encoded		
		if($ftype == "resume") {
			$temporaryFile = FileUtility::makeRandomTemporaryFilePath() . $_GET['resume_type'];
			if (file_put_contents($temporaryFile, $resumeText) === false) {
				$this->fatal('Failed to save Gmail data for parsing.');
			}
			$documentToText = new DocumentToText();
			switch($_GET['resume_type']) {
				case "DOC": //200
					$documentType = $documentToText->getDocumentType($temporaryFile, 'application/msword');
					break;
				case "PDF": //100
					$documentType = $documentToText->getDocumentType($temporaryFile, 'application/pdf');
					break;
				case "RTF": //300
					$documentType = $documentToText->getDocumentType($temporaryFile, 'application/rtf');
					break;
				case "DOCX": //400, application/vnd.ms-word.document.12
					$documentType = $documentToText->getDocumentType($temporaryFile, 'application/vnd.ms-word.document.12');
					break;
				default: //text html
					$documentType = $documentToText->getDocumentType($temporaryFile, 'text/html');
					break;	
			}		
			
			$documentToText->convert($temporaryFile, $documentType);
			if ($documentToText->isError())
			{
				$this->_isTextExtractionError = true;
				$this->_textExtractionError = $documentToText->getError();
				$parsedText = '';
			}
			else
			{
				$parsedText = $documentToText->getString(); 
			}
			@unlink($temporaryFile);		
			$storedID = $_SESSION['CATS']->storeData($parsedText);			
		} else {
			//store the photo directly
			$storedID = $_SESSION['CATS']->storeData($resumeText);
		}
        
		
        return $storedID;
	}
	
    private function _authenticate()
    {
        /* Get username / password, and apply ASP username if applicable. */
        $siteID = 1;
        $siteName = '';

        $username = $this->getTrimmedInput('CATSUser', $_GET);
        $password = $this->getTrimmedInput('CATSPassword', $_GET);

        //if (!eval(Hooks::get('TOOLBAR_AUTHENTICATE_PRE'))) return;

        //if(!$_SESSION['CATS']->isLoggedIn())
        //{
            $_SESSION['CATS']->processLogin($username, $password);
        //}

        //if (!eval(Hooks::get('TOOLBAR_AUTHENTICATE_POST'))) return;

        if (!$_SESSION['CATS']->isLoggedIn())
        {
            //echo 'cats_authenticationFailed(); Message:You do not have permision to use the toolbar.';
            //echo 'cats_authenticationFailed(); Message:'.$_SESSION['CATS']->getLoginError();
			echo 'Authentication Failed!';
            die();
        }
		/*
        if (!ModuleUtility::moduleExists('asp'))
        {
            if (!LicenseUtility::isProfessional())
            {
                echo 'cats_authenticationFailed(); Message:The FireFox toolbar extension '
                    . 'is only available to CATS Professional users. See catsone.com/Professional for '
                    . 'more information.';
                die();
            }
        }
		*/
        return true;
    }

    private function authenticate()
    {
        if (!$this->_authenticate())
        {
            // FIXME: Do something here?
        }

        // FIXME: Make protocol less bandwidth-intensive.
        echo 'cats_connected = true';
        if (isset($_GET['callback']))
        {
            echo ' EVAL=', $_GET['callback'];
        }
    }
    private function getRemoteVersion()
    {
        // Obsolete function used to notify old toolbars that they are no longer supported.
        // FIXME:  Remove me after toolbar migration is finished.
        echo 99999;
    }


    private function getJavaScriptLibLegacy()
    {
        // FIXME: Send a JS library that just makes a button indicating that their version
        // is out of date.

        $toolbarLibrary = @file_get_contents('./modules/toolbar/toolbarlibForLegacy.js');
        echo $toolbarLibrary;
        return;
    }

    private function checkEmailIsInSystem()
    {
        if (!eval(Hooks::get('TOOLBAR_CHECK_EMAIL'))) return;

        $this->_authenticate();

        $email = $this->getTrimmedInput('email', $_GET);
        if (empty($email))
        {
            $this->fatal('No e-mail address.');
        }

        echo $email;

        $candidates = new Candidates($this->_siteID);
        $candidateID = $candidates->getIDByEmail($email);
        if ($candidateID < 0)
        {
            echo ':0';
        }
        else
        {
            echo ':1';
        }

        flush();
    }
	
	private function checkEmailExists($email)
    {	
		if(strlen(trim($email))==0) {
			return false; //if no email is presented, automatically candidate doesn't exist
		} else {
			$candidates = new Candidates($this->_siteID);
			$candidateID = $candidates->getIDByEmail($email);
			if ($candidateID < 0)
				return false;
			else
				return true;
		}
    }
	
	private function writeTempFile($value, $filename) {
		$fp = fopen($filename, 'wb');
		fwrite($fp, $value);
		fclose($fp);		
	}
	
	private function storeLinkedInResume() {
		//TODO: Parse LinkedIn Resume Here
		//      Make a two-part extraction: overview area and the lower resume area
		
		/* OLD LINKEDIN CODE
		$resumeText = file_get_contents("php://input");		
        $temporaryFile = FileUtility::makeRandomTemporaryFilePath() . '.html';
        if (file_put_contents($temporaryFile, $resumeText) === false)
        {
            $this->fatal('Failed to save data for parsing.');
        }
        $documentToText = new DocumentToText();
        $documentType = $documentToText->getDocumentType($temporaryFile, 'text/html');
        $documentToText->convert($temporaryFile, $documentType);
        if ($documentToText->isError()) {
            $this->_isTextExtractionError = true;
            $this->_textExtractionError = $documentToText->getError();
            $parsedText = '';
        } else {
            $parsedText = $documentToText->getString(); 
        }
        @unlink($temporaryFile);
		*/

		// ********************************************************************************************************************
		$resumeText = file_get_contents("php://input");
        $temporaryFile = 'test.pdf';

        if (file_put_contents($temporaryFile, $resumeText) === false)
        {
            $this->fatal('Failed to save LinkedIn data for parsing.');
        }

        $documentToText = new DocumentToText();
		$documentType = $documentToText->getDocumentType($temporaryFile, 'application/pdf');
		
        $documentToText->convert($temporaryFile, $documentType);

        if ($documentToText->isError())
        {
            $this->_isTextExtractionError = true;
            $this->_textExtractionError = $documentToText->getError();
            $parsedText = '';
        }
        else
        {
            $parsedText = $documentToText->getString(); 
        }

        @unlink($temporaryFile);
		
		// ********************************************************************************************************************
		
		$parsedTextArray = explode("\n", $parsedText);
		$parsedTextArray2 = $parsedTextArray;
        $firstLine = 0;
        $lastLine = count($parsedTextArray) - 1;
		
		//get overview area -------------------------------------------------------		
		foreach ($parsedTextArray as $line => $data) {
            /* Find first line */			
			if (strpos($data, 'Current') !== false && $firstLine == 0)
                $firstLine = $line;

            /* Find last line */
			if (strpos($data, 'Share') !== false)
                $lastLine = $line - 1; //

            /* Convert bullet points into - symbols. */
            $data = str_replace('%u2022', '-', $data);
			$data = str_replace('Â', '', $data);
			$data = str_replace('â¢', '-', $data);
			$data = str_replace('â', '-', $data);
			
			/* Convert linkedIn-specific hardcoded Unneeded data */			
			$data = str_replace('&amp;', '&', $data);
			
			/* Skip unneeded Lines */
			if( (stripos($data, "see_all")===false && stripos($data, "see_less")===false) )
				$skip = false;
			else
				$skip = true;
				
			if($skip == false)
            	$parsedTextArray[$line] = $data;
			else
				$parsedTextArray[$line] = "";
        }
        $parsedTextArray = array_slice($parsedTextArray, $firstLine, $lastLine - $firstLine + 1);
        $parsedText = implode("\n", $parsedTextArray);
		
		//get lower resume area ------------------------------------------------------
		//$firstLine = $lastLine+2;
		$firstLine = 0;
        $lastLine = count($parsedTextArray2) - 1;
		foreach ($parsedTextArray2 as $line => $data) {
            /* Find first line 	*/
			if (strpos($data, 'Summary') !== false && $firstLine == 0)
                $firstLine = $line;
			elseif (strpos($data, 'Experience') !== false && $firstLine == 0)
				$firstLine = $line;
			
            /* Find last line */
			if (strpos($data, 'Contact '.$_GET['firstname'].' for:') !== false)
                $lastLine = $line - 1; //

            /* Convert bullet points into - symbols. */
            $data = str_replace('%u2022', '-', $data); //
			$data = str_replace('Ã¢', '-', $data);
			$data = str_replace('Â', '', $data);
			$data = str_replace('Â€Â', '', $data);
			
			$data = str_replace('â¢', '-', $data);
			$data = str_replace('â', '-', $data);

			
            /* Convert linkedIn-specific hardcoded Unneeded data */			
			$data = str_replace('&amp;', '&', $data);
			
			/* Skip unneeded Lines */
			if( stripos($data, "_logo]") ) //skip logo lines
				$skip = true;
			else
				$skip = false;
			
			// $skip = false;
			
			if($skip == false)
            	$parsedTextArray2[$line] = $data;
			else
				$parsedTextArray2[$line] = "";
        }
		if($firstLine > 0) {
			$parsedTextArray2 = array_slice($parsedTextArray2, $firstLine, $lastLine - $firstLine + 1);
			$parsedText .= "\n\n".implode("\n", $parsedTextArray2);
		}
		
        $storedID = $_SESSION['CATS']->storeData($parsedText);
        return $storedID;
		
	}
	
	private function storeWorkpolisResume() {
		//$resumeText = $_POST['html'];		
		$resumeText = file_get_contents("php://input");
		
        $temporaryFile = FileUtility::makeRandomTemporaryFilePath() . '.html';
        if (file_put_contents($temporaryFile, $resumeText) === false)
        {
            $this->fatal('Failed to save Workpolis data for parsing.');
        }
        $documentToText = new DocumentToText();
        $documentType = $documentToText->getDocumentType($temporaryFile, 'text/html');
        $documentToText->convert($temporaryFile, $documentType);
        if ($documentToText->isError())
        {
            $this->_isTextExtractionError = true;
            $this->_textExtractionError = $documentToText->getError();
            $parsedText = '';
        }
        else
        {
            $parsedText = $documentToText->getString(); 
        }

        @unlink($temporaryFile);
		
        /* Now, we have to determine where the resume begins and ends and cut out the
         * top and bottom of the resume...
         */

        $parsedTextArray = explode("\n", $parsedText);

        $firstLine = 0;
        $lastLine = count($parsedTextArray) - 1;

        foreach ($parsedTextArray as $line => $data)
        {
            /* Find first line */
			/*
            if ((strpos($data, 'RESUME') !== false || strpos($data, 'CV') !== false) &&
                strpos($data, '^BACK_TO_TOP') !== false &&
                $firstLine == 0)
            {
                $firstLine = $line + 1;
            }
			*/
			if (strpos($data, '***** Cover Letter *****') !== false && $firstLine == 0)
            {
                $firstLine = $line;
            }
            /* Find last line */
			if (strpos($data, 'nav_previous.gif') !== false || strpos($data, 'nav_back.gif') !== false)
            {
                $lastLine = $line - 1; //
            }
            /* TODO:  Look for more keywords present at the bottom of this page
             * in case Back_top_top goes away
             */

            /* Remove the back to top links from the resume to prevent indexing */
            if (strpos($data, '^BACK_TO_TOP') !== false)
            {
                $data = str_replace('^BACK TO TOP', '', $data);
            }

            /* Convert bullet points into - symbols. */
            $data = str_replace('%u2022', '-', $data);
			$data = str_replace('Â', '', $data);
            $parsedTextArray[$line] = $data;
        }

        $parsedTextArray = array_slice($parsedTextArray, $firstLine, $lastLine - $firstLine + 1);

        $parsedText = implode("\n", $parsedTextArray);

        /* Remember the output in the session and return to the toolbar
         * the ID number of the data.
         */		
		
        $storedID = $_SESSION['CATS']->storeData($parsedText);

        return $storedID;
	}
	
	private function storeMonsterResume() {
        //$resumeText = $_POST['html'];		
		$resumeText = file_get_contents("php://input");
		
        $temporaryFile = FileUtility::makeRandomTemporaryFilePath() . $_GET['resume_type'];
        if (file_put_contents($temporaryFile, $resumeText) === false)
        {
            $this->fatal('Failed to save Monster data for parsing.');
        }
        $documentToText = new DocumentToText();
		switch($_GET['resume_type']) {
			case "DOC": //200
				$documentType = $documentToText->getDocumentType($temporaryFile, 'application/msword');
				break;
			case "PDF": //100
				$documentType = $documentToText->getDocumentType($temporaryFile, 'application/pdf');
				break;
			case "RTF": //300
				$documentType = $documentToText->getDocumentType($temporaryFile, 'application/rtf');
				break;
			case "DOCX": //400, application/vnd.ms-word.document.12
				$documentType = $documentToText->getDocumentType($temporaryFile, 'application/vnd.ms-word.document.12');
				break;
			default: //text html
				$documentType = $documentToText->getDocumentType($temporaryFile, 'text/html');
				break;	
		}		
        
        $documentToText->convert($temporaryFile, $documentType);
        if ($documentToText->isError())
        {
            $this->_isTextExtractionError = true;
            $this->_textExtractionError = $documentToText->getError();
            $parsedText = '';
        }
        else
        {
            $parsedText = $documentToText->getString(); 
        }

        @unlink($temporaryFile);
		
		//temp -------------------------------------
		//$this->writeTempFile($resumeText,  "RESUME_DATA.".$_GET['resume_type']);
		// -----------------------------------------
		
		
        /* Now, we have to determine where the resume begins and ends and cut out the
         * top and bottom of the resume...
         */		
        $parsedTextArray = explode("\n", $parsedText);

        $firstLine = 0;
        $lastLine = count($parsedTextArray) - 1;

        foreach ($parsedTextArray as $line => $data)  {
			//first line
			if (strpos($data, 'Resume ID') !== false && $firstLine == 0)
                $firstLine = $line + 1;

            /* Find last line */
			if (strpos($data, '**** Activity ****') !== false)
                $lastLine = $line - 1;

            /* Remove the back to top links from the resume to prevent indexing */
            if (strpos($data, '^BACK_TO_TOP') !== false)
                $data = str_replace('^BACK TO TOP', '', $data);

            /* Convert bullet points into - symbols. */
            $data = str_replace('%u2022', '-', $data);
			$data = str_replace('Â', '', $data);  //Ã·
			$data = str_replace('Ã', '', $data);
			$data = str_replace('·', '-', $data);
            $parsedTextArray[$line] = $data;
        }

        $parsedTextArray = array_slice($parsedTextArray, $firstLine, $lastLine - $firstLine + 1);
        $parsedText = implode("\n", $parsedTextArray);		
		
		//if( ($lastLine - $firstLine) <= 0) {
			//add other info
			$parsedText .= "\n".$_GET["other_info"];	
		//}
		
        $storedID = $_SESSION['CATS']->storeData($parsedText);
        return $storedID;
	}
	
    private function storeMonsterResumeText()
    {
        $this->_authenticate();

        if (!isset($_POST['resumeText']))
        {
            $this->fatal('No resume.');
        }

        $resumeText = $_POST['resumeText'];

        /* The toolbar inputs the BODY of the monster page.  First, we convert
         * the HTML of the BODY into text with html2text...
         */
        $temporaryFile = FileUtility::makeRandomTemporaryFilePath() . '.html';

        if (file_put_contents($temporaryFile, $resumeText) === false)
        {
            $this->fatal('Failed to save data for parsing.');
        }

        $documentToText = new DocumentToText();

        $documentType = $documentToText->getDocumentType($temporaryFile, 'text/html');
        $documentToText->convert($temporaryFile, $documentType);

        if ($documentToText->isError())
        {
            $this->_isTextExtractionError = true;
            $this->_textExtractionError = $documentToText->getError();
            $parsedText = '';
        }
        else
        {
            $parsedText = $documentToText->getString();
        }

        @unlink($temporaryFile);

        /* Now, we have to determine where the resume begins and ends and cut out the
         * top and bottom of the resume...
         */

        $parsedTextArray = explode("\n", $parsedText);

        $firstLine = 0;
        $lastLine = count($parsedTextArray) - 1;

        foreach ($parsedTextArray as $line => $data)
        {
            /* Find first line */
            if ((strpos($data, 'RESUME') !== false || strpos($data, 'CV') !== false) &&
                strpos($data, '^BACK_TO_TOP') !== false &&
                $firstLine == 0)
            {
                $firstLine = $line + 1;
            }

            /* Find last line */
            if (strpos($data, '^BACK_TO_TOP') !== false ||
                strpos($data, 'Back_to_top') !== false ||
                strpos($data, 'Back to top') !== false)
            {
                $lastLine = $line - 1;
            }

            /* TODO:  Look for more keywords present at the bottom of this page
             * in case Back_top_top goes away
             */

            /* Remove the back to top links from the resume to prevent indexing */
            if (strpos($data, '^BACK_TO_TOP') !== false)
            {
                $data = str_replace('^BACK TO TOP', '', $data);
            }

            /* Convert bullet points into - symbols. */
            $data = str_replace('%u2022', '-', $data);

            $parsedTextArray[$line] = $data;
        }

        $parsedTextArray = array_slice($parsedTextArray, $firstLine, $lastLine - $firstLine + 1);

        $parsedText = implode("\n", $parsedTextArray);

        /* Remember the output in the session and return to the toolbar
         * the ID number of the data.
         */
        $storedID = $_SESSION['CATS']->storeData($parsedText);

        echo $storedID;

        flush();
    }

    public function getLicenseKey()
    {
        echo (defined('LICENSE_KEY') ? LICENSE_KEY : '');
    }
}

?>