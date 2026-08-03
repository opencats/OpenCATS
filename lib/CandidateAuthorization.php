<?php
/**
 * Shared authorization helpers for candidate object-level access checks.
 */

include_once(LEGACY_ROOT . '/lib/Candidates.php');
include_once(LEGACY_ROOT . '/lib/Attachments.php');

class CandidateAuthorization
{
    public static function getCandidate($candidateID)
    {
        $candidates = new Candidates();
        return $candidates->get($candidateID);
    }

    public static function canAccessCandidate($candidateID, &$candidate = null)
    {
        $candidate = self::getCandidate($candidateID);
        return self::canAccessCandidateRecord($candidate);
    }

    public static function canAccessCandidateRecord($candidate)
    {
        if (empty($candidate))
        {
            return false;
        }

        if ($candidate['isAdminHidden'] == 1 &&
            $_SESSION['CATS']->getAccessLevel('candidates.hidden') < ACCESS_LEVEL_SA)
        {
            return false;
        }

        return true;
    }

    public static function getAttachment($attachmentID)
    {
        $attachments = new Attachments();
        return $attachments->get($attachmentID);
    }

    public static function canAccessCandidateAttachment($attachmentID, &$attachment = null)
    {
        $attachment = self::getAttachment($attachmentID);
        if (!isset($attachment['attachmentID']))
        {
            return false;
        }

        if ($attachment['dataItemType'] == DATA_ITEM_CANDIDATE)
        {
            $candidate = null;
            return self::canAccessCandidate($attachment['dataItemID'], $candidate);
        }

        return true;
    }
}

?>
