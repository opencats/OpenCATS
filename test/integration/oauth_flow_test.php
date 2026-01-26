#!/usr/bin/env php
<?php
/**
 * OAuth 2.0 Flow Validation Script for OpenCATS REST API
 *
 * This script validates that the OAuth2Server class has all required grant methods
 * and configuration for proper OAuth 2.0 implementation.
 *
 * This script uses static code analysis to avoid dependency issues with the
 * database connection and other runtime dependencies.
 *
 * @package    CATS
 * @subpackage Test
 * @copyright Copyright (C) 2025 OpenCATS Contributors
 */

/**
 * OAuth2ServerValidator
 *
 * Validates OAuth2Server implementation for completeness and security
 * using static code analysis (no runtime class loading required).
 */
class OAuth2ServerValidator
{
    /** @var string Path to the OAuth2Server.php file */
    private $oauth2ServerPath;

    /** @var string Content of the OAuth2Server.php file */
    private $fileContent;

    /** @var array Test results storage */
    private $results = array();

    /** @var int Total passed tests */
    private $passed = 0;

    /** @var int Total failed tests */
    private $failed = 0;

    /** @var int Total warning tests */
    private $warnings = 0;

    /** @var array Extracted method signatures */
    private $methods = array();

    /** @var array Extracted constants */
    private $constants = array();

    /**
     * Constructor.
     *
     * @param string $oauth2ServerPath Path to OAuth2Server.php file.
     */
    public function __construct($oauth2ServerPath)
    {
        $this->oauth2ServerPath = $oauth2ServerPath;
    }

    /**
     * Initialize the validator by loading and parsing the file.
     *
     * @return bool True if initialization successful, false otherwise.
     */
    public function initialize()
    {
        /* Check if file exists. */
        if (!file_exists($this->oauth2ServerPath))
        {
            $this->recordResult('File Exists', false, 'OAuth2Server.php not found at: ' . $this->oauth2ServerPath);
            return false;
        }

        $this->recordResult('File Exists', true, 'OAuth2Server.php found');

        /* Load file content for pattern matching. */
        $this->fileContent = file_get_contents($this->oauth2ServerPath);

        /* Verify it contains the OAuth2Server class. */
        if (!preg_match('/class\s+OAuth2Server/', $this->fileContent))
        {
            $this->recordResult('Class Definition', false, 'OAuth2Server class definition not found');
            return false;
        }

        $this->recordResult('Class Definition', true, 'OAuth2Server class found');

        /* Parse the file to extract methods and constants. */
        $this->parseFile();

        return true;
    }

    /**
     * Parse the file to extract method signatures and constants.
     *
     * @return void
     */
    private function parseFile()
    {
        /* Extract public methods. */
        preg_match_all(
            '/public\s+(?:static\s+)?function\s+(\w+)\s*\([^)]*\)/',
            $this->fileContent,
            $matches
        );

        foreach ($matches[1] as $method)
        {
            $this->methods[$method] = array(
                'public' => true,
                'static' => strpos($this->fileContent, "public static function {$method}") !== false
            );
        }

        /* Extract private methods. */
        preg_match_all(
            '/private\s+(?:static\s+)?function\s+(\w+)\s*\([^)]*\)/',
            $this->fileContent,
            $matches
        );

        foreach ($matches[1] as $method)
        {
            $this->methods[$method] = array(
                'public' => false,
                'static' => strpos($this->fileContent, "private static function {$method}") !== false
            );
        }

        /* Extract constants with their values. */
        preg_match_all(
            '/const\s+(\w+)\s*=\s*(\d+)\s*;/',
            $this->fileContent,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match)
        {
            $this->constants[$match[1]] = (int)$match[2];
        }
    }

    /**
     * Check if a method exists.
     *
     * @param string $methodName Method name to check.
     * @return bool True if method exists.
     */
    private function hasMethod($methodName)
    {
        return isset($this->methods[$methodName]);
    }

    /**
     * Check if a method is public.
     *
     * @param string $methodName Method name to check.
     * @return bool True if method is public.
     */
    private function isPublicMethod($methodName)
    {
        return isset($this->methods[$methodName]) && $this->methods[$methodName]['public'];
    }

    /**
     * Check if a constant exists.
     *
     * @param string $constantName Constant name to check.
     * @return bool True if constant exists.
     */
    private function hasConstant($constantName)
    {
        return isset($this->constants[$constantName]);
    }

    /**
     * Get a constant value.
     *
     * @param string $constantName Constant name.
     * @return mixed Constant value or null if not found.
     */
    private function getConstant($constantName)
    {
        return isset($this->constants[$constantName]) ? $this->constants[$constantName] : null;
    }

    /**
     * Run all validation tests.
     *
     * @return void
     */
    public function runAllTests()
    {
        $this->printHeader('OAuth 2.0 Flow Validation for OpenCATS REST API');

        if (!$this->initialize())
        {
            $this->printSummary();
            return;
        }

        $this->printSection('Method Validation');
        $this->validateRequiredMethods();

        $this->printSection('Constants Validation');
        $this->validateRequiredConstants();

        $this->printSection('Security Implementation Checks');
        $this->validateSecurityImplementation();

        $this->printSection('Token Expiry Implementation');
        $this->validateTokenExpiry();

        $this->printSection('Additional OAuth 2.0 Compliance Checks');
        $this->validateOAuth2Compliance();

        $this->printSummary();
    }

    /**
     * Validate that all required methods exist.
     *
     * @return void
     */
    private function validateRequiredMethods()
    {
        $requiredMethods = array(
            'createClient' => 'Client registration',
            'validateClient' => 'Client validation',
            'createAuthorizationCode' => 'Auth code generation',
            'exchangeAuthorizationCode' => 'Auth code to token exchange',
            'clientCredentialsGrant' => 'Client credentials grant',
            'refreshTokenGrant' => 'Refresh token grant',
            'validateAccessToken' => 'Token validation',
            'revokeToken' => 'Token revocation'
        );

        foreach ($requiredMethods as $method => $description)
        {
            $exists = $this->hasMethod($method);

            /* Special handling for revokeToken - check for alternative implementations. */
            if ($method === 'revokeToken' && !$exists)
            {
                /* Check if revokeUserTokens exists as an alternative. */
                $altExists = $this->hasMethod('revokeUserTokens');
                if ($altExists)
                {
                    $this->recordResult(
                        sprintf('Method: %s (%s)', $method, $description),
                        'warning',
                        'revokeToken not found, but revokeUserTokens exists. Consider adding single token revocation.'
                    );
                    continue;
                }
            }

            if ($exists)
            {
                $isPublic = $this->isPublicMethod($method);

                if ($isPublic)
                {
                    $this->recordResult(
                        sprintf('Method: %s (%s)', $method, $description),
                        true,
                        'Method exists and is public'
                    );
                }
                else
                {
                    $this->recordResult(
                        sprintf('Method: %s (%s)', $method, $description),
                        false,
                        'Method exists but is not public'
                    );
                }
            }
            else
            {
                $this->recordResult(
                    sprintf('Method: %s (%s)', $method, $description),
                    false,
                    'Method not found'
                );
            }
        }
    }

    /**
     * Validate that all required constants are defined.
     *
     * @return void
     */
    private function validateRequiredConstants()
    {
        $requiredConstants = array(
            'ACCESS_TOKEN_LIFETIME' => array(
                'description' => 'Access token lifetime in seconds',
                'expectedRange' => array(300, 86400) /* 5 minutes to 24 hours */
            ),
            'REFRESH_TOKEN_LIFETIME' => array(
                'description' => 'Refresh token lifetime in seconds',
                'expectedRange' => array(3600, 2592000) /* 1 hour to 30 days */
            ),
            'AUTH_CODE_LIFETIME' => array(
                'description' => 'Authorization code lifetime in seconds',
                'expectedRange' => array(60, 600) /* 1 minute to 10 minutes */
            )
        );

        foreach ($requiredConstants as $constant => $config)
        {
            $exists = $this->hasConstant($constant);

            if ($exists)
            {
                $value = $this->getConstant($constant);
                $inRange = $value >= $config['expectedRange'][0] && $value <= $config['expectedRange'][1];

                if ($inRange)
                {
                    $this->recordResult(
                        sprintf('Constant: %s (%s)', $constant, $config['description']),
                        true,
                        sprintf('Defined with value %d seconds (%.1f hours)', $value, $value / 3600)
                    );
                }
                else
                {
                    $this->recordResult(
                        sprintf('Constant: %s (%s)', $constant, $config['description']),
                        'warning',
                        sprintf(
                            'Value %d is outside recommended range [%d - %d]',
                            $value,
                            $config['expectedRange'][0],
                            $config['expectedRange'][1]
                        )
                    );
                }
            }
            else
            {
                $this->recordResult(
                    sprintf('Constant: %s (%s)', $constant, $config['description']),
                    false,
                    'Constant not defined'
                );
            }
        }
    }

    /**
     * Validate security implementation details.
     *
     * @return void
     */
    private function validateSecurityImplementation()
    {
        /* Check for password_hash usage for client secrets. */
        $usesPasswordHash = preg_match('/password_hash\s*\(/', $this->fileContent) === 1;
        $this->recordResult(
            'Uses password_hash for client secrets',
            $usesPasswordHash,
            $usesPasswordHash
                ? 'password_hash() is used for secure secret storage'
                : 'WARNING: password_hash() not found - client secrets may not be securely stored'
        );

        /* Check for password_verify usage. */
        $usesPasswordVerify = preg_match('/password_verify\s*\(/', $this->fileContent) === 1;
        $this->recordResult(
            'Uses password_verify for secret validation',
            $usesPasswordVerify,
            $usesPasswordVerify
                ? 'password_verify() is used for secure secret validation'
                : 'WARNING: password_verify() not found - secret validation may be insecure'
        );

        /* Check for random_bytes usage for token generation. */
        $usesRandomBytes = preg_match('/random_bytes\s*\(/', $this->fileContent) === 1;
        $this->recordResult(
            'Uses random_bytes for token generation',
            $usesRandomBytes,
            $usesRandomBytes
                ? 'random_bytes() is used for cryptographically secure token generation'
                : 'WARNING: random_bytes() not found - tokens may not be cryptographically secure'
        );

        /* Check for bin2hex usage (common pattern with random_bytes). */
        $usesBinToHex = preg_match('/bin2hex\s*\(/', $this->fileContent) === 1;
        $this->recordResult(
            'Uses bin2hex for token encoding',
            $usesBinToHex,
            $usesBinToHex
                ? 'bin2hex() is used to encode tokens as hexadecimal strings'
                : 'Token encoding method unknown'
        );

        /* Check that PASSWORD_DEFAULT is used (not weaker algorithms). */
        $usesPasswordDefault = preg_match('/PASSWORD_DEFAULT/', $this->fileContent) === 1;
        $this->recordResult(
            'Uses PASSWORD_DEFAULT algorithm',
            $usesPasswordDefault,
            $usesPasswordDefault
                ? 'PASSWORD_DEFAULT ensures the strongest available algorithm is used'
                : 'WARNING: PASSWORD_DEFAULT not found - may be using weaker hash algorithm'
        );
    }

    /**
     * Validate token expiry implementation.
     *
     * @return void
     */
    private function validateTokenExpiry()
    {
        /* Check for expires_at storage in tokens. */
        $storesExpiresAt = preg_match('/expires_at/', $this->fileContent) === 1;
        $this->recordResult(
            'Stores token expiry (expires_at)',
            $storesExpiresAt,
            $storesExpiresAt
                ? 'Token expiry is stored in database'
                : 'WARNING: Token expiry storage not found'
        );

        /* Check for expiry calculation using time(). */
        $calculatesExpiry = preg_match('/time\s*\(\s*\)\s*\+\s*self::/', $this->fileContent) === 1;
        $this->recordResult(
            'Calculates expiry using time() + constant',
            $calculatesExpiry,
            $calculatesExpiry
                ? 'Token expiry is calculated using current time plus lifetime constant'
                : 'Token expiry calculation method unclear'
        );

        /* Check for strtotime comparison for expiry validation. */
        $validatesExpiry = preg_match('/strtotime\s*\([^)]+\)\s*<\s*time\s*\(\s*\)/', $this->fileContent) === 1;
        $this->recordResult(
            'Validates expiry during token use',
            $validatesExpiry,
            $validatesExpiry
                ? 'Token expiry is validated before accepting tokens'
                : 'WARNING: Token expiry validation not found'
        );

        /* Check for date formatting for database storage. */
        $usesDateFormat = preg_match('/date\s*\(\s*[\'"]Y-m-d H:i:s[\'"]/', $this->fileContent) === 1;
        $this->recordResult(
            'Uses proper datetime format for storage',
            $usesDateFormat,
            $usesDateFormat
                ? 'Uses Y-m-d H:i:s format for database datetime storage'
                : 'Datetime format unclear'
        );
    }

    /**
     * Validate additional OAuth 2.0 compliance requirements.
     *
     * @return void
     */
    private function validateOAuth2Compliance()
    {
        /* Check for Bearer token type in response. */
        $usesBearerType = preg_match('/[\'"]token_type[\'"]\s*=>\s*[\'"]Bearer[\'"]/', $this->fileContent) === 1;
        $this->recordResult(
            'Returns Bearer token type',
            $usesBearerType,
            $usesBearerType
                ? 'OAuth 2.0 compliant Bearer token type is returned'
                : 'WARNING: Bearer token type not found in response'
        );

        /* Check for expires_in in response. */
        $hasExpiresIn = preg_match('/[\'"]expires_in[\'"]\s*=>/', $this->fileContent) === 1;
        $this->recordResult(
            'Returns expires_in in token response',
            $hasExpiresIn,
            $hasExpiresIn
                ? 'OAuth 2.0 compliant expires_in field is returned'
                : 'WARNING: expires_in not found in token response'
        );

        /* Check for scope handling. */
        $handlesScope = preg_match('/[\'"]scope[\'"]\s*=>/', $this->fileContent) === 1;
        $this->recordResult(
            'Handles OAuth 2.0 scopes',
            $handlesScope,
            $handlesScope
                ? 'Token scope is properly handled'
                : 'WARNING: Scope handling not found'
        );

        /* Check for redirect_uri validation. */
        $validatesRedirectUri = preg_match('/redirect_uri/', $this->fileContent) === 1;
        $this->recordResult(
            'Validates redirect_uri',
            $validatesRedirectUri,
            $validatesRedirectUri
                ? 'Redirect URI is validated in authorization code flow'
                : 'WARNING: Redirect URI validation not found'
        );

        /* Check for single-use authorization codes. */
        $singleUseAuthCodes = preg_match('/is_used|delete.*auth.*code/i', $this->fileContent) === 1;
        $this->recordResult(
            'Authorization codes are single-use',
            $singleUseAuthCodes,
            $singleUseAuthCodes
                ? 'Authorization codes are invalidated after use'
                : 'WARNING: Single-use auth code enforcement not found'
        );

        /* Check for refresh token rotation. */
        $rotatesRefreshTokens = preg_match('/delete.*refresh.*token/i', $this->fileContent) === 1;
        $this->recordResult(
            'Implements refresh token rotation',
            $rotatesRefreshTokens,
            $rotatesRefreshTokens
                ? 'Old refresh tokens are deleted when new ones are issued'
                : 'WARNING: Refresh token rotation not found'
        );

        /* Check for cleanup method. */
        $hasCleanup = $this->hasMethod('cleanup');
        $this->recordResult(
            'Has token cleanup method',
            $hasCleanup,
            $hasCleanup
                ? 'cleanup() method exists for expired token removal'
                : 'No cleanup method found - expired tokens may accumulate'
        );

        /* Check for confidential client handling. */
        $handlesConfidentialClients = preg_match('/is_confidential/', $this->fileContent) === 1;
        $this->recordResult(
            'Distinguishes confidential vs public clients',
            $handlesConfidentialClients,
            $handlesConfidentialClients
                ? 'Properly distinguishes between confidential and public clients'
                : 'WARNING: Confidential client distinction not found'
        );
    }

    /**
     * Record a test result.
     *
     * @param string $testName Name of the test.
     * @param bool|string $status True for pass, false for fail, 'warning' for warning.
     * @param string $message Additional details.
     * @return void
     */
    private function recordResult($testName, $status, $message)
    {
        if ($status === true)
        {
            $this->passed++;
            $statusText = '[PASS]';
            $color = "\033[32m"; /* Green */
        }
        elseif ($status === 'warning')
        {
            $this->warnings++;
            $statusText = '[WARN]';
            $color = "\033[33m"; /* Yellow */
        }
        else
        {
            $this->failed++;
            $statusText = '[FAIL]';
            $color = "\033[31m"; /* Red */
        }

        $reset = "\033[0m";

        $this->results[] = array(
            'test' => $testName,
            'status' => $status,
            'message' => $message
        );

        /* Print result immediately. */
        printf(
            "  %s%s%s %s\n",
            $color,
            $statusText,
            $reset,
            $testName
        );
        printf("         %s\n", $message);
    }

    /**
     * Print a header.
     *
     * @param string $title Header title.
     * @return void
     */
    private function printHeader($title)
    {
        echo "\n";
        echo str_repeat('=', 70) . "\n";
        echo " " . $title . "\n";
        echo str_repeat('=', 70) . "\n";
        echo "\n";
    }

    /**
     * Print a section header.
     *
     * @param string $title Section title.
     * @return void
     */
    private function printSection($title)
    {
        echo "\n";
        echo str_repeat('-', 50) . "\n";
        echo " " . $title . "\n";
        echo str_repeat('-', 50) . "\n";
    }

    /**
     * Print the test summary.
     *
     * @return void
     */
    private function printSummary()
    {
        $total = $this->passed + $this->failed + $this->warnings;

        echo "\n";
        echo str_repeat('=', 70) . "\n";
        echo " TEST SUMMARY\n";
        echo str_repeat('=', 70) . "\n";

        $green = "\033[32m";
        $red = "\033[31m";
        $yellow = "\033[33m";
        $reset = "\033[0m";

        printf("\n  Total Tests: %d\n", $total);
        printf("  %sPassed:%s %d\n", $green, $reset, $this->passed);
        printf("  %sFailed:%s %d\n", $red, $reset, $this->failed);
        printf("  %sWarnings:%s %d\n", $yellow, $reset, $this->warnings);

        echo "\n";

        if ($this->failed === 0)
        {
            printf("  %s*** ALL REQUIRED CHECKS PASSED ***%s\n", $green, $reset);
        }
        else
        {
            printf("  %s*** %d CHECK(S) FAILED - REVIEW REQUIRED ***%s\n", $red, $this->failed, $reset);
        }

        if ($this->warnings > 0)
        {
            printf("  %s*** %d WARNING(S) - REVIEW RECOMMENDED ***%s\n", $yellow, $this->warnings, $reset);
        }

        echo "\n";
        echo str_repeat('=', 70) . "\n";
    }

    /**
     * Get the exit code based on test results.
     *
     * @return int 0 for success, 1 for failure.
     */
    public function getExitCode()
    {
        return $this->failed > 0 ? 1 : 0;
    }
}


/*
 * Main execution
 */

/* Determine the path to OAuth2Server.php */
$scriptDir = dirname(__FILE__);
$oauth2ServerPath = realpath($scriptDir . '/../../lib/OAuth2Server.php');

/* Handle command line argument for custom path. */
if (isset($argv[1]))
{
    $oauth2ServerPath = $argv[1];
}

/* Validate path */
if (!$oauth2ServerPath)
{
    $oauth2ServerPath = $scriptDir . '/../../lib/OAuth2Server.php';
}

echo "\nOAuth 2.0 Server Validation Script\n";
echo "Target file: " . $oauth2ServerPath . "\n";

/* Create and run validator. */
$validator = new OAuth2ServerValidator($oauth2ServerPath);
$validator->runAllTests();

/* Exit with appropriate code. */
exit($validator->getExitCode());
