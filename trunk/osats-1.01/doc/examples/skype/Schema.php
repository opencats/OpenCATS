<?php
/*
   * OSATS
   * Open Source License Applies
*/

class SkypeSchema
{
    /* Each module can provide its own database schema updates. When OSATS loads
     * the module for the first time, it checks the module's schema updates and
     * makes sure the OSATS database is up to date.
     *
     * The schema updates are made up of an array, where the revision number is
     * the key, and the value is the code to execute to bring the database up to
     * that revision. The revision numbers increase with each update, and each
     * revision is associated with one or more SQL statements separated by
     * semicolons (;). Each statement must end with a semicolon.
     *
     * Internally, OSATS has a counter storing what revision number each module
     * is currently on.  When OSATS reads that update number 1 is provided by
     * the module and it has not been executed yet, OSATS executes the SQL
     * command associated with update number 1 (in this case adding a column
     * to the site table). OSATS will never execute this command again.
     *
     * If the user downloads a newer copy of the module, which then contains
     * revisions 2 and 3, OSATS will not run the SQL code associated with
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