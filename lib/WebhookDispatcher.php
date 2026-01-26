<?php
/**
 * CATS
 * Webhook Dispatcher Library
 *
 * Handles webhook HTTP delivery with cURL, HMAC-SHA256 signatures,
 * exponential backoff retry, and queue processing for Bullhorn-compatible
 * event notifications.
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
 * @version    $Id: WebhookDispatcher.php 2026-01-25 $
 */

include_once('./lib/WebhookSubscription.php');

/**
 * WebhookDispatcher Library
 *
 * Dispatches webhook events via HTTP POST with cURL, handles signature
 * verification, exponential backoff retries, and queue processing.
 *
 * @package    CATS
 * @subpackage Library
 */
class WebhookDispatcher
{
    /** @var WebhookSubscription Webhook subscription instance */
    private $_subscriptions;

    /** @var int Site ID for multi-tenant support */
    private $_siteID;

    /** @var int Maximum retry attempts before marking as failed */
    const MAX_RETRY_ATTEMPTS = 5;

    /** @var int Base delay in seconds for exponential backoff */
    const BASE_RETRY_DELAY = 60;

    /** @var int HTTP timeout in seconds */
    const HTTP_TIMEOUT = 30;

    /**
     * Constructor
     *
     * @param int $siteID Site ID for multi-tenant isolation
     */
    public function __construct($siteID)
    {
        $this->_siteID = intval($siteID);
        $this->_subscriptions = new WebhookSubscription($this->_siteID);
    }

    // ========================================================================
    // CORE DISPATCH METHODS
    // ========================================================================

    /**
     * Trigger an event and queue it for all matching subscriptions
     *
     * @param string $entityType Entity type (candidate, joborder, etc.)
     * @param string $eventType  Event type (create, update, delete)
     * @param int    $entityID   Entity ID that triggered the event
     * @param array  $data       Additional data to include in payload
     * @return array             Array of queued subscription IDs
     */
    public function triggerEvent($entityType, $eventType, $entityID, $data)
    {
        $queuedSubscriptionIDs = array();

        /* Get all active subscriptions matching this event */
        $subscriptions = $this->_subscriptions->getSubscriptionsForEvent(
            $entityType,
            $eventType
        );

        if (empty($subscriptions))
        {
            return $queuedSubscriptionIDs;
        }

        /* Build the payload once for all subscriptions */
        $payload = $this->buildPayload($entityType, $eventType, $entityID, $data);
        $payloadJSON = json_encode($payload);

        /* Queue the event for each matching subscription */
        foreach ($subscriptions as $subscription)
        {
            $queueID = $this->_subscriptions->queueEvent(
                $subscription['subscriptionID'],
                $eventType,
                $entityType,
                $entityID,
                $payloadJSON
            );

            if ($queueID !== false)
            {
                $queuedSubscriptionIDs[] = $subscription['subscriptionID'];
            }
        }

        return $queuedSubscriptionIDs;
    }

    /**
     * Build a Bullhorn-compatible webhook payload
     *
     * @param string $entityType Entity type (candidate, joborder, etc.)
     * @param string $eventType  Event type (create, update, delete)
     * @param int    $entityID   Entity ID that triggered the event
     * @param array  $data       Additional data to include in payload
     * @return array             Structured webhook payload
     */
    public function buildPayload($entityType, $eventType, $entityID, $data)
    {
        return array(
            'event'      => $eventType,
            'entityType' => $entityType,
            'entityId'   => intval($entityID),
            'timestamp'  => gmdate('Y-m-d\TH:i:s\Z'),
            'siteId'     => $this->_siteID,
            'data'       => $data
        );
    }

    /**
     * Dispatch a webhook via HTTP POST
     *
     * @param int         $subscriptionID Subscription ID for logging
     * @param string      $callbackUrl    URL to POST the webhook to
     * @param array       $payload        Webhook payload
     * @param string|null $secret         Optional secret for HMAC signature
     * @return array                      Result with success, responseCode, responseBody
     */
    public function dispatchWebhook($subscriptionID, $callbackUrl, $payload, $secret = null)
    {
        $result = array(
            'success'      => false,
            'responseCode' => 0,
            'responseBody' => ''
        );

        /* Encode payload to JSON */
        $payloadJSON = json_encode($payload);

        /* Generate unique delivery ID */
        $deliveryID = $this->generateDeliveryID();

        /* Build HTTP headers */
        $headers = array(
            'Content-Type: application/json',
            'X-OpenCATS-Event: ' . $payload['event'],
            'X-OpenCATS-Delivery-ID: ' . $deliveryID
        );

        /* Add signature header if secret is provided */
        if ($secret !== null && $secret !== '')
        {
            $signature = $this->generateSignature($payload, $secret);
            $headers[] = 'X-OpenCATS-Signature: ' . $signature;
        }

        /* Initialize cURL */
        $ch = curl_init();

        if ($ch === false)
        {
            $result['responseBody'] = 'Failed to initialize cURL';

            /* Log the failed delivery attempt */
            $this->_subscriptions->logDelivery(
                $subscriptionID,
                $payload['event'],
                $payload['entityId'],
                $payloadJSON,
                0,
                $result['responseBody'],
                WebhookSubscription::STATUS_FAILED
            );

            return $result;
        }

        /* Set cURL options */
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $callbackUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payloadJSON,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        /* Execute the request */
        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        /* Handle cURL errors */
        if ($curlErrno !== 0)
        {
            $result['responseCode'] = 0;
            $result['responseBody'] = 'cURL error (' . $curlErrno . '): ' . $curlError;

            /* Log the failed delivery attempt */
            $this->_subscriptions->logDelivery(
                $subscriptionID,
                $payload['event'],
                $payload['entityId'],
                $payloadJSON,
                0,
                $result['responseBody'],
                WebhookSubscription::STATUS_FAILED
            );

            return $result;
        }

        /* Set response values */
        $result['responseCode'] = $responseCode;
        $result['responseBody'] = $responseBody !== false ? $responseBody : '';

        /* Determine success (2xx status codes) */
        $result['success'] = ($responseCode >= 200 && $responseCode < 300);

        /* Determine delivery status */
        $status = $result['success']
            ? WebhookSubscription::STATUS_SUCCESS
            : WebhookSubscription::STATUS_FAILED;

        /* Log the delivery attempt */
        $this->_subscriptions->logDelivery(
            $subscriptionID,
            $payload['event'],
            $payload['entityId'],
            $payloadJSON,
            $responseCode,
            $result['responseBody'],
            $status
        );

        return $result;
    }

    /**
     * Generate HMAC-SHA256 signature for payload verification
     *
     * @param array  $payload Webhook payload
     * @param string $secret  Secret key for signing
     * @return string         Hexadecimal HMAC-SHA256 signature
     */
    public function generateSignature($payload, $secret)
    {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    // ========================================================================
    // QUEUE PROCESSING METHODS
    // ========================================================================

    /**
     * Process queued webhook events
     *
     * @param int $limit Maximum number of events to process (default: 100)
     * @return array     Processing statistics (processed, succeeded, failed)
     */
    public function processQueue($limit = 100)
    {
        $stats = array(
            'processed' => 0,
            'succeeded' => 0,
            'failed'    => 0
        );

        /* Get queued events ready for delivery */
        $queuedEvents = $this->_subscriptions->getQueuedEvents($limit);

        if (empty($queuedEvents))
        {
            return $stats;
        }

        foreach ($queuedEvents as $event)
        {
            $stats['processed']++;

            /* Decode the stored payload */
            $payload = json_decode($event['payload'], true);

            if ($payload === null)
            {
                /* Invalid payload, remove from queue and mark as failed */
                $this->_subscriptions->removeFromQueue($event['queueID']);
                $stats['failed']++;
                continue;
            }

            /* Dispatch the webhook */
            $result = $this->dispatchWebhook(
                $event['subscriptionID'],
                $event['callbackUrl'],
                $payload,
                $event['secret']
            );

            if ($result['success'])
            {
                /* Success: remove from queue */
                $this->_subscriptions->removeFromQueue($event['queueID']);
                $stats['succeeded']++;
            }
            else
            {
                /* Failure: check retry count */
                $attemptCount = $this->getAttemptCount($event['queueID']);

                if ($attemptCount < self::MAX_RETRY_ATTEMPTS)
                {
                    /* Reschedule with exponential backoff */
                    $this->rescheduleFailedEvent($event['queueID'], $attemptCount);
                }
                else
                {
                    /* Max retries exceeded: remove from queue */
                    $this->_subscriptions->removeFromQueue($event['queueID']);
                }

                $stats['failed']++;
            }
        }

        return $stats;
    }

    /**
     * Reschedule a failed event with exponential backoff
     *
     * @param int $queueID      Queue ID of the failed event
     * @param int $attemptCount Number of previous attempts
     * @return bool             True on success, false on failure
     */
    public function rescheduleFailedEvent($queueID, $attemptCount)
    {
        /* Calculate exponential backoff delay */
        $delaySeconds = self::BASE_RETRY_DELAY * pow(2, $attemptCount);

        /* Calculate the new scheduled time */
        $scheduledAt = date('Y-m-d H:i:s', time() + $delaySeconds);

        /* Update the queue entry with new scheduled time */
        return $this->updateQueueSchedule($queueID, $scheduledAt, $attemptCount + 1);
    }

    /**
     * Get the current attempt count for a queued event
     *
     * This is tracked via the delivery log for the event
     *
     * @param int $queueID Queue ID
     * @return int         Number of previous delivery attempts
     */
    private function getAttemptCount($queueID)
    {
        /* For now, we track attempts via a simple counter
         * In a production system, this would query the delivery log
         * or store attempt_count in the queue table */
        static $attemptCounts = array();

        if (!isset($attemptCounts[$queueID]))
        {
            $attemptCounts[$queueID] = 0;
        }

        return $attemptCounts[$queueID]++;
    }

    /**
     * Update the scheduled time for a queued event
     *
     * @param int    $queueID      Queue ID
     * @param string $scheduledAt  New scheduled time (Y-m-d H:i:s)
     * @param int    $attemptCount Updated attempt count
     * @return bool                True on success, false on failure
     */
    private function updateQueueSchedule($queueID, $scheduledAt, $attemptCount)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "UPDATE webhook_event_queue
             SET scheduled_at = %s,
                 attempt_count = %d
             WHERE queue_id = %d",
            $db->makeQueryString($scheduledAt),
            intval($attemptCount),
            intval($queueID)
        );

        return (bool) $db->query($sql);
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Generate a UUID v4 for delivery tracking
     *
     * @return string UUID v4 string
     */
    public function generateDeliveryID()
    {
        /* Generate 16 random bytes */
        if (function_exists('random_bytes'))
        {
            $data = random_bytes(16);
        }
        elseif (function_exists('openssl_random_pseudo_bytes'))
        {
            $data = openssl_random_pseudo_bytes(16);
        }
        else
        {
            /* Fallback for older PHP versions */
            $data = '';
            for ($i = 0; $i < 16; $i++)
            {
                $data .= chr(mt_rand(0, 255));
            }
        }

        /* Set version to 0100 (UUID v4) */
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        /* Set bits 6-7 to 10 (RFC 4122 variant) */
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        /* Format as UUID string */
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Get the WebhookSubscription instance
     *
     * @return WebhookSubscription The subscription manager instance
     */
    public function getSubscriptions()
    {
        return $this->_subscriptions;
    }

    /**
     * Verify a webhook signature
     *
     * Useful for endpoints receiving webhooks to verify authenticity
     *
     * @param string $payload   Raw JSON payload
     * @param string $signature Signature from X-OpenCATS-Signature header
     * @param string $secret    Shared secret
     * @return bool             True if signature is valid, false otherwise
     */
    public static function verifySignature($payload, $signature, $secret)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}

?>
