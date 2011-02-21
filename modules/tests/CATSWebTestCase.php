<?php
/*
 * CATS
 * CATS WebTestCase Extension for SimpleTest
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: CATSWebTestCase.php 2452 2007-05-11 17:47:55Z brian $
 */

class CATSWebTestCase extends WebTestCase
{
    private $_indexName;
    private $_indexURL;


    public function __construct()
    {
        $this->_indexName = CATSUtility::getIndexName();
        $this->_indexURL = CATSUtility::getAbsoluteURI($this->_indexName);
        parent::__construct();
    }


    public function runPageLoadAssertions($fatalErrors = false,
        $noFatalAssertions = false)
    {
        if (!$this->assertHTTPResponseOk())
        {
            return false;
        }

        if (!$this->assertNoQueryErrors())
        {
            $this->showSource();
            return false;
        }

        if (!$this->assertNoPHPErrors())
        {
            return false;
        }

        if ($noFatalAssertions)
        {
            return true;
        }

        if (!$fatalErrors)
        {
            if (!$this->assertNoCATSFatalErrors())
            {
                $this->showSource();
                return false;
            }
        }
        else
        {
            if (!$this->assertCATSFatalErrors())
            {
                return false;
            }
        }

        return true;
    }

    public function login()
    {
        $success = $this->assertPOST(
            $this->_indexURL . '?m=login&a=attemptLogin',
            array(
                'username' => TESTER_LOGIN,
                'password' => TESTER_PASSWORD
            ),
            'Login should succeed'
        );

        if (!$success)
        {
            return false;
        }

        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        $success = $this->assertNoPattern(
            '/Invalid username/i',
            'No invalid username / password errors should occur'
        );

        /* We don't know what page we're on; get the raw HTML so we can do pre-tests. */
        $rawHTML = $this->getRawSource();

        /* Are we on an Initial Configuration Wizard page? */
        $initialConfiguration = preg_match(
            '/<title>CATS - Initial Configuration Wizard<\/title>/', $rawHTML
        );
        if ($initialConfiguration)
        {
            /* We are on the E-Mail Features Disabled page. */
            $this->assertPattern(
                '/E-Mail Disabled/i', 'At E-Mail Features Disabled page'
            );
            $this->assertClickSubmit('Continue Using CATS');
        }

        if (!$success)
        {
            return false;
        }

        return true;
    }

    public function logout()
    {
        $this->get($this->_indexURL . '?m=logout');
        $this->assertHTTPResponseOk();
    }

    public function getRawSource()
    {
        return $this->_browser->getContent();
    }

    public function addJobOrder($title, $companyID, $contactID, $type,
        $recruiter, $openings, $city, $state)
    {
        /* Add the job order. */
        $this->assertPOST(
            $this->_indexURL . '?m=joborders&a=add',
            array(
                'postback'  => 'postback',
                'title'     => $title,
                'companyID'  => $companyID,
                'contactID' => $contactID,
                'type'      => $type,
                'recruiter' => $recruiter,
                'openings'  => $openings,
                'city'      => $city,
                'state'     => $state
            ),
            'Adding test job order should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        /* Get the job order ID. */
        $matchResult = preg_match(
            '/jobOrderID=(?P<jobOrderID>\d+)/', $this->getUrl(), $matches
        );
        $this->assertTrue($matchResult, 'URL should contain jobOrderID=');

        if ($matchResult)
        {
            return $matches['jobOrderID'];
        }

        return false;
    }

    public function addCandidate($firstName, $lastName)
    {
        /* Add the candidate. */
        $this->assertPOST(
            $this->_indexURL . '?m=candidates&a=add',
            array(
                'postback'  => 'postback',
                'firstName' => $firstName,
                'lastName'  => $lastName
            ),
            'Adding test candidate should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        /* Get the candidate ID. */
        $matchResult = preg_match(
            '/candidateID=(?P<candidateID>\d+)/', $this->getUrl(), $matches
        );
        $this->assertTrue($matchResult, 'URL should contain candidateID=');

        if ($matchResult)
        {
            return $matches['candidateID'];
        }

        return false;
    }

    public function addCompany($name, $city = false, $state = false,
        $zip = false, $departmentsCSV = false)
    {
        /* Build POST data. */
        $POSTData = array(
            'postback' => 'postback',
            'name'     => $name
        );
        if ($city !== false)
        {
            $POSTData['city'] = $city;
        }
        if ($state !== false)
        {
            $POSTData['state'] = $state;
        }
        if ($zip !== false)
        {
            $POSTData['zip'] = $zip;
        }
        if ($departmentsCSV !== false)
        {
            $POSTData['departmentsCSV'] = $departmentsCSV;
        }

        /* Add the company. */
        $this->assertPOST(
            $this->_indexURL . '?m=companies&a=add',
            $POSTData,
            'Adding test company should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        /* Get the company ID. */
        $matchResult = preg_match(
            '/companyID=(?P<companyID>\d+)/', $this->getUrl(), $matches
        );
        $this->assertTrue($matchResult, 'URL should contain companyID=');

        if ($matchResult)
        {
            return $matches['companyID'];
        }

        return false;
    }

    public function addContact($firstName, $lastName, $companyID, $title)
    {
        /* Add the contact. */
        $this->assertPOST(
            $this->_indexURL . '?m=contacts&a=add',
            array(
                'postback'  => 'postback',
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'companyID'  => $companyID,
                'title'     => $title
            ),
            'Adding test contact should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        /* Get the contact ID. */
        $matchResult = preg_match(
            '/contactID=(?P<contactID>\d+)/', $this->getUrl(), $matches
        );
        $this->assertTrue($matchResult, 'URL should contain contactID=');

        if ($matchResult)
        {
            return $matches['contactID'];
        }

        return false;
    }

    public function addPipelineActivity($candidateID, $jobOrderID,
        $activityTypeID, $activityNote)
    {
        /* Add the candidate. */
        $this->assertPOST(
            $this->_indexURL . '?m=candidates&a=addActivityChangeStatus',
            array(
                'postback'       => 'postback',
                'addActivity'    => 'on',
                'candidateID'    => $candidateID,
                'regardingID'    => $jobOrderID,
                'activityTypeID' => $activityTypeID,
                'activityNote'   => $activityNote
            ),
            'Adding pipeline activity note should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        $this->assertPattern(
            '/An activity entry of type <span class="bold">[^<]+<\/span> has been added/'
        );

        return true;
    }

    public function addUser($firstName, $lastName, $username, $accessLevel,
                            $password)
    {
        /* Add the user. */
        $this->assertPOST(
            $this->_indexURL . '?m=settings&a=addUser',
            array(
                'postback'       => 'postback',
                'firstName'      => $firstName,
                'lastName'       => $lastName,
                'username'       => $username,
                'accessLevel'    => $accessLevel,
                'password'       => $password,
                'retypePassword' => $password
            ),
            'Adding test user should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }

        $doesNotExist = $this->assertNoPattern(
            '/The specified username already exists./',
            sprintf('User \'%s\' should not exist', $username)
        );
        if (!$doesNotExist)
        {
            return false;
        }

        /* Get the user ID. */
        $matchResult = preg_match(
            '/userID=(?P<userID>\d+)/', $this->getUrl(), $matches
        );
        $this->assertTrue(
            $matchResult, 'URL should contain userID='
        );

        if (!$matchResult)
        {
            return false;
        }

        return $matches['userID'];
    }

    public function deleteJobOrder($jobOrderID)
    {
        $this->assertGET(
            $this->_indexURL . '?m=joborders&a=delete&jobOrderID=' . $jobOrderID,
            false,
            'Deleting job order [' . $jobOrderID . '] should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }
    }

    public function deleteCandidate($candidateID)
    {
        $this->assertGET(
            $this->_indexURL . '?m=candidates&a=delete&candidateID=' . $candidateID,
            false,
            'Deleting candidate [' . $candidateID . '] should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }
    }

    public function deleteCompany($companyID)
    {
        $this->assertGET(
            $this->_indexURL . '?m=companies&a=delete&companyID=' . $companyID,
            false,
            'Deleting company [' . $companyID . '] should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }
    }

    public function deleteContact($contactID)
    {
        $this->assertGET(
            $this->_indexURL . '?m=contacts&a=delete&contactID=' . $contactID,
            false,
            'Deleting contact [' . $contactID . '] should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }
    }

    public function deleteUser($userID)
    {
        $this->assertGET(
            $this->_indexURL . '?m=settings&a=deleteUser&iAmTheAutomatedTester=1&userID=' . $userID,
            false,
            'Deleting user [' . $userID . '] should succeed'
        );
        if (!$this->runPageLoadAssertions(false))
        {
            return false;
        }
    }

    public function assertEqual($first, $second, $message = "%s")
    {
        return $this->assert(
            new EqualExpectation($first),
            $second,
            $message
        );
    }

    public function assertNotEqual($first, $second, $message = "%s")
    {
        return $this->assert(
            new NotEqualExpectation($first),
            $second,
            $message
        );
    }

    public function assertIdentical($first, $second, $message = "%s")
    {
        return $this->assert(
            new IdenticalExpectation($first),
            $second,
            $message
        );
    }

    public function assertNotIdentical($first, $second, $message = "%s")
    {
        return $this->assert(
            new NotIdenticalExpectation($first),
            $second,
            $message
        );
    }

    public function assertPatternIn($pattern, $subject, $message = "%s")
    {
        return $this->assert(
            new PatternExpectation($pattern),
            $subject,
            $message
        );
    }

    public function assertNoPatternIn($pattern, $subject, $message = "%s")
    {
        return $this->assert(
            new NoPatternExpectation($pattern),
            $subject,
            $message
        );
    }

    public function assertClickLink($label, $index = 0, $message = '%s')
    {
        $message = sprintf(
            $message, "Clicking link [$label ($index)] should succeed"
        );
        return $this->assertTrue(
            $this->clickLink($label, $index),
            $message
        );
    }

    public function assertClickLinkById($id, $message = '%s')
    {
        $message = sprintf(
            $message, "Clicking link of ID [$id] should succeed"
        );
        return $this->assertTrue(
            $this->clickLinkById($id),
            $message
        );
    }

    public function assertClickSubmit($label = 'Submit',
                                      $additional = false, $message = '%s')
    {
        $message = sprintf(
            $message, "Clicking submit [$label] should succeed"
        );
        return $this->assertTrue(
            $this->clickSubmit($label, $additional),
            $message
        );
    }

    public function assertClickSubmitById($id, $additional = false,
                                          $message = '%s')
    {
        $message = sprintf(
            $message, "Clicking submit of ID [$id] should succeed"
        );
        return $this->assertTrue(
            $this->clickSubmitById($id, $additional),
            $message
        );
    }

    public function assertNoQueryErrors($message = '%s')
    {
        $message = sprintf(
            $message, "No query errors should occur -- %s"
        );
        return $this->assertNoPattern(
            '/Query Error -- Report to System Administrator ASAP/i',
            $message
        );
    }

    public function assertNoPHPErrors($message = '%s')
    {
        $message = sprintf(
            $message, "No PHP errors should occur -- %s"
        );
        return $this->assertNoPattern(
            '/<\/b> on line <[b]>/i',
            $message
        );
    }

    public function assertHTTPResponseOk($message = '%s')
    {
        $message = sprintf(
            $message, "HTTP response code should be [200]"
        );
        return $this->assertResponse(
            HTTP_OK, $message
        );
    }

    public function assertCATSFatalErrors($message = '%s')
    {
        $message = sprintf(
            $message, "CATS fatal errors should occur -- %s"
        );
        return $this->assertPattern(
            '/A fatal error has occurred./i',
            $message
        );
    }

    public function assertNoCATSFatalErrors($message = '%s')
    {
        $message = sprintf(
            $message, "No CATS fatal errors should occur -- %s"
        );
        return $this->assertNoPattern(
            '/A fatal error has occurred./i',
            $message
        );
    }

    public function assertDateInputExists($name, $required, $dateFormat,
                                          $defaultDate = false)
    {
        if ($defaultDate === false)
        {
            $pattern = sprintf(
                "/DateInput\('%s', %s, '%s', '', (:?-1|\d+)\);/",
                $name,
                $required,
                $dateFormat
            );
        }
        else if ($defaultDate === true)
        {
            $pattern = sprintf(
                "/DateInput\('%s', %s, '%s', '\d{2}-\d{2}-\d{2}', (:?-1|\d+)\);/",
                $name,
                $required,
                $dateFormat
            );
        }
        else
        {
            $pattern = sprintf(
                "/DateInput\('%s', %s, '%s', '%s', (:?-1|\d+)\);/",
                $name,
                $required,
                $dateFormat,
                $defaultDate
            );
        }

        return $this->assertPattern($pattern);
    }

    public function assertPOST($url, $parameters = false, $message = '%s')
    {
        $message = sprintf(
            $message, "POST should succeed"
        );
        return $this->assertTrue(
            $this->post($url, $parameters),
            $message
        );
    }

    public function assertGET($url, $parameters = false, $message = '%s')
    {
        $message = sprintf(
            $message, "GET should succeed"
        );
        return $this->assertTrue(
            $this->get($url, $parameters),
            $message
        );
    }
}

?>
