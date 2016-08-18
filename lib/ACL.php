<?php

/*
 * OpenCATS
 * ACL Library
 * author: 
 */

include_once("./config.php");

class ACL
{

    /* Constant to define root secured object for retrieveing access level 
    */
    const SECOBJ_ROOT = '';
    const CATEGORY_EMPTY = '';

    /* Access level map in form securedObject => category => accessLevel
     * Example:
    const CATEGORY_DISABLED = '#';

    const ACCESS_LEVEL_MAP = array(
        'recruiter' => array(
            'calendar' => ACCESS_LEVEL_DELETE,
            'candidates'=> ACCESS_LEVEL_DELETE,
            'candidates.add'=> ACCESS_LEVEL_DISABLED
        ),
        'candidate' => array(
            ACL::SECOBJ_ROOT => ACCESS_LEVEL_DISABLED,
            'news' => ACCESS_LEVEL_READ,
        ),
        ACL::CATEGORY_EMPTY => array(
            ACL::SECOBJ_ROOT => ACCESS_LEVEL_READ
        ),
        ACL::CATEGORY_DISABLED => array(
            ACL::SECOBJ_ROOT => ACCESS_LEVEL_DISABLED
        )

    );
    */
    const ACCESS_LEVEL_MAP = array();


    /* Returns accessLevel to securedObjectName for user with userCategories 
     * current implementation evaluates only first user category
    */
    public static function getAccessLevel($securedObjectName, $userCategories, $defaultAccessLevel)
    {
        if( empty(ACL::ACCESS_LEVEL_MAP))
        {
            return $defaultAccessLevel;
        }

        $userCategory = ACL::CATEGORY_EMPTY;
 	    if( isset($userCategories) && count($userCategories) > 0 )
        {
            // for now, only first category is used for evalualtion
            $userCategory = $userCategories[0];
            if( isset($userCategory) == false)
            {
                $userCategory = ACL::CATEGORY_EMPTY;
            }
        }

        if( NULL !== ACL::ACCESS_LEVEL_MAP[$userCategory][$securedObjectName])
        {
            return ACL::ACCESS_LEVEL_MAP[$userCategory][$securedObjectName];
        }
        else
        {
            while(($pos = strrpos($securedObjectName, ".")) !== false)
            {
                $securedObjectName = substr($securedObjectName, 0, $pos);
                if( NULL !== ACL::ACCESS_LEVEL_MAP[$userCategory][$securedObjectName])
                {
                    return ACL::ACCESS_LEVEL_MAP[$userCategory][$securedObjectName];
                }
            }
            if( NULL !== ACL::ACCESS_LEVEL_MAP[$userCategory][ACL::SECOBJ_ROOT])
            {
                return ACL::ACCESS_LEVEL_MAP[$userCategory][ACL::SECOBJ_ROOT];
            }
        }
        return $defaultAccessLevel;
    }

}
?>
