<?php
/**
 * CATS
 * API Attachment Handler
 *
 * Handles REST API operations for file attachments including
 * upload, download, listing, and deletion.
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

include_once('./lib/Attachments.php');
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class AttachmentHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;

    /**
     * Block size for streaming file downloads (80KB chunks)
     */
    const ATTACHMENT_BLOCK_SIZE = 81920;

    /**
     * Default allowed MIME types for upload
     */
    private static $_allowedMimeTypes = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/rtf',
        'text/plain',
        'text/html',
        'text/csv',
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/webp',
        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        // Other
        'application/octet-stream'
    ];

    /**
     * Maximum file size in bytes (default 10MB)
     */
    private static $_maxFileSize = 10485760;

    /**
     * Data item type mapping from string names to constants
     */
    private static $_dataItemTypeMap = [
        'candidate' => 100,   // DATA_ITEM_CANDIDATE
        'company' => 200,     // DATA_ITEM_COMPANY
        'contact' => 300,     // DATA_ITEM_CONTACT
        'joborder' => 400,    // DATA_ITEM_JOBORDER
        'bulkresume' => 500,  // DATA_ITEM_BULKRESUME
        'user' => 600,        // DATA_ITEM_USER
        'list' => 700,        // DATA_ITEM_LIST
        'pipeline' => 800,    // DATA_ITEM_PIPELINE
        'duplicate' => 900,   // DATA_ITEM_DUPLICATE
        'placement' => 1000,  // DATA_ITEM_PLACEMENT
        'jobsubmission' => 1100, // DATA_ITEM_JOBSUBMISSION
        'task' => 1200,       // DATA_ITEM_TASK
        'appointment' => 1300, // DATA_ITEM_APPOINTMENT
        'note' => 1400        // DATA_ITEM_NOTE
    ];

    /**
     * Constructor
     *
     * @param int $siteID Site ID
     * @param int $userID User ID
     * @param object|null $requestLogger Optional request logger
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle attachments endpoint
     * Routes requests to appropriate handler based on HTTP method
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $download = isset($_GET['download']) && $_GET['download'] == '1';
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if ($id && $download) {
                    $this->handleDownload($id);
                } elseif ($id) {
                    $this->handleGetOne($id);
                } else {
                    $this->handleList();
                }
                break;
            case 'POST':
                $this->handleUpload();
                break;
            case 'DELETE':
                $this->handleDelete($id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET request - list attachments with filtering
     *
     * Filters by dataItemType and dataItemID if provided
     */
    private function handleList()
    {
        $dataItemType = isset($_GET['dataItemType']) ? $this->resolveDataItemType($_GET['dataItemType']) : null;
        $dataItemID = isset($_GET['dataItemID']) ? intval($_GET['dataItemID']) : null;

        if ($dataItemType === null || $dataItemID === null) {
            $this->sendError('dataItemType and dataItemID are required for listing attachments', 400);
            return;
        }

        $attachments = new Attachments($this->_siteID);
        $results = $attachments->getAll($dataItemType, $dataItemID);

        $pagination = $this->getPaginationParams();
        $formatted = [];

        if (is_array($results)) {
            foreach ($results as $attachment) {
                $formatted[] = $this->formatAttachment($attachment);
            }
        }

        $this->sendPaginatedResponse($formatted, $pagination['page'], $pagination['limit']);
    }

    /**
     * Handle GET request - get single attachment metadata
     *
     * @param int $id Attachment ID
     */
    private function handleGetOne($id)
    {
        if (!$id) {
            $this->sendError('Attachment ID required', 400);
            return;
        }

        $attachments = new Attachments($this->_siteID);
        $attachment = $attachments->get($id);

        if (empty($attachment) || empty($attachment['attachmentID'])) {
            $this->sendError('Attachment not found', 404);
            return;
        }

        $this->sendSuccess($this->formatAttachment($attachment));
    }

    /**
     * Handle GET request with download=1 - stream file download
     *
     * @param int $id Attachment ID
     */
    private function handleDownload($id)
    {
        if (!$id) {
            $this->sendError('Attachment ID required', 400);
            return;
        }

        $attachments = new Attachments($this->_siteID);
        $attachment = $attachments->get($id);

        if (empty($attachment) || empty($attachment['attachmentID'])) {
            $this->sendError('Attachment not found', 404);
            return;
        }

        $directoryName = $attachment['directoryName'];
        $storedFilename = $attachment['storedFilename'];
        $originalFilename = $attachment['originalFilename'];
        $filePath = sprintf('attachments/%s/%s', $directoryName, $storedFilename);

        // Check file existence
        if (!file_exists($filePath)) {
            $this->sendError('Attachment file not found on server', 404);
            return;
        }

        // Determine content type
        $contentType = !empty($attachment['contentType'])
            ? $attachment['contentType']
            : Attachments::fileMimeType($storedFilename);

        // Get file size
        $fileSize = filesize($filePath);

        // Open the file
        $fp = @fopen($filePath, 'rb');
        if ($fp === false) {
            $this->sendError('Unable to read attachment file', 500);
            return;
        }

        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Handle Range requests for partial content (optional, for large files)
        $range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;

        if ($range) {
            $this->handleRangeRequest($fp, $filePath, $fileSize, $contentType, $originalFilename, $range);
        } else {
            // Set headers for full file download
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $this->sanitizeFilename($originalFilename) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . $fileSize);
            header('Accept-Ranges: bytes');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0');

            // Stream the file in chunks
            while (!feof($fp)) {
                echo fread($fp, self::ATTACHMENT_BLOCK_SIZE);
                flush();
            }
        }

        fclose($fp);
        exit;
    }

    /**
     * Handle Range request for partial content download
     *
     * @param resource $fp File pointer
     * @param string $filePath File path
     * @param int $fileSize Total file size
     * @param string $contentType MIME type
     * @param string $filename Original filename
     * @param string $range Range header value
     */
    private function handleRangeRequest($fp, $filePath, $fileSize, $contentType, $filename, $range)
    {
        // Parse range header
        if (!preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        $start = $matches[1] !== '' ? intval($matches[1]) : 0;
        $end = $matches[2] !== '' ? intval($matches[2]) : ($fileSize - 1);

        // Validate range
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        $length = $end - $start + 1;

        // Set partial content headers
        header('HTTP/1.1 206 Partial Content');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $this->sanitizeFilename($filename) . '"');
        header('Content-Length: ' . $length);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Accept-Ranges: bytes');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // Seek to start position
        fseek($fp, $start);

        // Read and output the requested range
        $remaining = $length;
        while ($remaining > 0 && !feof($fp)) {
            $readSize = min(self::ATTACHMENT_BLOCK_SIZE, $remaining);
            echo fread($fp, $readSize);
            flush();
            $remaining -= $readSize;
        }
    }

    /**
     * Handle POST request - upload new attachment
     */
    private function handleUpload()
    {
        // Check for file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->sendError('No file uploaded', 400);
            return;
        }

        $file = $_FILES['file'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->sendError($this->getUploadErrorMessage($file['error']), 400);
            return;
        }

        // Get required parameters
        $dataItemType = isset($_POST['dataItemType']) ? $this->resolveDataItemType($_POST['dataItemType']) : null;
        $dataItemID = isset($_POST['dataItemID']) ? intval($_POST['dataItemID']) : null;

        if ($dataItemType === null) {
            $this->sendError('dataItemType is required (candidate, joborder, company, contact, etc.)', 400);
            return;
        }

        if ($dataItemID === null || $dataItemID <= 0) {
            $this->sendError('dataItemID is required and must be a positive integer', 400);
            return;
        }

        // Get optional parameters
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $contentType = isset($_POST['contentType']) ? trim($_POST['contentType']) : '';
        $isResume = isset($_POST['isResume']) ? filter_var($_POST['isResume'], FILTER_VALIDATE_BOOLEAN) : false;

        // Validate file size
        if ($file['size'] > self::$_maxFileSize) {
            $this->sendError('File size exceeds maximum allowed (' . self::formatBytes(self::$_maxFileSize) . ')', 400);
            return;
        }

        // Validate MIME type
        $detectedMimeType = !empty($contentType) ? $contentType : $file['type'];
        if (!$this->isAllowedMimeType($detectedMimeType)) {
            // Allow unknown types as octet-stream
            $detectedMimeType = 'application/octet-stream';
        }

        // Sanitize filename
        $originalFilename = $this->sanitizeFilename($file['name']);
        if (empty($title)) {
            $title = pathinfo($originalFilename, PATHINFO_FILENAME);
        }

        // Use AttachmentCreator for proper file handling
        $attachmentCreator = new AttachmentCreator($this->_siteID);
        $success = $attachmentCreator->createFromUpload(
            $dataItemType,
            $dataItemID,
            'file',  // File field name
            false,   // Not a profile image
            $isResume  // Extract text if it's a resume
        );

        if (!$success) {
            if ($attachmentCreator->duplicatesOccurred()) {
                $this->sendError('A duplicate attachment already exists', 409);
                return;
            }
            $this->sendError('Failed to create attachment: ' . $attachmentCreator->getError(), 500);
            return;
        }

        // Get the created attachment
        $attachmentID = $attachmentCreator->getAttachmentID();
        $attachments = new Attachments($this->_siteID);
        $attachment = $attachments->get($attachmentID);

        if (empty($attachment)) {
            $this->sendError('Attachment created but could not be retrieved', 500);
            return;
        }

        $this->sendSuccess($this->formatAttachment($attachment), 201);
    }

    /**
     * Handle DELETE request - delete attachment
     *
     * @param int|null $id Attachment ID
     */
    private function handleDelete($id)
    {
        if (!$id) {
            $this->sendError('Attachment ID required for delete', 400);
            return;
        }

        $attachments = new Attachments($this->_siteID);
        $attachment = $attachments->get($id);

        if (empty($attachment) || empty($attachment['attachmentID'])) {
            $this->sendError('Attachment not found', 404);
            return;
        }

        // Delete attachment (including file)
        $success = $attachments->delete($id, true);

        if (!$success) {
            $this->sendError('Failed to delete attachment', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Attachment deleted successfully',
            'id' => $id
        ]);
    }

    /**
     * Format attachment data for API response
     *
     * @param array $attachment Raw attachment data
     * @return array Formatted attachment
     */
    private function formatAttachment($attachment)
    {
        return [
            'id' => intval($attachment['attachmentID'] ?? 0),
            'title' => $attachment['title'] ?? '',
            'originalFilename' => $attachment['originalFilename'] ?? '',
            'contentType' => $attachment['contentType'] ?? 'application/octet-stream',
            'fileSize' => intval($attachment['fileSizeKB'] ?? 0) * 1024, // Convert KB to bytes
            'fileSizeKB' => intval($attachment['fileSizeKB'] ?? 0),
            'dataItemType' => intval($attachment['dataItemType'] ?? 0),
            'dataItemTypeName' => $this->getDataItemTypeName(intval($attachment['dataItemType'] ?? 0)),
            'dataItemId' => intval($attachment['dataItemID'] ?? 0),
            'isResume' => isset($attachment['hasText']) && $attachment['hasText'] == '1',
            'isProfileImage' => (bool)($attachment['isProfileImage'] ?? 0),
            'md5sum' => $attachment['md5sum'] ?? '',
            'dateCreated' => $attachment['dateCreated'] ?? '',
            'downloadUrl' => sprintf('/api/v1/attachments?id=%d&download=1', intval($attachment['attachmentID'] ?? 0))
        ];
    }

    /**
     * Resolve data item type from string or integer
     *
     * @param mixed $type Type as string name or integer
     * @return int|null Data item type constant or null if invalid
     */
    private function resolveDataItemType($type)
    {
        // If it's already an integer, validate it
        if (is_numeric($type)) {
            $intType = intval($type);
            // Check if it's a valid type by looking in our reverse map
            if (in_array($intType, self::$_dataItemTypeMap)) {
                return $intType;
            }
            return null;
        }

        // Convert string to lowercase for lookup
        $typeKey = strtolower(trim($type));

        if (isset(self::$_dataItemTypeMap[$typeKey])) {
            return self::$_dataItemTypeMap[$typeKey];
        }

        return null;
    }

    /**
     * Get human-readable name for data item type
     *
     * @param int $type Data item type constant
     * @return string Type name
     */
    private function getDataItemTypeName($type)
    {
        $reverseMap = array_flip(self::$_dataItemTypeMap);
        return isset($reverseMap[$type]) ? ucfirst($reverseMap[$type]) : 'Unknown';
    }

    /**
     * Check if MIME type is allowed
     *
     * @param string $mimeType MIME type to check
     * @return bool True if allowed
     */
    private function isAllowedMimeType($mimeType)
    {
        // Normalize MIME type
        $mimeType = strtolower(trim($mimeType));

        // Remove charset and other parameters
        if (strpos($mimeType, ';') !== false) {
            $mimeType = trim(substr($mimeType, 0, strpos($mimeType, ';')));
        }

        return in_array($mimeType, self::$_allowedMimeTypes);
    }

    /**
     * Sanitize filename for safe storage and download
     *
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename($filename)
    {
        // Remove path components
        $filename = basename($filename);

        // Replace potentially dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Limit length
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = substr(pathinfo($filename, PATHINFO_FILENAME), 0, 250 - strlen($ext));
            $filename = $name . '.' . $ext;
        }

        return $filename;
    }

    /**
     * Get human-readable upload error message
     *
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive specified in the form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Format bytes to human-readable string
     *
     * @param int $bytes Number of bytes
     * @return string Formatted string (e.g., "10 MB")
     */
    private static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
