<?php
/**
 * CATS
 * API Configuration
 *
 * Configurable settings for the REST API module.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: ApiConfig.php 2026-01-25 $
 */

/**
 * API Configuration Constants
 *
 * Override these in config.php if needed:
 * define('API_RATE_LIMIT_PER_MINUTE', 120);
 */

// Rate Limiting
if (!defined('API_RATE_LIMIT_PER_MINUTE')) {
    define('API_RATE_LIMIT_PER_MINUTE', 60);
}

if (!defined('API_RATE_LIMIT_PER_HOUR')) {
    define('API_RATE_LIMIT_PER_HOUR', 1000);
}

// Request Logging
if (!defined('API_LOG_ENABLED')) {
    define('API_LOG_ENABLED', true);
}

if (!defined('API_LOG_RETENTION_DAYS')) {
    define('API_LOG_RETENTION_DAYS', 30);
}

// Session Token Settings
if (!defined('API_TOKEN_EXPIRY_SECONDS')) {
    define('API_TOKEN_EXPIRY_SECONDS', 3600);
}

// CORS Settings (can be overridden for security)
if (!defined('API_CORS_ALLOWED_ORIGINS')) {
    define('API_CORS_ALLOWED_ORIGINS', '*');  // Change to specific domain in production
}

// API Version
if (!defined('API_VERSION')) {
    define('API_VERSION', '1.0.0');
}

// Rate Limiting Enable/Disable
if (!defined('API_RATE_LIMIT_ENABLED')) {
    define('API_RATE_LIMIT_ENABLED', true);
}
