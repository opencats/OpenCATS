<?php
/**
 * CATS
 * API Subscription Handler
 *
 * Handles CRUD operations for Webhook Subscriptions via REST API.
 * Provides Bullhorn-compatible webhook subscription management.
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

include_once(dirname(__FILE__) . '/../../../lib/WebhookSubscription.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class SubscriptionHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;

    /**
     * Constructor
     *
     * @param int $siteID Site ID
     * @param int $userID User ID
     * @param object $requestLogger Optional request logger
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle subscriptions endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        /* Check if WebhookSubscription class exists (graceful degradation) */
        if (!class_exists('WebhookSubscription')) {
            $this->sendError('Webhook subscriptions feature is not available', 503);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $action = isset($_GET['action']) ? trim($_GET['action']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $subscriptions = new WebhookSubscription($this->_siteID);

        /* Handle special actions for GET requests */
        if ($method === 'GET' && $action !== null) {
            switch ($action) {
                case 'test':
                    $this->handleTestWebhook($subscriptions, $id);
                    return;
                case 'logs':
                    $this->handleGetLogs($subscriptions, $id);
                    return;
                default:
                    $this->sendError('Unknown action: ' . $action, 400);
                    return;
            }
        }

        switch ($method) {
            case 'GET':
                $this->handleGet($subscriptions, $id);
                break;
            case 'POST':
                $this->handlePost($subscriptions);
                break;
            case 'PUT':
                $this->handlePut($subscriptions, $id);
                break;
            case 'DELETE':
                $this->handleDelete($subscriptions, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     * @param int|null $id Subscription ID for single record
     */
    private function handleGet($subscriptions, $id)
    {
        if ($id) {
            /* Get single subscription */
            $subscription = $subscriptions->get($id);
            if ($subscription && !empty($subscription['subscriptionID'])) {
                $this->sendSuccess($this->formatSubscription($subscription));
            } else {
                $this->sendError('Subscription not found', 404);
            }
        } else {
            /* Get list with optional filters */
            $this->handleList($subscriptions);
        }
    }

    /**
     * Handle list request with filters
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     */
    private function handleList($subscriptions)
    {
        /* Optional filters */
        $entityType = isset($_GET['entityType']) ? trim($_GET['entityType']) : null;
        $isActive = isset($_GET['isActive']) ? (bool)intval($_GET['isActive']) : null;

        /* Validate entityType if provided */
        if ($entityType !== null && !in_array($entityType, WebhookSubscription::getEntityTypes())) {
            $this->sendError('Invalid entityType. Must be one of: ' . implode(', ', WebhookSubscription::getEntityTypes()), 400);
            return;
        }

        $pagination = $this->getPaginationParams();

        /* Get subscriptions from library */
        $allSubscriptions = $subscriptions->getAll(
            $pagination['limit'],
            $pagination['offset'],
            $entityType,
            $isActive
        );

        /* Format subscriptions */
        $formatted = [];
        if (is_array($allSubscriptions)) {
            foreach ($allSubscriptions as $subscription) {
                $formatted[] = $this->formatSubscription($subscription);
            }
        }

        /* Get total count for pagination */
        $total = $subscriptions->getCount($entityType, $isActive);

        $this->sendSuccess([
            'total' => $total,
            'page' => $pagination['page'],
            'limit' => $pagination['limit'],
            'data' => $formatted
        ]);
    }

    /**
     * Handle POST request (create subscription)
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     */
    private function handlePost($subscriptions)
    {
        $input = $this->getRequestBody();

        /* Validate required fields */
        if (empty($input['name'])) {
            $this->sendError('Missing required field: name', 400);
            return;
        }

        if (empty($input['entityType'])) {
            $this->sendError('Missing required field: entityType', 400);
            return;
        }

        if (empty($input['eventTypes']) || !is_array($input['eventTypes'])) {
            $this->sendError('Missing required field: eventTypes (must be an array)', 400);
            return;
        }

        if (empty($input['callbackUrl'])) {
            $this->sendError('Missing required field: callbackUrl', 400);
            return;
        }

        /* Validate entityType */
        if (!in_array($input['entityType'], WebhookSubscription::getEntityTypes())) {
            $this->sendError('Invalid entityType. Must be one of: ' . implode(', ', WebhookSubscription::getEntityTypes()), 400);
            return;
        }

        /* Validate eventTypes */
        $validEventTypes = WebhookSubscription::getEventTypes();
        foreach ($input['eventTypes'] as $eventType) {
            if (!in_array($eventType, $validEventTypes)) {
                $this->sendError('Invalid eventType: ' . $eventType . '. Must be one of: ' . implode(', ', $validEventTypes), 400);
                return;
            }
        }

        /* Validate callbackUrl format */
        if (!filter_var($input['callbackUrl'], FILTER_VALIDATE_URL)) {
            $this->sendError('Invalid callbackUrl format', 400);
            return;
        }

        /* Extract fields */
        $name = $input['name'];
        $entityType = $input['entityType'];
        $eventTypes = $input['eventTypes'];
        $callbackUrl = $input['callbackUrl'];
        $secret = isset($input['secret']) ? $input['secret'] : null;

        /* Create the subscription */
        $subscriptionID = $subscriptions->add(
            $name,
            $entityType,
            $eventTypes,
            $callbackUrl,
            $this->_userID,
            $secret
        );

        if ($subscriptionID === false || $subscriptionID <= 0) {
            $this->sendError('Failed to create subscription', 500);
            return;
        }

        /* Return the created subscription */
        $newSubscription = $subscriptions->get($subscriptionID);
        $this->sendSuccess($this->formatSubscription($newSubscription), 201);
    }

    /**
     * Handle PUT request (update subscription)
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     * @param int|null $id Subscription ID
     */
    private function handlePut($subscriptions, $id)
    {
        if (!$id) {
            $this->sendError('Subscription ID required for update', 400);
            return;
        }

        /* Check if subscription exists */
        $existing = $subscriptions->get($id);
        if (!$existing || empty($existing['subscriptionID'])) {
            $this->sendError('Subscription not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        /* Build update data array */
        $updateData = [];

        if (isset($input['name'])) {
            $updateData['name'] = $input['name'];
        }

        if (isset($input['callbackUrl'])) {
            /* Validate callbackUrl format */
            if (!filter_var($input['callbackUrl'], FILTER_VALIDATE_URL)) {
                $this->sendError('Invalid callbackUrl format', 400);
                return;
            }
            $updateData['callbackUrl'] = $input['callbackUrl'];
        }

        if (isset($input['eventTypes'])) {
            if (!is_array($input['eventTypes'])) {
                $this->sendError('eventTypes must be an array', 400);
                return;
            }

            /* Validate eventTypes */
            $validEventTypes = WebhookSubscription::getEventTypes();
            foreach ($input['eventTypes'] as $eventType) {
                if (!in_array($eventType, $validEventTypes)) {
                    $this->sendError('Invalid eventType: ' . $eventType . '. Must be one of: ' . implode(', ', $validEventTypes), 400);
                    return;
                }
            }
            $updateData['eventTypes'] = $input['eventTypes'];
        }

        if (isset($input['isActive'])) {
            $updateData['isActive'] = (bool)$input['isActive'];
        }

        if (array_key_exists('secret', $input)) {
            $updateData['secret'] = $input['secret'];
        }

        if (empty($updateData)) {
            $this->sendError('No update fields provided', 400);
            return;
        }

        /* Perform update */
        $success = $subscriptions->update($id, $updateData);

        if (!$success) {
            $this->sendError('Failed to update subscription', 500);
            return;
        }

        /* Return updated subscription */
        $updatedSubscription = $subscriptions->get($id);
        $this->sendSuccess($this->formatSubscription($updatedSubscription));
    }

    /**
     * Handle DELETE request
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     * @param int|null $id Subscription ID
     */
    private function handleDelete($subscriptions, $id)
    {
        if (!$id) {
            $this->sendError('Subscription ID required for delete', 400);
            return;
        }

        /* Check if subscription exists */
        $existing = $subscriptions->get($id);
        if (!$existing || empty($existing['subscriptionID'])) {
            $this->sendError('Subscription not found', 404);
            return;
        }

        /* Perform delete */
        $success = $subscriptions->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete subscription', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Subscription deleted successfully',
            'id' => $id
        ]);
    }

    /**
     * Handle test webhook action
     * Sends a test webhook to verify the callback URL works
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     * @param int|null $id Subscription ID
     */
    private function handleTestWebhook($subscriptions, $id)
    {
        if (!$id) {
            $this->sendError('Subscription ID required for test', 400);
            return;
        }

        /* Check if subscription exists */
        $subscription = $subscriptions->get($id);
        if (!$subscription || empty($subscription['subscriptionID'])) {
            $this->sendError('Subscription not found', 404);
            return;
        }

        /* Build test payload */
        $testPayload = [
            'test' => true,
            'subscriptionId' => intval($subscription['subscriptionID']),
            'subscriptionName' => $subscription['name'],
            'entityType' => $subscription['entityType'],
            'eventTypes' => $subscription['eventTypesArray'],
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'message' => 'This is a test webhook from OpenCATS'
        ];

        $payloadJson = json_encode($testPayload);
        $callbackUrl = $subscription['callbackUrl'];

        /* Send test webhook */
        $ch = curl_init($callbackUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $headers = [
            'Content-Type: application/json',
            'User-Agent: OpenCATS-Webhook/1.0',
            'X-OpenCATS-Webhook-Test: true'
        ];

        /* Add HMAC signature if secret is configured */
        if (!empty($subscription['secret'])) {
            $signature = hash_hmac('sha256', $payloadJson, $subscription['secret']);
            $headers[] = 'X-OpenCATS-Signature: sha256=' . $signature;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        /* Log the test delivery */
        $status = ($responseCode >= 200 && $responseCode < 300)
            ? WebhookSubscription::STATUS_SUCCESS
            : WebhookSubscription::STATUS_FAILED;

        $subscriptions->logDelivery(
            $id,
            'test',
            0,
            $payloadJson,
            $responseCode,
            $responseBody ? substr($responseBody, 0, 1000) : $curlError,
            $status
        );

        /* Return result */
        if ($responseCode >= 200 && $responseCode < 300) {
            $this->sendSuccess([
                'success' => true,
                'message' => 'Test webhook delivered successfully',
                'subscriptionId' => intval($id),
                'callbackUrl' => $callbackUrl,
                'responseCode' => $responseCode,
                'responseBody' => $responseBody ? substr($responseBody, 0, 500) : null
            ]);
        } else {
            $this->sendSuccess([
                'success' => false,
                'message' => 'Test webhook delivery failed',
                'subscriptionId' => intval($id),
                'callbackUrl' => $callbackUrl,
                'responseCode' => $responseCode,
                'error' => $curlError ?: ($responseBody ? substr($responseBody, 0, 500) : 'Unknown error')
            ]);
        }
    }

    /**
     * Handle get delivery logs action
     *
     * @param WebhookSubscription $subscriptions WebhookSubscription instance
     * @param int|null $id Subscription ID
     */
    private function handleGetLogs($subscriptions, $id)
    {
        if (!$id) {
            $this->sendError('Subscription ID required for logs', 400);
            return;
        }

        /* Check if subscription exists */
        $subscription = $subscriptions->get($id);
        if (!$subscription || empty($subscription['subscriptionID'])) {
            $this->sendError('Subscription not found', 404);
            return;
        }

        /* Get limit from query params */
        $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 50;

        /* Get delivery logs */
        $logs = $subscriptions->getDeliveryLogs($id, $limit);

        /* Format logs for response */
        $formattedLogs = [];
        foreach ($logs as $log) {
            $formattedLogs[] = [
                'id' => intval($log['logID']),
                'subscriptionId' => intval($log['subscriptionID']),
                'eventType' => $log['eventType'],
                'entityId' => intval($log['entityID']),
                'responseCode' => $log['responseCode'] !== null ? intval($log['responseCode']) : null,
                'status' => $log['status'],
                'attemptCount' => intval($log['attemptCount']),
                'dateCreated' => $log['dateCreated'],
                'dateCompleted' => $log['dateCompleted']
            ];
        }

        $this->sendSuccess([
            'subscriptionId' => intval($id),
            'subscriptionName' => $subscription['name'],
            'total' => count($formattedLogs),
            'data' => $formattedLogs
        ]);
    }

    /**
     * Format a subscription record for API response
     *
     * @param array $subscription Raw subscription data
     * @return array Formatted subscription data
     */
    private function formatSubscription($subscription)
    {
        return [
            'id' => intval($subscription['subscriptionID']),
            'name' => $subscription['name'],
            'entityType' => $subscription['entityType'],
            'eventTypes' => $subscription['eventTypesArray'] ?? explode(',', $subscription['eventTypes']),
            'callbackUrl' => $subscription['callbackUrl'],
            'isActive' => (bool)$subscription['isActive'],
            'dateAdded' => $subscription['dateCreated'],
            'dateLastModified' => $subscription['dateModified'],
            'createdBy' => [
                'id' => intval($subscription['createdBy'])
            ]
        ];
    }
}
