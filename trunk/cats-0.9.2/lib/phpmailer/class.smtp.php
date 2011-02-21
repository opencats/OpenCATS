<?php
/*
 * CATS
 * Drop-In Replacement for PHPMailer SMTP Library
 *
 * Based on "SMTP - PHP SMTP class" version 1.03 by Chris Ryan from the
 * PHPMailer package. Rewritten almost entirely by Cognizo Technologies, Inc.,
 * adding additional support for SMTP AUTH, as well as general robustness
 * fixes, error handling, removal of obsolete / unused SMTP features, etc.
 *
 * Modifications made by Cognizo Technologies, Inc. Copyright (C) 2005 - 2007
 * Cognizo Technologies, Inc.
 *
 * You may use this code under either the GNU Lesser General Public License
 * or the CATS Public License, Version 1.1a. You may obtain a copy of the CATS
 * Public License at http://www.catsone.com/.
 *
 *
 * $Id: class.smtp.php 2101 2007-03-06 00:20:17Z brian $
 */

class SMTP
{
    /* Default SMTP port. */
    const SMTP_PORT = 25;

    /* Debug level. This is used externally by PHPMailer; don't change name. */
    public $do_debug = 0;

    private $socket = false;      /* SMTP server socket. */
    private $HELOReply = array(); /* Lines from the EHLO / HELO reply. */
    private $error = array();     /* Errors. */


    /**
     * Connect to the server specified on the specified port. If a port number
     * is not specified, the default will be used. If a timeout is specified,
     * it will be used instead of the default of 30 seconds.
     *
     * @param string SMTP server hostname / IP
     * @param integer SMTP server port
     * @param integer timeout in seconds
     * @access public
     * @return boolean success
     */
    public function Connect($host, $port = 0, $timeout = 30)
    {
        $this->clearError();

        /* Make sure we aren't already connected. */
        if ($this->connected())
        {
            $this->setError('Already connected to a server.');
            $this->debugError(1);
            return false;
        }

        /* If port number is empty or not an integer, use default. */
        $port = (int) $port;
        if ($port == 0)
        {
            $this->debugText(
                1,
                'Warning: The specified port number is invalid; using default.'
            );
            $port = self::SMTP_PORT;
        }

        /* Open a socket to the SMTP server. Note that the timeout passed to
         * fsockopen() is only for OPENING the connection. Read / write timeout
         * is set below.
         */
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        /* Verify that the connection has been successfully established. */
        if (!$this->socket)
        {
            $this->setError(sprintf(
                'Failed to connect to server (%s - %s).', $errno, $errstr
            ));
            $this->debugError(1);
            return false;
        }

        /* Set the timeout for read / write operations. */
        stream_set_timeout($this->socket, $timeout);

        /* Get any 'announcement' data (sent by the server before we even send
         * a HELO / EHLO).
         */
        $announcementText = $this->getLines();
        $this->debugText(2, 'FROM SERVER:' . "\r\n" . $announcementText);

        /* Set a much lower timeout for read / write operations. */
        //socket_set_timeout($this->socket, 0, 100000);

        return true;
    }

    /**
     * Returns true if connected to the server; false otherwise.
     *
     * @return boolean connected
     * @access public
     */
    public function Connected()
    {
        if (!$this->socket)
        {
            return false;
        }

        $socketStatus = socket_get_status($this->socket);
        if ($socketStatus['eof'])
        {
            /* Socket is still valid, but we received an EOF and have been
             * disconnected.
             */
            $this->debugText(
                1, 'NOTICE: EOF caught while checking if connected.'
            );
            $this->Close();
            return false;
        }

        /* We are connected. */
        return true;
    }

    /**
     * Sends the HELO or EHLO command to the server. This identifies us to the
     * server, and the server responds by identifying itself.
     *
     * @param string our local hostname
     * @access public
     * @return boolean success
     */
    public function Hello($host = '')
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Hello() without being connected.');
            $this->debugError(1);
            return false;
        }

        /* If no hostname was specified, use 'localhost'. */
        if (empty($host))
        {
            //FIXME: Try to determine local hostname.
            $host = 'localhost';
        }

        /* First, try sending an EHLO (Enhanced SMTP) to the server. We will
         * fall back to HELO (SMTP) if the server does not accept EHLO.
         */
        fputs($this->socket, sprintf('EHLO %s%s', $host, "\r\n"));

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, 'FROM SERVER:' . "\r\n" . $reply);

        /* We should receive a "250 OK" response from the server, but if the
         * server does not support EHLO, we will not, and we must fall back
         * to HELO.
         */
        $code = $this->getCode($reply);
        if ($code != 250)
        {
            $this->debugText(
                2,
                sprintf(
                    'NOTICE: EHLO not accepted from server (code %s). [%s]',
                    $code,
                    substr($reply, 4)
                )
            );
            $this->debugError(2);

            /* Fall back and try sending HELO to the server. */
            fputs($this->socket, sprintf('HELO %s%s', $host, "\r\n"));

            /* Get the server's response. */
            $reply = $this->getLines();
            $this->debugText(2, 'FROM SERVER:' . "\r\n" . $reply);

            /* We should receive a "250 OK" response from the server. If we
             * don't, this is one messed up mail server.
             */
            $code = $this->getCode($reply);
            if ($code != 250)
            {
                $this->setError(
                    'EHLO and HELO not accepted from server.',
                    $code,
                    substr($reply, 4)
                );
                $this->debugError(2);
                return false;
            }
        }

        /* Normalize newlines in reply for easy explode()ing. */
        $reply = str_replace("\r\n", "\n", $reply);
        $reply = str_replace("\r",   "\n", $reply);

        /* Store the hello reply as an array of lines. */
        $this->HELOReply = explode("\n", $reply);

        return true;
    }

    /**
     * Performs SMTP authentication. This must be called after Hello(). See
     * RFC 2554.
     *
     * @param string SMTP username
     * @param string SMTP password
     * @access public
     * @return boolean success
     */
    public function Authenticate($username, $password)
    {
        /* Get an array of supported SMTP AUTH types. */
        $support = $this->getSupportedAUTHTypes();

        /* Is AUTH supported at all? */
        if (!$support['AUTH'])
        {
            $this->setError('This server does not support SMTP AUTH.');
            $this->debugError(1);
            return false;
        }

        /* Try PLAIN first; it seems to work better. */
        if ($support['PLAIN'])
        {
            /* Try to AUTH using PLAIN mode. */
            if ($this->authenticatePLAIN($username, $password))
            {
                return true;
            }

            /* Failed; lets try LOGIN mode just to be safe. */
            if ($support['LOGIN'] &&
                $this->authenticateLOGIN($username, $password))
            {
                return true;
            }

            /* Failed; abort. */
            $this->setError('All SMTP authentication methods failed.');
            $this->debugError(1);
        }
        else if ($support['LOGIN'])
        {
            /* Try to AUTH using LOGIN mode. */
            if ($this->authenticateLOGIN($username, $password))
            {
                return true;
            }

            /* Failed; abort. */
            $this->setError('All SMTP authentication methods failed.');
            $this->debugError(1);
        }
        else
        {
            /* The server doesn't support any types we know how to work with. */
            $this->setError(
                'This server supports SMTP AUTH but has no supported AUTH types.'
            );
            $this->debugError(1);
        }

        return false;
    }

    /**
     * Starts a mail transaction from the e-mail address specified in $from.
     * If successful, Recipient() can be called next.
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE SUCCESS: 552, 451, 452
     * SMTP CODE SUCCESS: 500, 501, 421
     *
     * @access public
     * @return boolean success
     */
    public function Mail($from)
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Mail() without being connected.');
            $this->debugError(1);
            return false;
        }

        /* Send MAIL FROM to the server. */
        fputs($this->socket, sprintf('MAIL FROM:<%s>%s', $from, "\r\n"));

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, "FROM SERVER:\r\n" . $reply);

        /* We should get a "250 Sender OK" from the server. */
        $code = $this->getCode($reply);
        if ($code != 250)
        {
            $this->setError(
                'MAIL not accepted from server.', $code, substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        return true;
    }

    /**
     * Sends the command RCPT TO to the SMTP server with an argument of $to.
     * Returns true if the recipient was accepted; false if it was rejected.
     * If successful, Data() can be called next.
     *
     * SMTP CODE SUCCESS: 250, 251
     * SMTP CODE FAILURE: 550, 551, 552, 553, 450, 451, 452
     * SMTP CODE ERROR  : 500, 501, 503, 421
     *
     * @access public
     * @return boolean success
     */
    public function Recipient($to)
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Recipient() without being connected.');
            $this->debugError(1);
            return false;
        }

        /* Send RCPT TO to the server. */
        fputs($this->socket, sprintf('RCPT TO:<%s>%s', $to, "\r\n"));

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, "FROM SERVER:\r\n" . $reply);

        /* We should get a "250 Recipient OK" or a "251" from the server. */
        $code = $this->getCode($reply);
        if ($code != 250 && $code != 251)
        {
            $this->setError(
                'RCPT TO not accepted from server.', $code, substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        return true;
    }

    /**
     * Sends a DATA command and sends message data to the server,
     * finializing the mail transaction. Each header needs to be on a single
     * line, terminated by a CR/LF, with the message headers and the message
     * body being seperated by and additional CR/LF.
     *
     * SMTP CODE INTERMEDIATE: 354
     *     [data]
     *     <CRLF>.<CRLF>
     *     SMTP CODE SUCCESS: 250
     *     SMTP CODE FAILURE: 552, 554, 451, 452
     * SMTP CODE FAILURE: 451, 554
     * SMTP CODE ERROR  : 500, 501, 503, 421
     *
     * @param string message data
     * @access public
     * @return boolean success
     */
    public function Data($messageData)
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Data() without being connected.');
            return false;
        }

        /* Send DATA to the server. */
        fputs($this->socket, "DATA\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, "FROM SERVER:\r\n" . $reply);

        /* We should get a "354 Ok Send data ending with <CRLF>.<CRLF>" from
         * the server.
         */
        $code = $this->getCode($reply);
        if ($code != 354)
        {
            $this->setError(
                'DATA not accepted from server.', $code, substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        /* The server is ready to accept data. We cannot send more than 1000
         * characters per line, including CR/LF, so we will break up the data
         * into smaller lines if needed. We will also prepend any line
         * beginning with a period ('.') with another period, as per the RFC:
         *
         * Without some provision for data transparency the character
         * sequence "<CRLF>.<CRLF>" ends the mail text and cannot be sent
         * by the user.  In general, users are not aware of such
         * "forbidden" sequences.  To allow all user composed text to be
         * transmitted transparently the following procedures are used.
         *
         *    1. Before sending a line of mail text the sender-SMTP checks
         *    the first character of the line.  If it is a period, one
         *    additional period is inserted at the beginning of the line.
         *
         *    2. When a line of mail text is received by the receiver-SMTP
         *    it checks the line.  If the line is composed of a single
         *    period it is the end of mail.  If the first character is a
         *    period and there are other characters on the line, the first
         *    character is deleted.
         */

        /* Normalize message line breaks so that we can explode() into an
         * array of lines.
         */
        $messageData = str_replace("\r\n", "\n", $messageData);
        $messageData = str_replace("\r",   "\n", $messageData);

        $lines = explode("\n", $messageData);

        /* We need to determine if headers are contained within the message
         * data passed to this function. To do this, we first see if the first
         * line contains a colon (':'). If it doesn't, no headers are contained
         * in the message data. Also, if there is a space in the line anywhere
         * before the first colon, we can assume it is not a header line.
         *
         * If we find a header on the first line, we can parse every line up to
         * the first blank line as headers.
         */
        $colonPosition = strpos($lines[0], ':');
        if ($colonPosition === false)
        {
            $containsHeaders = false;
        }
        else
        {
            $field = substr($lines[0], 0, $colonPosition);
            if (!empty($field) && strpos($field, ' ') === false)
            {
                $containsHeaders = true;
            }
            else
            {
                $containsHeaders = false;
            }
        }

        /* Maximum length of lines sent to the server. */
        $maxLineLength = 998;

        foreach ($lines as $line)
        {
            /* If we've found an empty line and our data contains headers,
             * we can assume that this line marks the end of the headers. We
             * then prevent further lines from being parsed as headers by
             * disabling "contains headers" mode.
             */
            if (empty($line) && $containsHeaders)
            {
                $containsHeaders = false;
            }

            /* If required, break long lines into shorter ones. */
            $outputLines = null;
            while (strlen($line) > $maxLineLength)
            {
                /* Find the position of the last space in the first
                 * $maxLineLength characters of the line.
                 */
                $spacePosition = strrpos(substr($line, 0, $maxLineLength), ' ');
                if (!$spacePosition)
                {
                    $spacePosition = $maxLineLength - 1;
                }

                /* Append the first part of the line (before the space) to the
                 * output lines array and remove it from the original line.
                 */
                $outputLines[] = substr($line, 0, $spacePosition);
                $line = substr($line, $spacePosition + 1);

                /* Prepend a tab to wrapped header lines. */
                if ($containsHeaders)
                {
                    $line = "\t" . $line;
                }
            }

            /* Append whatever is left over from wrapping (or the whole line,
             * if it was shorter than the maximum line length) to the output
             * lines array.
             */
            $outputLines[] = $line;

            /* Send output lines to the server. */
            foreach ($outputLines as $outputLine)
            {
                /* Prepend another period ('.') to any line that begins with
                 * a period, per the RFC.
                 */
                if (strlen($outputLine) > 0 && $outputLine[0] == '.')
                {
                    $outputLine = '.' . $outputLine;
                }

                /* Send the line to the server. */
                fputs($this->socket, $outputLine . "\r\n");
            }
        }

        /* All data from the message has been sent. Terminate the DATA command
         * by sending <CRLF>.<CRLF>.
         */
        fputs($this->socket, "\r\n.\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, 'FROM SERVER:' . "\r\n" . $reply);

        /* We should get a "250 Message received" from the server. */
        $code = $this->getCode($reply);
        if ($code != 250)
        {
            $this->setError(
                'DATA not accepted from server.', $code, substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        return true;
    }

    /**
     * Sends a NOOP command to the SMTP server. This is mainly used for keeping
     * a connection alive.
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 421
     *
     * @access public
     * @return boolean success
     */
    public function Noop()
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Noop() without being connected.');
            $this->debugError(1);
            return false;
        }

        /* Send NOOP to the server. */
        fputs($this->socket, "NOOP\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, "FROM SERVER:\r\n" . $reply);

        /* We should get a "250 Ok" from the server. */
        $code = $this->getCode($reply);
        if ($code != 250)
        {
            $this->setError(
                'NOOP not accepted from server.', $code, substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        return true;
    }

    /**
     * Sends an SMTP REST command to abort the transaction that is currently
     * in progress.
     *
     * SMTP CODE SUCCESS: 250
     * SMTP CODE ERROR  : 500, 501, 504, 421
     *
     * @access public
     * @return boolean success
     */
    public function Reset()
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Reset() without being connected.');
            return false;
        }

        /* Send RSET to the server. */
        fputs($this->socket, 'RSET' . "\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, 'FROM SERVER:' . "\r\n" . $reply);

        /* We should get a '250 OK' response from the server. */
        $code = $this->getCode($reply);
        if ($code != 250)
        {
            $this->setError('RSET failed.', $code, substr($reply, 4));
            $this->debugError(1);
            return false;
        }

        return true;
    }

    /**
     * Sends the QUIT command to the server and then closes the socket.
     * If an error occurrs and $closeOnError is false, the connection will
     * not be closed.
     *
     * SMTP CODE SUCCESS: 221
     * SMTP CODE ERROR  : 500
     *
     * @param boolean close socket even if errors occur
     * @access public
     * @return boolean success
     */
    public function Quit($closeOnError = true)
    {
        $this->clearError();

        /* Make sure we are (still) connected. */
        if (!$this->connected())
        {
            $this->setError('Called Quit() without being connected.');
            $this->debugError(1);
            return false;
        }

        /* Send QUIT to the server. */
        fputs($this->socket, "QUIT\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();
        $this->debugText(2, "FROM SERVER:\r\n" . $reply);

        /* Return value. */
        $return = true;

        /* We should get a "221" from the server. */
        $code = $this->getCode($reply);
        if ($code != 221)
        {
            $this->setError(
                'QUIT not accepted from server.', $code, substr($reply, 4)
            );
            $this->debugError(1);
            $return = false;
        }

        /* Can we close the socket? */
        if ($return || $closeOnError)
        {
            $this->Close();
        }

        return $return;
    }

    /**
     * Closes the socket to the server and cleans up. This should only be used
     * directly as a last resort when Quit() cannot be used.
     *
     * @access public
     * @return void
     */
    public function Close()
    {
        $this->clearError();
        $this->HELOReply = array();

        if ($this->socket)
        {
            fclose($this->socket);
            $this->socket = false;
        }
    }



    private function getSupportedAUTHTypes()
    {
        /* SMTP AUTH types. */
        $support = array(
            'AUTH'       => false,
            'PLAIN'      => false,
            'LOGIN'      => false,
            'CRAM-MD5'   => false,
            'DIGEST-MD5' => false,
            'GSSAPI'     => false,
            'NTLM'       => false
        );

        /* Figure out what AUTH types are supported. */
        foreach ($this->HELOReply as $line)
        {
            if (strpos($line, 'AUTH') === false)
            {
                continue;
            }

            /* SMTP AUTH itself is supported. */
            $support['AUTH'] = true;

            $support['PLAIN']      = (strpos($line, 'PLAIN') !== false);
            $support['LOGIN']      = (strpos($line, 'LOGIN') !== false);
            $support['CRAM-MD5']   = (strpos($line, 'CRAM-MD5') !== false);
            $support['DIGEST-MD5'] = (strpos($line, 'DIGEST-MD5') !== false);
            $support['GSSAPI']     = (strpos($line, 'GSSAPI') !== false);
            $support['NTLM']       = (strpos($line, 'NTLM') !== false);

            break;
        }

        return $support;
    }

    private function authenticatePLAIN($username, $password)
    {
        /* AUTH PLAIN requires a NUL byte (\000), followed by the username,
         * followed by another NUL byte, followed by the password, all on one
         * line. This entire string must be base64 encoded and then appended
         * with a CR/LF.
         */
        $loginString = base64_encode(
            sprintf("\000%s\000%s", $username, $password)
        );

        /* Send AUTH command to the server. */
        fputs(
            $this->socket, sprintf('AUTH PLAIN %s%s', $loginString, "\r\n")
        );

        /* Get the server's response. */
        $reply = $this->getLines();

        /* We should get a "235 Authentication Successful" from the server. */
        $code = $this->getCode($reply);
        if ($code != 235)
        {
            $this->setError(
                'AUTH PLAIN not accepted from server.',
                $code,
                substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        return true;
    }

    private function authenticateLOGIN($username, $password)
    {
        /* Send AUTH command to the server. */
        fputs($this->socket, sprintf('AUTH LOGIN%s', "\r\n"));

        /* Get the server's response. */
        $reply = $this->getLines();

        /* We should get a "334 VXNlcm5hbWU6" from the server. */
        $code = $this->getCode($reply);
        if ($code != 334)
        {
            $this->setError(
                'AUTH LOGIN not accepted from server.',
                $code,
                substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        /* Send base64-encoded username to the server. */
        fputs($this->socket, base64_encode($username) . "\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();

        /* We should get a "334 UGFzc3dvcmQ6" from the server. */
        $code = $this->getCode($reply);
        if ($code != 334)
        {
            $this->setError(
                'AUTH LOGIN Username not accepted from server.',
                $code,
                substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        /* Send base64-encoded password to the server. */
        fputs($this->socket, base64_encode($password) . "\r\n");

        /* Get the server's response. */
        $reply = $this->getLines();

        /* We should get a "235 Authentication Successful" from the server. */
        $code = $this->getCode($reply);
        if ($code != 235)
        {
            $this->setError(
                'AUTH LOGIN Password not accepted from server.',
                $code,
                substr($reply, 4)
            );
            $this->debugError(1);
            return false;
        }

        return true;
    }

    /**
     * Read from the server socket until we get an EOF / Timeout. We can also
     * stop reading when we  receive a line in which the 4th character is a
     * space. Data from the server is returned as a string.
     *
     * @access private
     * @return string server response data
     */
    private function getLines()
    {
        $data = '';

        while (($string = fgets($this->socket, 515)))
        {
            $data .= $string;

            /* If the 4th character in the string is a space, we don't need to
             * read any more data. We only need to keep reading when we receive
             * lines where the 4th character is a '-'.
             */
            if ($string[3] == ' ')
            {
                break;
            }
        }

        return $data;
    }

    private function setError($eerrorMessage, $SMTPCode = -1, $SMTPMessage = '')
    {
        $this->error = array(
            'error'       => $eerrorMessage,
            'SMTPCode'    => $SMTPCode,
            'SMTPMessage' => $SMTPMessage
        );
    }

    /**
     * Clears / resets error data.
     *
     * @return void
     * @access private
     */
    private function clearError()
    {
        $this->error = array();
    }

    /**
     * Returns the first 3 characters of a string, converted to an integer.
     * This is used for parsing the SMTP response code from a server reply.
     *
     * @param string SMTP reply
     * @return integer SMTP response code
     * @access private
     */
    private function getCode($reply)
    {
        return (int) substr($reply, 0, 3);
    }

    private function debugError($level)
    {
        if ($this->do_debug < $level)
        {
            return;
        }

        echo sprintf(
            'SMTP -> DEBUG (%s): %s [%s]%s',
            $this->error['SMTPCode'],
            $this->error['error'],
            $this->error['SMTPMessage'],
            "\r\n"
        );
    }

    private function debugText($level, $text)
    {
        if ($this->do_debug < $level)
        {
            return;
        }

        echo sprintf('SMTP -> DEBUG: %s%s', $text, "\r\n");
    }
}


 ?>
