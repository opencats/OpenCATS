<?php

/*
 * ACL Library
 * @package OpenCATS
 * @subpackage Library
 * @copyright (C) OpenCats
 */

include_once("./config.php");

class ACL
{
    /* Constant to define root secured object for retrieveing access level
    */
    public const SECOBJ_ROOT = '';

    public const CATEGORY_EMPTY = '';

    /* Access level map in form securedObject => category => accessLevel
     * Example:
    );
     */

    /* Returns accessLevel to securedObjectName for user with userCategories
     * current implementation evaluates only first user category
    */
    public static function getAccessLevel($securedObjectName, $userCategories, $defaultAccessLevel)
    {
        if (! class_exists('ACL_SETUP') || empty(ACL_SETUP::$ACCESS_LEVEL_MAP)) {
            return $defaultAccessLevel;
        }

        $aclmap = ACL_SETUP::$ACCESS_LEVEL_MAP;
        $userCategory = ACL::CATEGORY_EMPTY;
        if (isset($userCategories) && count($userCategories) > 0 && isset($userCategories[0])) {
            // for now, only first category is used for evalualtion
            $userCategory = $userCategories[0];
        }
        if (ACL::_hasACLEntry($aclmap, $userCategory, $securedObjectName)) {
            return $aclmap[$userCategory][$securedObjectName];
        }

        while (($pos = strrpos($securedObjectName, ".")) !== false) {
            $securedObjectName = substr($securedObjectName, 0, $pos);
            if (ACL::_hasACLEntry($aclmap, $userCategory, $securedObjectName)) {
                return $aclmap[$userCategory][$securedObjectName];
            }
        }
        if (ACL::_hasACLEntry($aclmap, $userCategory, ACL::SECOBJ_ROOT)) {
            return $aclmap[$userCategory][ACL::SECOBJ_ROOT];
        }
        return $defaultAccessLevel;
    }

    public static function _hasACLEntry($aclmap, $userCategory, $securedObjectName)
    {
        return array_key_exists($userCategory, $aclmap)
            && array_key_exists($securedObjectName, $aclmap[$userCategory])
            && null !== $aclmap[$userCategory][$securedObjectName];
    }
}
