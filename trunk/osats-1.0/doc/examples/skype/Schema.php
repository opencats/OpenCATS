<?php
/*
 * CATS
 * Company Demonstration Module Schema
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1 (the "License"); you may not use this file except in
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
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2006
 * (or from the year in which this file was created to the year 2006) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 * $Id: Schema.php 78 2007-01-17 07:38:53Z will $
 */

class SkypeSchema
{
    /* Each module can provide its own database schema updates. When CATS loads
     * the module for the first time, it checks the module's schema updates and
     * makes sure the CATS database is up to date.
     *
     * The schema updates are made up of an array, where the revision number is
     * the key, and the value is the code to execute to bring the database up to
     * that revision. The revision numbers increase with each update, and each
     * revision is associated with one or more SQL statements separated by
     * semicolons (;). Each statement must end with a semicolon.
     *
     * Internally, CATS has a counter storing what revision number each module
     * is currently on.  When CATS reads that update number 1 is provided by
     * the module and it has not been executed yet, CATS executes the SQL
     * command associated with update number 1 (in this case adding a column
     * to the site table). CATS will never execute this command again.
     *
     * If the user downloads a newer copy of the module, which then contains
     * revisions 2 and 3, CATS will not run the SQL code associated with
     * revision 1, but will execute the code associated with revisions 2 and
     * 3 to bring the database up to date.
     */
    public function get()
    {
        return array(
            /* Adds a column called skype_enabled to each site record. The column
             * will have a value of 0 if skype links are disabled, or 1 if they are
             * enabled.
             */
            '1' => "
                ALTER IGNORE TABLE `site` ADD COLUMN `skype_enabled` int(1) DEFAULT 1;
            "
        );
    }
}

?>
