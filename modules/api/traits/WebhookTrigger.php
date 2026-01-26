<?php
/**
 * CATS
 * Webhook Trigger Trait
 *
 * Provides webhook event triggering for API handlers.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
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
 * @package    CATS
 * @subpackage API
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 */

// Include WebhookDispatcher
if (file_exists('./lib/WebhookDispatcher.php')) {
    include_once('./lib/WebhookDispatcher.php');
}

trait WebhookTrigger
{
    /**
     * Trigger a webhook event
     *
     * @param string $entityType Entity type (candidate, joborder, etc.)
     * @param string $eventType Event type (create, update, delete)
     * @param int $entityID Entity ID
     * @param array $data Entity data to include in payload
     */
    protected function triggerWebhook($entityType, $eventType, $entityID, $data = [])
    {
        // Only trigger if WebhookDispatcher is available
        if (!class_exists('WebhookDispatcher')) {
            return;
        }

        // Need site ID from the handler
        if (!isset($this->_siteID)) {
            return;
        }

        try {
            $dispatcher = new WebhookDispatcher($this->_siteID);
            $dispatcher->triggerEvent($entityType, $eventType, $entityID, $data);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log('Webhook trigger failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper to strip sensitive fields from data before sending
     *
     * @param array $data Data to sanitize
     * @param array $sensitiveFields List of sensitive field names to remove
     * @return array Sanitized data
     */
    protected function sanitizeWebhookData($data, $sensitiveFields = ['password', 'secret', 'api_key'])
    {
        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
            unset($data[str_replace('_', '', ucwords($field, '_'))]);
        }
        return $data;
    }
}
