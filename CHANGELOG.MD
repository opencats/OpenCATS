

**0.9.3-3 (2016-11-22)**


**Closed issues:**

  * Powered by CATS pic on Careers Page does not appear correctly #139
  * While sending email to candidates the body field does not appear #131
  * Notice: Undefined variable: commandResult #105

**Merged pull requests:**

  * Generate packages with full dependencies on Travis #162 (mlespiau)
  * Extended xml export #158 (skrchnavy)
  * Logging into DB fix #157 (skrchnavy)
  * Remove references to pro version and links to downloads page #151 (cptr13)
  * Fix of attachments processing #149 (skrchnavy)
  * Fixes in db shcema #148 (skrchnavy)
  * Fixed Favicon.ico now showing #143 (amaisonneuve)
  * Powered By broken link #142 (amaisonneuve)
  * Upgraded ckeditor to current (version 4.5.11) to address #131 #132 (RussH)
  * Access level tests #125 (skrchnavy)
  * Update InstallationTests.php #124 (theSlyest)
  * Replace deprecated simpletest with phpunit and behat #123 (mlespiau)
  * Tests using behat isolated in test directory #100 (skrchnavy)

**0.9.3-2 (2016-11-22)**

**Closed issues:**

  * Feature Request - add a ‘is Hot?’ column to cold call list #161
  * 500 error while creating a company or job order #160
  * How can I access to my opencats web in the vps from another pc #146
  * Forgot Password link in the login screen missing #141
  * Content area turns blue when something is added #140
  * 500 Error after install #138
  * Error while creating http://localhost/opencats #129
  * Resume Upload error #126

**0.9.3-1 (2016-08-26)**

**Closed issues:**

No actions work against list members in 0.9.3 #121

**Merged pull requests:**

  * fixed send email and add candidates #122 (skrchnavy)

**opencats-0.9.3(final) (2016-08-22)**

**Closed issues:**

  * Troubleshoot MySQL #111
  * Non editable “paste the resume contents” field #104
  * Issue with vagrant #103
  * Windows installation #99
  * From Forums - Manuel’s attachment issue #95
  * asp File in module #69
  * ElementITMultiPowUpload1.7.swf #68
  * Unable to login #64
  * I need to change menu names #63
  * Sync from Uservoice #24
  * TODO: add in fixes from Debian packaging #10

**0.9.3 (2016-03-09)**

**Closed issues:**
  * Add JobOrder available for ReadOnly user on Job Orders page #109
  * Add Candidate available for ReadOnly user on Candidates page #107
  * Add Contact available for ReadOnly user on Contacts page #101
  * Cannot import any data. New Unit test? #86
  * Initial sign-in, cookie error #85
  * Tests do not run #70
  * Use fluid width pages #58
  * Update installation procedure #57
  * Initial password displayed in installer page is wrong #53
  * Plain Text Password #45
  * Decoding of UTF-8 characters mangles Calendar entries #42
  * update phpmailer to more recent version #37
  * disable the catsone.com version check #35
  * update exceptions table with two additional fields or it’ll throw errors #28
  * Include updated SVG artwork #21
  * Default username wrong #19
  * TODO: add in LDAP integration from LIBREGEEK #11
  * Incompatibility with Explorer 11 in the editor #2
  * datepicker returns the wrong default date when adding activities #1

**Merged pull requests:**
  * update version no / name to 0.9.3 Inferno #119 (RussH)
  * updated styling for login screen #117 (RussH)
  * Checks for access level #114 (skrchnavy)
  * Fix of permission in Job Orders for readOnly user #110 (skrchnavy)
  * Fix of permission in candidates for readOnly user #108 (skrchnavy)
  * Fix of permission in contacts for readOnly user #106 (skrchnavy)
  * Waffle badges in README #96 (skrchnavy)
  * fixes some typo errors #92 (skrchnavy)
  * docker - added execute permission for installing composr #90 (skrchnavy)
  * Wait for mysql and apache before running tests in travis #89 (mlespiau)
  * Issue 86 cannot import data after initial setup #88 (mlespiau)
  * Datagrid filters ui refactor and bugfixes #87 (mlespiau)
  * Fixed issues with UTF8 #84 (skrchnavy)
  * Mysql utf8 character set #81 (mlespiau)
  * Add cucumber like functional tests #80 (mlespiau)
  * Docker for development #78 (mlespiau)
  * Flexible layout #74 (mlespiau)
  * Ldap module improvements #73 (skrchnavy)
  * Fix unit tests #72 (mlespiau)
  * Replace GroupTest class with TestSuite as it was removed from Simplet… #71 (mlespiau)
  * Added grave accents, backticks, to database schema name #66 (aaam)
  * Update lib.js #62 (theSlyest)
  * Update suggest.js #61 (theSlyest)
  * Fixed #57 - Updated documentation. Fixed #45 - Added md5 encryption to passwords. #59 (libregeek)
  * Updated phpmailer library refer #37 #56 (vinay-athiyil)
  * LDAP authentication support and default password display #54 (libregeek)

**opencats-0.9.3 (2016-03-09)**

**Closed issues:**
  * OpenCATS vs CandidATS #46
  * Strict standards: Only variables should be passed by reference in D:\wamp\www\cats\lib\DataGrid.php on line 1714 #39
  * Error in config.php for email settings #34
  * Cannot generate report on CATS 0.9.2 #33
  * Error while trying to generate report from Jobs view #31
  * Cats freezes/stops working when creating candidate #30
  * Sync from Uservoice #27
  * Disable Modules #26
  * issues on UI application #22
  * CATS references in Install Wizard #16
  * Sync from Uservoice #15
  * Add in latest PHP fixes from crossgate9 #14
  * Add in updated and enhanced Sphinx Search from Allan Jones #13
  * When editing a company the fields at the right side isn’t lined up well #8
  * Missing Icon in calandar #7
  * Deprecated error in Opencats #5

**Merged pull requests:**
  * Update from RussH fork #49 (RussH)
  * Make CSV exports in UTF-8 readable for Excel #41 #48 (wjcheers)
  * Fix bug and add duplicated phone number check. #47 (wjcheers)
  * Removing the old superseded simpletest library #20 (RussH)
  * Updated SQL to ensure tag db is created. #18 (RussH)
  * Please merge Opencats fixes as below #17 (RussH)



$Id: CHANGELOG 3810 2007-12-05 19:13:25Z brian $

Original CATS ChangeLog
    _____________________________________________________________________

                               CATS ChangeLog

12/05/07
  * BH: Various changes to backup process and graph statistics for
        compatibility with older versions of MySQL.
  * BH: TinyMCE added to edit job order descriptions and notes.
  * BH: Added CATS Professional user class - Career Portal Customizer
  * BH: Added Job Order Datagrid column - Number of Not Contacted Pipeline

11/26/07
  * BH: Saved list management removed from edit candidate.
  * BH: Added field best time to call to candidates.
  * BH: PHP 4 doesn't generate a PHP error, but says PHP is out of
        date in installer instead.

11/22/07
  * BH: CATS checks PHP version when index.php loads now.  This prevents
        PHP 4 users from seeing a cryptic error message because they
        don't have the required functions for CATS.

11/21/07
  * BH: Accessing CATS from a different directory forces a logout of
        the current session.
  * BH: Minor style changes.

11/20/07
  * BH: List encoding fixed.  Even supports unicode characters!

11/19/07
  * BH: Extra fields are now renameable.

11/14/07
  * BH: Replaced old career portal with new Career portal by Sae.
  * BH: Extra fields are now reorderable.

11/13/07
  * BH: Filecompressor extraction works.
  * BH: Restoring from backup works.
  * BH: Restoring from demo works.

11/09/07
  * BH: Calendar can now send e-mail reminders to multiple recipients.

11/08/07
  * BH: Job Orders now tracks openings available and total openings separately.

11/06/07
  * BH: $_GET, $POST, and $_REQUEST's array keys are also handled by 
        stripslashes if magic quotes are on.  This fixes Saved Lists not 
        sortable with magic quotes on.
  * BH: Placing a candidate reduces number of openings by one.
  * BH: Public calendar entries do not show up on dashboard - just personal ones.
  * BH: Set event duration is added to add activity change status popup.  
        Adding an all day event disabled duration (both on popup and 
        calendar.)

10/31/07
  * BH/WB: Properly handle uppercase file extensions when converting documents.
           Thanks to MePHiSTY and hardik.panchal on the CATS Forums.

10/29/07
  * BH: Users can no longer commit data imports.  They loose the ability to
		revert an import after 1 week.
  * BH: Installer now shows progress while updating the schema.

10/25/07
  * BH: Star ranking can now be set to 0 stars (there is 3 pixels to the
        left of the first star to set 0 rank)
  * BH: Status negotiating is now status qualifying.
  * BH: Fix to datagrid.
  * BH/AK: No data images added throughout CATS.

10/19/07
  * BH: First beta of dashboard - still needs a few gui changes from Asim.

10/12/07
  * WB: Added basic sanity checking of query results to the S3 Storage module.
  * WB: Fixed a bug in the S3 Storage module where the resultset could be lost
        if the file was renamed due to bad characters in the filename.
  * WB: General S3 Storage module cleanups.

09/25/07
  * BH: Removing from saved list via action menu works.
  * BH: Page number less than 0 or more than total pages will constrain users 
        view to within the number of pages in a datagrid.
        
09/24/07
  * BH: Submodal is now a core javascript library, and is included in every file.
        PrintPopupArea is no longer necessary.
  * BH: Multiple items can be added to a list at once.
  * BH: Multiple candidates can be added to a pipeline at once.
  * BH: All popup windows in the system revised to be slimmer and have the title
        in the popup titlebar rather than inline in the popup content.
  * BH: All datagrids in the system now have an item count at the top of the
        datagrid.  (Indicating, for example, number of candidates in the system).
  * BH: Joborders, Companies, and Contacts now have static saved lists.
  * BH: Datagrid modified so a separate instance can be made of each saved list
        rather than shared layout preferences for each saved list.  Saved lists 
        can now be aware of what ID of a list (or other future data item, like a
        saved search) they are rendering.
  * BH: Lists are now added to MRU.
  * BH: Lists are quicksearchable.

09/13/2007
  * BH: ASP Password retrieval fixed.

09/07/2007
  * BH: Attachments are now unauthenticated files delivered through a PHP streamer
        function.

09/06/2007
  * BH: Action area all cleaned up to make it much easier to script in bulk
        actions on items in a datagrid.  For now, only get actions are supported,
        but ajax style popups will soon follow.

08/28/2007
  * BH: Added checkbox for "Only Hot Contacts" and fixed nonfunctional checkbox
        "Only My Contacts".
        
08/24/2007
  * BH: Fixed a horrible bug in attachments where files were not being deleted
        from the server when removed from the database.
  * BH: Added hooks to attachments to allow other modules to interact
         with the act of storing and retrieving attachments.

08/22/2007
  * BH: Various changes.
  * BH: Events can now have reminders set at time of event creation for candidates
        and contacts.

08/21/2007
  * BH: Added a document reindexer that will reindex any resume that has been
        added while a document to text converter for that datatype was unavailable.
        It is invoked at installer time.

08/14/2007
  * BH: Career portal now accepts URL's with unusual characters in JoborderID
        and filters them out.
  * BH: Activity dates/times now editable.
  * BH: Users can now optionally not see EEO information (enabled by default).

07/31/2007
  * BH: Job orders can now be created off of an existing Job Order.

07/25/2007
  * BH: Every E-Mail feature in CATS is now disableable.

07/23/2007
  * BH: Fixed an error restoring an ASP database and viewing its career portal.

07/12/2007
  * BH: If US Zipcode database is installed, users can filter by distance
        to a Zipcode.

07/10/2007
  * AK: Added a mail setup for the installation wizard.
  * AK: Can e-mail one or multiple candidates directly from the candidates
        data grid.

07/09/2007
  * BH: Some DataGrid fixes.
  * BH: Contacts tab is now a DataGrid.
  * BH: makeBackup CLI interface to backups now produces CATS installer
        compatible catsbackup.bak files.
  * BH: New version check is now mandatory.
  * BH: When CATS sees a new version, administrator users have a notice
        in the upper right area of a page where their user name is.
  * BH: Administrators have a bold 'Administrator' flag added to the
        upper right area of the page.
  * BH: Install documentation updated - Windows installation instructions
        simplified and separated from *nix instructions.
  * BH: New version check now collects number of users in use per site.
  * AB: Note Gradient style changed from blue to gray.

07/05/2007
  * BH: Some DataGrid fixes.
  * BH: DataGrid columns now are separated by gray resize bars.

07/05/2007
  * BH: DataGrid States now persist across a session.  If you navigate
        away from a DataGrid and come back, you will be on the exact
        same page with the same filters applied.
  * BH: Companies tab is now a DataGrid.

07/02/2007
  * BH: Job Orders Tab is now a DataGrid.
  * BH: DataGrid arguments can now support OR clause (status = OnHold OR Closed)
  * BH: DataGrid has a button to change number of rows per page.
  * BH: Filter area will not appear when the user clicks a checkbox
        or dropdown menu that invokes a filter (Only Hot Candidates)
  * BH: Minor DataGrid fixes.

06/27/2007
  * AK: Added an XML output module for career portal which exports
    	a live job feed in various job posting website formats (indeed,
    	simplyhired, etc.) You can access the feed by browsing to:
        	'http://my-cats-website.com/xml'
    	with the optional
        	'?t=<insert template name here>'
    	Template names can found in the xml_templates folder in modules/xml

06/21/2007
  * BH: Career Portal now supports EEO tracking.

06/19/2007
  * BH: Added HR mode.  At the moment, it is only enable through MySQL
		because of its alpha status.
  * BH: Added a site backup utility which can be called from CLI.
  * BH: Added support tab.

06/14/2007
  * BH: Invalid job order links on career portal no longer fatal -
		they simply forward the user to the job order list.

06/06/2007
  * BH: Filters now support less than and greater than operators on
		appropriate fields.
  * BH: Calendar entries now show entered by name.

06/05/2007
  * BH: DynamicPager replaced with DataGrid.  DataGrid is the successor
		to pager.
  * BH: DataGrid adds Filtering functionality to candidates and improved
		exporting.
  * BH: Changing a user's access level immediately takes effect on the
		users session.

05/31/2007
  * WB: Improvements to backup code.

05/28/2007
  * WB: Fixed a bug which caused Career Portal activity entries to not be
        editable.

05/25/2007
  * WB: AddressParser can handle multiword US and Canadian state/province
        names now.
  * WB: Refactored AddressParser phone number code to be more extendible.
  * WB: AddressParser can now handle labels such as "Address:" and "Name:'
        proceeding address information.
  * WB: Improved automated test cases for AddressParser.
  * WB: Moved StringUtility::isCityStateZip() into AddressParser.

05/23/2007
  * BH: Inactive candidates no longer show up under resume or keyword search
		results.
  * WB: Adding a candidate event no longer adds a blank activity entry. Thanks
        to ccladoulis on the CATS Forums.

05/16/2007
  * BH: Added reports to property to contacts.

05/15/2007
  * BH: Added pipeline graph to individual job order details pages.

05/11/2007
  * BH: Renamed siteUserID back to userID.
  * BH: Contacts Log Activity interface similar to modal popup Candidates use.
  * BH: Contacts can now schedule events.
  * BH: Added an option to schedule an event to Candidates and Contacts
        near the upcoming event areas.

05/10/2007
  * BH: Adding a candidate does a duplicate check on the E-Mail field
	    while the user is typing in candidate details.

05/04/2007
  * BH: Various small bugfixes.
  * BH: Added Radio Button / Date Extra field types.

05/03/2007
  * WB: Search code no longer fails with unmatched parenthesis or empty
        parenthesis.
  * WB: Search code handles AND in parenthesis properly now.
  * WB: Improved robustness of wildcard handling in search code.
  * WB: '!' is also accepted as a NOT operator by search code now.
  * WB: Added automated test cases for search code.
  * WB: Dashboard component parameters are now stored in the database as
        serialized arrays instead of broken CSV.
  * WB: Small DocumentToText.php cleanup.

05/02/2007
  * BH: Extra fields can be used on Career Portal Application page.

05/01/2007
  * WB: Fixed a caching issue when generating PDF files. Thanks to Michelle W.
  * WB: Dashboard GUI improvements.
  * WB: Refactored dashboard; module data is no longer stored in the database.
        This will make future changes a lot easier.

04/27/2007
  * BH: Refactored all extra fields display / add / edit code into
        ExtraFields.php.
  * BH: Added extra fields to Job Orders.
  * BH: Made new extra fields editor, removing the current list editor
        interface.
  * BH: Added new extra field types Textarea and Dropdown.
  * BH: Prevented extra fields from causing duplicate or empty field names.

04/26/2007
  * WB: Finished the last bit of refactoring to AttachmentCreator (FS#132).
  * WB: Added a middle name / initial field to candidates (FS#64). Thanks to
        pheedback on the CATS forums.
  * BH: Abstracted Extra Fields Classes into separate library.

04/24/2007
  * WB: Various XHTML fixes.
  * WB: Preparing for adding AddressParser to Companies.
  * WB: Add Candidate GUI improvements.
  * BH: Renamed Hot Lists to Saved Lists.
  * BH: Saved Lists are no longer dependent on a candidate being hot.
  * WB: Various Companies GUI fixes.

04/23/2007
  * WB: Start of Search code refactoring to allow for additional functionality.

04/22/2007
  * WB: Slight AttachmentCreator refactoring; made backup code use
        AttachmentCreator (FS#132).

04/21/2007
  * WB: Fixed a bug in FileUtility::makeRandomTemporaryFilePath().

04/20/2007
  * BH: Changed names of access levels to more descriptive equivalents.
  * BH: Considering a candidate for a pipeline entry shows last 5 modified JO's.
  * BH: Added recent status field to candidates pager.

04/19/2007
  * BH: Date / Time added to upper right corner.
  * BH: Address1/Address2/Address3 merged into Address memo field in candidates,
		companies, and contacts, as well as job board.
  * BH: Added candidate fields 'Current Salary' and 'Desired Salary'.

04/18/2007
  * WB: Renamed Zip Code to Postal Code on Add / Edit forms.
  * WB: More Attachments refactoring. Attachments::getUniqueDirectory() is now
        part of FileUtility.

04/17/2007
  * BH: Fixed hotlists to work with DynamicPager.

04/13/2007
  * WB: Address parser can handle a few more address types it couldn't parse
        before.
  * WB: Added more test cases to AddressParser.
  * BH: Added DynamicPager library, supporting dynamic column modification.
  * BH: Converted Candidates tab to DynamicPager interface.

04/12/2007
  * WB: Fixed a minor HTML escaping error in ModuleUtility::loadModule().
        Thanks to Brad W.
  * WB: AddressParser handles 5 digit PO boxes now. More general improvements
        to AddressParser as well.
  * WB: Misc. cleanups.

04/09/2007
  * BH: Added cron scheduler framework.
  * BH: Added e-mail reminders of pending events.

04/03/2007
  * BH: Moved import to settings.
  * BH: Disabled single resume import into the bulk resume pool.

04/02/2007
  * BH: Attachments now consider files with duplicate text to be duplicates.
		To invoke upgrade and remove duplicates, login as a root user and execute:
		ajax.php?f=install:attachmentsDeleteDuplicateContents.

03/29/2007
  * BH: Attachments now bury themselves a few more levels of directories down.
		To invoke upgrade, login as a root user and execute:
		ajax.php?f=install:attachmentsToThreeDirectory.

03/27/2007
  * BH: Placements link in reports now shows all placements.

03/21/2007
  * BH: EEO Statistics Preview Report is now available.
  * BH: EEO Statistics Preview Report now obeys filter rules.
  * BH: Checkbox extra fields sometimes weren't displaying right on candidates.
        Thanks to

03/20/2007
  * BH: CATS can now store basic Equal Employment Opportunity statistics.

03/15/2007
  * BH: You can now click on the active sub-tab.
  * BH: Merged tables calendar_settings and mailer_settings into settings.

03/14/2007
  * BH: Small fixes.
  * BH: Added a 'do not change copyright' notice to places that implement parts
        II and III of CPL exhibit B.
  * BH: Small fix to Backup code.
  * BH: Source field added to possible fields on career application page.
  * BH: Validation fix for ContactsUI.

03/13/2007
  * BH: Renamed Job Board to Career Portal.  There should be no more mentions
        of the term 'Job Board' in the system.

03/08/2007
  * BH: Merged tables *_foreign and *_foreign_settings into extra_field
        and extra_field_settings.

03/07/2007
  * WB: Renamed Session::getDatePreferenceDMY() to isDateDMY().
  * WB: Lots of documentation.
  * WB: Renamed Session::updateLoginHistory() to updateLastRefresh().
  * WB: MRU objects don't get created every time they are accessed anymore. The
        MRU object is now stored in the session.
  * WB: Session::getCachedBuild() now reads the build number first if it isn't
        cached.
  * WB: General Session cleanups.
  * WB: Renamed Session::setTimeDatePref() to setTimeDateLocalization().
  * WB: Removed getBackupDirectory() and setBackupDirectory() from Session.
        storeValueByName() / retrieveValueByName() are now used instead.

03/06/2007
  * BH: Dropped timecard module, table.
  * BH: Renamed user table to site_user.
  * BH: Renamed alien_id and assoc_id on each extra field / extra field
        settings table to relevant names.
  * BH: Dropped legacy column schema_version from system table.
  * BH: Dropped table work_status_type.

03/05/2007
  * BH: CATS now gets its E-Mail settings from config.php.

03/01/2007
  * BH: Fix to isDirectoryWritable. Thanks to stepan on the CATS forums.
  * WB: Various improvements to Attachments code. Security improvements, etc.
  * BH: Removed Cognizo Technologies link from dashboard.

02/28/2007
  * WB: Merged PDFToText and DocumentToText.
  * WB: Attachments were totally broken on Windows due to magic quotes; fixed.

02/27/2007
  * WB: International phone numbers now keep their original formatting. Thanks
        to Owen F. for reporting this.
  * BH: Attachment file names now change any character not part of the standard
        ASCII character set to underscore characters for compatability across
        different environments.

02/26/2007
  * WB: Added validation for Add Activity / Change Status.
  * BH: Put a safety feature on list editors that prevents CATS from
        deleting records if a javascript error occurs and clears
        the hidden CSV field on a page.
  * BH: Mass resume import revised to simply display a progress indicator
        rather than a parsing result for every single file.
  * BH: Added a backup mode that only outputs attachment files.

02/25/2007
  * WB: Fixed a spelling error in a login error message.

02/24/2007
  * WB: Sped up the framework a bit by removing a MouduleUtility dependency
        on FileUtility.

02/23/2007
  * BH: Templates updated to make centering the job board look the same on
        IE and Firefox.

02/22/2007
  * BH: Mass resume import break up parsing into 150 file chunks.
  * BH: Mass resume import recursively reads directories.
  * WB: Small AJAX framework fixes.

02/21/2007
  * WB: Export module cleanups.
  * WB: Fixed an SQL injection vulnerability in the Export module.
  * BH: Various fixes.
  * BH: Added mass bulk resume import. Relies on user uploading documents
        to /upload.
  * WB: Converted CATS to use XHTML 1.0/strict doctype.
  * BH: Added UNIX support for XPDF's pdftotext.

02/19/2007
  * BH: Import GUI revised.
  * WB: Fix to Search.php fulltext translation.
  * WB: Fixed a bug where CATS can incorrectly think session.auto_start is
        enabled when it isn't. Thanks to khubla on the CATS Forums.
  * WB: Search keyword highlighting now properly handles quotes.
  * WB: Renamed asp_notYetAgreedToTrial.tpl to asp_notAgreedToTrial.tpl.

02/18/2007
  * WB: More cleanups.
  * WB: Better duplicate keyword handling for non-Sphinx search.
  * WB: Moved rating images to images/stars/.
  * WB: Fixed an undefined variable error in JobOrderReport.tpl.
  * WB: Fixed a PHP error caused when a job order report is passed invalid
        pipeline values.
  * WB: Split Export into Export and ExportUtility. This design was totally
        messed up.
  * WB: Renamed DatabaseConnection::makeQueryID() to makeQueryInteger().

02/17/2007
  * WB: Misc. cleanups, library robustness fixes, safe-query fixes, etc.

02/16/2007
  * WB: Small fix to login tests to account for E-Mail Settings Disabled
        Initial Configuration Wizard screens.
  * BH: Added an internal setting to career portal configuration allowing
        CATS to use a different html template (blank2.tpl).
  * WB: Renamed catstoolbaroptions.* to options.* in toolbar content/.
  * WB: Fixed two Login module PHP errors that weren't easily visible.
  * WB: Added an update.rdf to toolbar.
  * WB: Fixes to toolbar install.rdf.
  * WB: More Attachments / Backup cleanups.
  * WB: Added Attachments::setSizeMD5() to support backup functionality.
  * WB: Misc. robustness fixes to Attachments code.
  * WB: Candidates cleanups.

02/15/2007
  * WB/BH: ~50% performance improvement to the main Candidates listing by
           removing the need for a derived table.
  * WB: Removed references to two JavaScript files that don't exist anymore.
  * BH: Job order view of pipelines # of entries per page persists and is saved
        for future sessions.

02/14/2007
  * WB: Fixed a fatal query error when selecting "Only My Contacts" on the
        Contacts page. Thanks to gotblue and jos on the CATS Forums (FS#133).
  * WB: Misc. cleanups.
  * BH: Added possible field key skills to apply for job position on career
        portal.
  * BH: Rearranged the client selection area on add/edit job orders. Validation
        always works now.
  * WB: Fixes missing var keywords in JavaScript libraries.
  * WB: Toolbar can now determine phone number types.
  * WB: Rewrote FileUtility::sizeToHuman(); supports rounding and unit offsets.
  * WB: We can now handle magic_quotes_runtime being on.
  * WB: Fixed a few magic_quotes_gpc issues. Thanks to Wonder on the CATS
        Forums.
  * WB: Added unit test cases for FileUtility::sizeToHuman().

02/13/2007
  * WB: AttachmentsCreator can now create attachments from text.
  * BH: Uploading a resume from modal add candidate now works.
  * BH: Pipeline star rankings were sometimes broken when more than
        15 entries in job order pipeline, fixed.
  * WB: More AttachmentsCreator refactoring (FS#132).
  * WB: Career portal master template Powered by CATS link fix.
  * WB: Improved non-Sphinx resume search speeds by around 700% with 3k
        attachments by using POSIX word boundaries in regular expression
        keyword searching.

02/12/2007
  * BH: Enabled freelance type.
  * BH: Schema update - adds MD5 and filesize fields to attachments,
        populates existing attachment records.  This could take awhile.
  * BH: Attachments now don't attach duplicate files to a record.
  * BH: Bulk Resume Import 50% done; needs GUI for importing resumes.
  * BH: "Bulk" import for single resumes complete.
  * WB: Added Sphinx full text indexing support to CATS.
  * WB: Non-Sphinx resume searching speed improved by limiting the amount of
        records returned internally for each page.
  * WB: Renamed lib/BooleanDB.php to DatabaseSearch.php.
  * WB: Removed the Developer access level.
  * BH: Bulk resumes now have their own data item types.
  * BH: Merged code from AttachmentsCreator::createFromUpload() and
        AttachmentsCreator::createFromFile() to make createGeneric() (FS#132).

02/11/2007
  * WB: Job Order Report cleanups.
  * WB: Fixed some serious timezone issues. Things aren't totally fixed yet...
  * WB: Misc. GUI fixes.
  * WB: Timezone localization fixes; date on 'Add Event' is now correct.
  * WB: Renamed all instances of Add Activity to Log Activity for consistency.
  * WB: InfoString cleanups.
  * WB: All test cases pass.

02/10/2007
  * WB: Cleanups to js/backup.js.
  * WB: Cleanups to index_updating.php.
  * WB: Small HTTPS fix to CATSUtility.php.
  * WB: Added line wrapping guidelines to doc/DEVELOPMENT-GUIDELINES. We need
        some consistency here (and I like my way =P). The most important thing
        is clarity, and wrapping in a way that the existing braces / brackets /
        parenthesis are used to show structure provides a lot of clarity.
  * WB: Mailer works with E_STRICT now (PHPMailer is PHP4 compatible).
  * WB: Misc. E_STRICT fixes.
  * WB: Small Calendar.tpl cleanup.
  * WB: Lots of toolbar cleanups and possible error fixes. Fixed a few of
        variable scope issues, especially in for loop declarations, etc. XUL /
        GUI updates. Password input in options dialog is now *'d out.
        (typeof(DOMVariable) != 'undefined') -> !DOMVariable.
  * WB: Toolbar mostly follows CATS style conventions now.
  * WB: Regenerated toolbar XPI.
  * WB: Small User Management GUI fix. Thanks to meg on the CATS Forums
        (FS#131).
  * WB: Added CATSUtility::getFilteredGET().
  * WB: Misc. cleanups and fixes to modules/settings/ajax/backup.php. Fixed a
        divide-by-zero error.
  * WB: Renamed MSWordToText to DocumentToText; start of multiple resume format
        support.
  * WB: SearchPager cleanups.

02/09/2007
  * BH: Candidate responded no longer generates an E-Mail by default.
  * BH: Added a 'FL' (Freelance) job order type hidden to all sites.
  * BH: Added a few more possible fields to job order details on candidate
        portal.
  * BH: Toolbar updates - can now parse monster resume PIM data.
  * BH: Recruiter gets an E-Mail now when a candidate applies.
  * BH: Changed E-Mail Subject for Status Change E-Mail.

02/08/2007
  * BH: Changed toolbar to have non JS eval() callback functions.  Now more
        secure.
  * WB: Added DatabaseConnection::makeQueryDouble().
  * BH: CATS no longer records pipeline added or rating changed.

02/07/2007
  * WB: Start of refactoring attachment creation code / file upload handling
        into AttachmentCreator (FS#132).
  * WB: Renamed Attachments::getAttachmentDir() to getUniqueDirectory().

02/06/2007
  * WB: Start of 0.8.0.
  * WB: Completely removed is_beta_tester user flags.
  * BH: Toolbar installer added. Invoke with m=toolbar&a=install.
  * WB: Start of renaming Job Board to Career Portal.
  * BH: Career portal fix where recruiters were getting the wrong job order url.
  * BH: Career portal formatting fix for IE.
  * BH: Can now copy/paste resumes, and upload resume while adding a candidate.

02/05/2007
  * WB: Fixes to localization settings date format dropdowns to retail the
        correct value.
  * BH: Toolbar beta added. Code updated to have a preferences dialog
        and cache server side provided JS rules.

02/04/2007
  * WB: Misc. cleanups.
  * WB: Removed 'Beta Tester' code.
  * WB: Documentation.

02/02/2007
  * WB: UserInterface::fatalModal() should not have been used in the career
        portal; fixed.
  * WB: Subject lines for career portal e-mails are now configurable in
        config.php.
  * WB: Lots of career portal cleanups / template fixes.
  * WB: Calendar events no longer lose their data item associations on Edit.
        Thanks to jmtoh on the CATS Forums (FS#127).
  * WB: Fixed a few errors that could occur if index.php is renamed.
  * WB: Misc. cleanups.
  * WB: Calendar events added from Add Activity / Change Status properly
        save descriptions now. Thanks to jmtoh on the CATS Forums (FS#128).
  * WB: Show 'All Day' instead of 'None Set' for durations in calendar events.

02/01/2007
  * BH: Removed import candidate link from candidate tab.
  * WB: E-mail template fixes.
  * WB: New demo data.
  * WB: Small CareersUI cleanups.
  * WB: More schema spelling errors. :(

01/31/2007
  * WB: Updated INSTALL documentation for 0.7.1.
  * WB: Fixed a date input localization bug. Thanks to punchesboomboom on the
        CATS Forums (FS#116).
  * WB: Automated Testing Framework Reporter shows PHP errors now (FS#118).
  * WB: Added unit test cases for DateUtility::convert() and validate().
  * WB: Added a missing include in lib/EmailTemplates.php.

01/30/2007
  * WB: INSTALL documentation fix. Thanks to pheedback on the CATS Forums.

01/26/2007
  * WB / BH: Fixed a possible fatal error on the Add Job Order page caused by
        a lack of proper JavaScript Client validation.

01/24/2007
  * BH: CATS now saves / displays who created a pipeline entry.
  * WB: Small fix to e-mail address parsing.
  * WB: Small fix to DatabaseConnection error handling.

01/23/2007
  * WB: Wrong header in calendar/Error.tpl; fixed. Thanks to Infusion on the
        CATS Forums (FS#98).

01/22/2007
  * WB: GUI fixes to Job Board Settings page.
  * WB: Job Board Settings works in IE7 now. The best that I can tell is this
        is an IE7 bug. document.getElementByID() seemed to be having a
        "duplicate ID" issue when two inputs had the same *NAME* yet different
        IDs. Very strange.
  * WB: Integrated the RSS module with the public job board. You can now use
        http://www.mycompany.net/cats/rss/ to view an RSS feed of all public
        job orders, with URLs pointing to that job's job board page.
  * WB: Misc. site ID / URL fixes.
  * WB: TemplateUtility cleanups / documentation.
  * WB: Misc. GUI fixes.
  * WB: Settings->User Management GUI fixes.
  * WB: More IE7 GUI fixes (Add/Edit Contact/Client).
  * WB: CATS should work properly with HTTPS again. Thanks to libregeek on the
        CATS Forums (FS#102).
  * WB: W3C compliance fixes.
  * WB: Job board input validation fix.
  * WB: Misc. job board cleanups.

01/21/2007
  * WB: Template cleanups.

01/18/2007
  * Start of 0.7.1.
  * WB: Reordered some of the runtime tests in index.php.
  * WB: Small fix to code counting script.
  * WB: Lots of job board fixes. Thanks to alexukie and skibumm101 on the CATS
        Forums (FS#100).
  * WB: Use proper encoding on job board pages.

01/17/2007
  * WB: Localization form inputs have a name attribute now.
  * WB: User Management was still called Manage Users in a few places.
  * WB: Schema fixes.
  * BH: Changed layout of Job Board template editor to work with altered
        CATS color scheme.
  * WB: Small Add / Edit User fixes.
  * WB: Small fix to upgrades in Installer.
  * WB: Departments are now populated when adding a job order from the Client
        perspective.
  * WB: Misc. template fixes.
  * WB: Job Orders' modified dates are now set to their created dates when the
        job order is initially created.
  * 0.7.0 released!

01/16/2007
  * WB: Show an indicator while database connectivity is being tested in
        installer.
  * BH: Time zone constants updated.
  * WB: Cleanups to DatabaseConnection.php; always use connection handle for
        mysql_error().
  * WB: More additions to qc.sh.
  * WB: Misc. cleanups.
  * BH: All sites can use job order PDF report generator.
  * BH: Minor touchups.
  * WB: Lots of installer cleanups and fixes.
  * WB: Focus on wildCardString field on load on search pages.
  * WB: Better default "search by" dropdown selections on search pages.
  * WB: Small installer fix.
  * BH: Installer no longer says default Windows Antiword path on UNIX.
  * WB: Updated Exhibit B in the CATS Public License to allow for exemption
        from certain terms with the express written permission of Cognizo
        Technologies, Inc.
  * WB: 2007 copyright updates.
  * WB: All tests pass now.
  * WB: Finished renaming "Profile Settings" to "My Profile".
  * WB: ENABLE_HOSTNAME_LOOKUP off by default; it's too slow.
  * WB: Added StringUtility::extractAllPhoneNumbers().
  * 0.7.0 RC1 Released.

01/15/2007
  * BH: E-Mail templates can now be in UK format.  Thanks to Gavin S.
  * BH: Abstracted out the function that gets the local timezone into
        DateUtility. More date math abstractions should probably be
        done in the future.
  * BH: getTimeZoneOffset works if the session is not logged in now,
        removing the need for a check to isLoggedIn every time it is
        used in a lib function.
  * BH: More obvious that all day calendar events have no duration.
  * WB: Fixed Advanced Search button order. The first button is now 'Search'
        not 'Simple'.
  * BH: Time zone option list prioritizes US / Canada time zones or first
        region in the option list for a time zone.
  * BH: Fixed bug: Advanced Search Parser Broken after clicking advanced
        search twice.
  * BH: Fixed bug: Search Contacts JavaScript Error.
  * BH: Added "Powered by CATS" on Job Board Pages (FS#61).
  * BH: Fixed Hotlists Submitted Issue;hot lists submitted logic incorrect
        (FS#56).
  * BH: Fixed bug where files couldn't attach to a client.
  * WB: Initial Configuration Wizard pages redesigned; message text made more
        user-friendly, etc.
  * WB: Added new timezone selection dropdowns to Initial Configuration Wizard
        (FS#51).
  * WB: Removed orphaned "Search Failure Mode" code.
  * WB: Misc. CSS fixes.
  * BH: Read Only users can't modify hot lists anymore.
  * BH: Merged getAbsoluteURI and getBaseURL.
  * BH: Install wizard touchups.
  * WB: CHANGELOG fixes.
  * WB: Added new timezone selection dropdowns to Settings->Localization
        (FS#51).

01/14/2007
  * WB: Fixes to dashboard title bar preview.
  * WB: An empty dashboard title bar displays properly now.
  * WB: Fixed JavaScript errors in Add Activity / Change Status dialog.
  * WB: GraphGenerator now allows an image format to be specified.
  * WB: Fixed an obscure job order PDF report bug. Thanks to helphand on the
        CATS Forums.
  * WB: More Customize Dashboard improvements.
  * WB: Fixed references to nonexistent file "ajax.js".

01/13/2007
  * WB: Small HTML / JavaScript fixes to the Job Order Report page. Thanks to
        helphand on the CATS Forums.
  * WB: Fixes to Add Job Order when clicking the Add Job Order link from a
        Client Details page; used to result in PHP error.
  * WB: Several minor Dashboard GUI fixes.
  * WB: "Top Right" section wasn't being shown; fixed.
  * WB: A few GUI changes:
          * Bright Whites changed to very light greys to reduce contrast.
          * White->Blue gradient changed to a White->Blue->White gradient to
            make page flow better visually.
  * WB: Calendar padding fixes; this was really bothering me.
  * WB: Added a new more professional looking sample image to the dashboard
        customizer.
  * WB: Lots of work on Customize Dashboard; new sample images, GUI updates,
        etc.
  * WB: Fixed more spelling errors ;) .
  * WB: Use short CSS colors (#000 instead of #000000) to save bandwidth.
  * WB: Misc. CSS cleanups.
  * WB: Small installer GUI fix.

01/12/2007
  * WB: Fixes to Add Client's layout.
  * WB: Small fixed to layout of Add / Edit Contact and Edit Client.
  * WB: Lots of GUI fixes to installer.
  * WB: Cleaned up test output. It is shorter visually now, which helps a lot
        with visually scanning the test output.
  * WB: Added lots more unit tests.
  * WB: Time zone dropdown in the install wizard looks much nicer now.
  * WB: Browser detection code handles more browsers now; fixed detection of
        a few existing ones.
  * WB: Simplified DHTML popup window code a bit; HTML for showPopWinHTML()
        is passed as a string instead of a function pointer now.
  * WB: Installer's optional components info dialog is a modal window instead
        of an alert() now.

01/11/2007
  * BH: Fixed bug where inserting a null value into the calendar would cause
        fatal database error. Thanks to dl on the CATS Forums.
  * BH: getWeekNumber now considers logged in sessions time zone preferences.
  * BH: Updated install documentation.
  * BH: Edited release generator script to be slightly more interactive.
  * BH: Antiword testing / configuration added to installer.
  * WB: Fixed formatting of date format option select dropdowns.

        Before:
            MM/DD/YYYY
            DD/MM/YYYY

        After:
            MM-DD-YYYY (US)
            DD-MM-YYYY (UK)

  * WB: All instances of 'pipelineID' are now renamed to candidateJobOrderID
        for consistency.
  * WB: Cleanups to getPipelineDetails AJAX function; no more unnecessary
        dependence on TemplateUtility.
  * WB: Added Pipelines::getCandidateJobOrderID() to retrieve a
        candidateJobOrderID from a jobOrderID and a candidateID.
  * WB: Dates are now properly formatted in AJAX activity entry details.
  * WB: Adding tests to the testing framework's test selector is easier now.
  * WB: Lots more automated AJAX testing. All AJAX test cases pass.
  * BH: OFFSET_GMT reversed (* -1).

01/10/2007
  * WB/BH: More installer cleanups.
  * WB: Corrected various spelling errors throughout the application.
  * WB: qc.sh checks for common spelling errors now.
  * WB: Small CSS fix for initial configuration wizard pages.
  * WB: Resolved a few test class name conflicts in the testing framework.
  * BH: Minor bugfixes.
  * BH: Removed and added some pages to the administration settings template.
  * BH: Modified backup script to omit user_login table data, set demo user
        to SA.
  * WB: Lots of improvements to makeRelease.sh.
  * BH: Updated testdata.
  * WB / BH: Added DateUtility::getWeekNumber() to return the number of the
             current week out of the current year, with weeks starting on
             Sunday.
  * WB / BH: Don't use date('W') to get the current week number; use the new
             DateUtility::getWeekNumber() instead.
  * WB / BH: Fixed week number calculation for Calendar's week view.

01/09/2007
  * BH: On demand backups work in IE6 now.
  * BH: Timezone preferences carried over in transparent login now.
  * BH: Removed JS sort from job order pipeline - now only uses pager sort.
  * WB: More work on installer GUI.
  * WB: Installer reuses InstallationTests::checkMySQL() now.
  * WB: Misc. installer cleanups.
  * WB: AJAX function install:ui now uses $_REQUEST instead of $_POST for
        better testability.
  * WB: Added missing type attributes to <script> tags.
  * WB: Clicking "Test Database Connectivity" no longer keeps appending more
        test output to the page; replaces existing output instead.
  * WB: Installer test cases can trigger warnings now.
  * WB: Renamed hideAllSpanBoxes() to hideDivsWithin() in install.js.
  * WB: Moved changeConfigSetting() from install/ajax/ui.php to CATSUtility.
  * WB: Made CATSUtility::changeConfigSetting() a lot more robust with handling
        permissions errors.
  * WB: More installer cleanups; start of refactoring actions into a switch
        (not complete).
  * BH: Renamed employment folder to careers.
  * BH: Localization settings don't break installer on new schema.
  * WB: Pipeline graph cutting looks slightly better now. We still want a
        jagged line, but I didn't have time to figure that out yet; just a
        straight 2px white line for now.
  * BH: Fixed some domain related issues with job board.

01/08/2007
  * WB: GUI redesign of installer (work in progress).
  * WB: Misc. installer fixes (work in progress).
  * WB: Small login.css cleanup.
  * WB: Separated installer CSS out into modules/install/install.css. This is
        going to be needed by another file tomorrow.
  * WB: Shrunk the whitespace between the CATS logo and the login box on login
        pages very slightly. It looked a bit off.
  * BH: Modifying source field now updates source field on existing candidates
        using that source. Thanks to trivers on the CATS Forums.

01/07/2007
  * WB: Made AJAXInterface::isRequiredIDValid() a bit more robust.
  * WB: Lots of test case fixes and new test cases.

01/05/2007
  * BH: Time/Date localization settings in installer.
  * BH: Time/Date localization settings in site administration.

01/04/2007
  * BH: CATS Now parses text resumes.
  * BH: Timezone / date formatting for different timezones implemented.
        Still need GUI for editing timezone in session / installer.
        NOTE: config.php updated.
  * BH: Graph glitch at end of week fixed.

01/02/2007
  * BH: Beta of candidate portal complete, all features work. Enter ting
        testing stage.

12/30/2006
  * WB: Removed Users::updateSelf(); duplicated code. The access level
        parameter is now optional to Users::update().

12/29/2006
  * WB: Fixed a bug where changing a user's access level to Disabled caused
        an SQL error due to invalid use of makeQueryStringOrNULL().
  * WB: Fixed more invalid usage of makeQueryStringOrNULL() in Users.php.

12/28/2006
  * BH: Added candidate pipeline screening code.
  * BH: Candidate portal applications work.
  * BH: Added 2 new E-Mail templates for sending to an applied candidate
        and a recruiter when a candidate uses the portal.
  * BH: Added a new screen to the Job Board template for 'thanks for your
        submission' style messages.

12/27/2006
  * BH: Minor touchups.
  * BH: Installer upgrade fixed, tested.
  * BH: More work to candidate registration.

12/26/2006
  * WB: Improved several of the error messages output by installtest.

12/23/2006
  * WB: Major installtest improvements. Factored out actual tests in a way
        to allow installer to reuse tests without output buffering hacks.

12/22/2006
  * WB: Eliminated messy getdate() usage in Activities module / DateUtility.
  * WB: Added DateUtility::addDaysToDate() method.
  * WB: DateUtility::subtractDaysFromDate() now uses timestamps rather than
        getdate() arrays, as does DateUtility::addDaysToDate().

12/21/2006
  * WB: Added CATS_ADMIN_SITE constant. Special system-wide data is stored
        under the CATS_ADMIN_SITE site_id.
  * WB: Misc. cleanups.

12/20/2006
  * WB: Renamed JobBoard::set_template() to setForTemplate().
  * WB: Documented Attachments::getAttachmentDir().
  * BH: Accidentally deleted job board schema about 10 revisions back. Fixed.
  * BH: Job Board Progress Added - now has a list of all public job
        orders and descriptions.
  * BH: Misc. cleanups.
  * BH: Added DatabaseConnection::makeQueryID().
  * BH: Removed commented out zip code database upgrade script.

12/19/2006
  * BH: Candidate portal template picker, editor works.
  * WB: Removed orphaned code from old schema wizard.
  * WB: Major cleanups to the settings module and install schema file.
  * WB: Other misc. cleanups.

12/18/2006
  * BH: Internal postings client name shortened.
  * BH: More work to candidate portal template editor.
  * BH: Added career portal module framework.

12/14/2006
  * BH: Some installer bugfixes.
  * BH: cats_schema.sql brought up to date for version 0.7.
  * BH: Installer now creates a CATS session and forces CATS to perform
        self maintenance scripts prior to saying install completed.
  * BH: Test data sql file removed, replaced with cats_testdata.bak.
        cats_testdata.bak is based off of catsone.net demo data, with
        confidential login passwords removed and some useless job orders
        removed.
  * BH: Installer: Restore from backup works now.

12/13/2006
  * WB: Reverted accidental config.php changes; please review diffs before
        all commits to ensure that no accidental changes are made.

12/12/2006
  * BH: Added geographical location data to zipcode tables.
  * BH: Modified schema array pairs system to allow for upgrading with PHP
        code. See modules/install/Schema.php revision 3 for an example.
  * BH: Started work on public job board (schema, settings editor completed).
  * BH: Not in Consideration no longer implies submission status (or any other
        status).
  * BH: Attachment directory names that are not a random MD5 sum will get
        automatically MD5ed.

12/11/2006
  * BH: Fixed a particularly nasty bug where server side generated javascript
        wasn't being executed in Internet Explorer (affecting installer and
        backup functions).
  * BH: Minor touchups.

12/08/2006
  * BH: New edition of installer beta ready. Still missing restore from backup
        screens.
  * WB: Added session_name() call to prevent session interference. Thanks to
        helphand on the CATS Forums (post 1602).

12/05/2006
  * BH: Fixed bug: Sorting breaks when changing page in ajax pipeline.
  * BH: Fixed bug: Some missing file references removed or fixed.
        Thanks to helphand on the CATS Forums (post 357).
  * BH: Fixed bug: Activity graphs data incorrect on Sundays.
        Thanks to helphand on the CATS Forums (post 354).

12/04/2006
  * BH: Backups on demand completed.
  * BH: Minor changes.

12/03/2006
  * WB: Small fixes to AJAX filters / hooks.

12/01/2006
  * BH: Did some work on GUI for backups on demand. Backups now produce
        schemas that can restore a single site in both CATS importable
        and MySQL importable form.

11/30/2006
  * BH: Installer checkpoint. GUI, workflow redesigned. Doesn't do anything
        after database connectivity yet.

11/28/2006
  * BH: Deleted download proxy feature, now obfuscating files locations with
        MD5 sums to prevent iteration.
  * BH: Updated schema updater script to lock the database while making
        updates.

11/27/2006
  * BH: WIP of the installer script.
  * BH: Upgrade for schema 0.6 to 0.7 added. From now on, add all schema
        changes either under modules/install/Schema.php or in a schema
        for the module that the schema file relates to.
  * WB: Created a general-purpose string encryption class (lib/Encryption.php)
        using PHP's mcrypt module.

11/24/2006
  * WB: Added StringUtility::CSVLineToArray() for CSV parsing.

11/21/2006
  * BH: Hooks code doesn't throw errors when there is no session now.
  * BH: Started work on install wizard.

11/20/2006
  * BH: AJAX pager now works with filters and hooks; job order pipeline
        has all the same features it did before its AJAX upgrade.
  * BH: Brought default AJAX timeout up to 15 seconds.

11/19/2006
  * WB: More Pager / SQL tweaking.
  * BH: Job order's pipeline table doesn't have pager controls if less than
        15 entries visible.

11/18/2006
  * WB: Fixed a small table wrapping bug on Show Job Order's Pipeline table.
  * WB: Major cleanups to Pager navigation code. The (fairly expensive)
        navigation queries are now cached so that the second navigation bar on
        each page doesn't require a separate query. A unnecessary level of
        nesting has also been removed from each of these navigation queries,
        which itself gave noticeable performance improvements in my testing.
        On my sample data, with apache-bench, this resulted in a 48% decrease
        in load times for the candidates page (~2500 candidates in the system).
        Results are more noticeable with larger datasets.

11/17/2006
  * BH: Added some <nobr> tags to fields similar to recruiter / owner name.
  * BH: Pricing information now included on catsone.net
  * WB/BH: Added leading-whitespace filtering to the template engine. This
           gives us almost a 25% bandwidth reduction.
  * WB: Renamed StringUtility::makeFirstInitialName() to makeInitialName().
  * WB: StringUtility::makeInitialName() no longer inserts an extra comma
        between the first initial and the last name, and first name truncation
        now works properly. Also added a parameter to allow it to still operate
        in "LastName, FirstInitial." mode (no option yet).
  * WB: SVN revision parser can now handle format version 7 and 8 'entries'
        files, which are non-XML.
  * BH: Pipeline table for job orders converted to AJAX based table.
  * WB: Fixed pipeline-details centering problem in IE7.
  * BH: AJAX pipeline table now limits number of entries.
  * BH: AJAX pipeline table now has page navigation.
  * BH: Anti caching feature applied to css files now as well.
  * BH: AJAX pipeline uses JS sort if all pipeline entries are visible.
  * BH: AJAX pipeline whitespace removed.
  * WB: IE7 GUI fixes to data tables.
  * WB: A few GraphsUI and SystemInfo cleanups.

11/16/2006
  * BH: Pipeline entries now have a detailed view. The only thing in detailed
        view right now is activity history.
  * WB: Pipeline table HTML cleanups.
  * WB: js/sorttable.js can handle the pipeline tables now by sorting in
        "pairs" of rows. This is enabled by using the class name "sortablepair"
        for the table.
  * WB: Show "(No Notes)" under Pipeline Details if notes are empty.
  * WB: Removed 'Failure URLs' from DatabaseConnection.php. Queries should
        never fail, and when they do, we want to know exact error messages to
        be able to fix. They had been disabled anyway.

11/15/2006
  * BH: Fixed commit 1180 from yesterday.
  * BH: CATS watches the SVN revision number (if it exists) and forces reload
        of module list and hooks if the current session contains data populated
        from an earlier revision.
  * BH: Fixed JavaScript caching problems by appending the revision number to
        JavaScript URLs as a parameter.
  * BH: First initial last name pairs changed to first name last initial pairs.
  * WB: CATS build number is now parsed from SVN metadata; no more .build!
  * WB: Call session_write_close() before files are downloaded in download.php.
        This allows the session to be written to while files are downloading.
  * WB: CATS version number is now a constant. It is still parsed easily enough
        by external programs, including our version-changer script, and this
        will be faster / cleaner than an extra filesystem access.
  * WB: Updated scripts/newversion.sh to work with CATS_VERSION constant.
  * WB: CATSUtility::getBuild() now returns 0 instead of -1 if there is no SVN
        metadata.
  * WB: The build number (not the entire line) is now stored in Session, and
        can be retrieved via Session::getCachedBuild().
  * WB: Use the cached build number from Session when generating footers.
  * WB: Don't show build number in footer if SVN metadata doesn't exist.
  * WB: Renamed the session object from 'catsSession' to 'CATS'.
  * WB: Fixed coupling problems between Session and MRU. Library code should
        only access $_SESSION[] when there aren't better ways.
  * WB: Moved from array_key_exists() to isset(). After researching, isset()
        is what we should be using.

11/13/2006
  * WB: Fixed a bug causing the Date Available field to not keep its value
        when the Edit Candidate page is loaded. Thanks to DFelzke on the CATS
        Forums.
  * WB: Fixed YET ANOTHER Hot Lists bug/typo (two $'s in front of a variable).
  * WB: Audited and cleaned up HotLists.php; shouldn't be any more bugs now.
        Documentation fixes along the way. As a side note, since I've seen this
        quite a bit in the code,  DatabaseConnection::makeQueryStringOrNULL()
        should only be used if NULL is an acceptable value; otherwise use
        makeQueryString().
  * WB: ListEditor::getStringFromList() properly handles empty result sets now.
  * WB: installtest.php warns if register_globals is on in PHP.ini now. I've
        seen other applications do this, as register_globals is a security
        risk.
  * WB: Removed an extra <body> tag from installtest.php's output.
  * WB: installtest.php now properly informs if warnings occurred at the bottom
        of the page. This got broken somehow.
  * WB: Misc. installtest.php improvements; more to come.

11/12/2006
  * WB: Small cleanups to DateUtility.
  * WB: Candidate pipeline match rating HTML IDs are now based on the entry's
        candidateJobOrderID to make them more predictable.
  * WB: Added AJAX 'Screen' icons to the pipeline entries for unscreened
        candidates. Clicking the icon changes the candidate's match rating
        to 0 from -1.
  * WB: Candidate pipeline match rating AJAX code now selects the star image
        based on what the server said the new rating is; instead of what it
        "should" be according to the client. I can see lots of hard-to-find
        errors later on if we don't do it this way.
  * WB: Candidate pipeline match rating cleanups.

11/11/2006
  * WB: Module Schema class's method names are now get() and now getSchema().
  * WB: Lots of AJAX cleanups / fixes. Everything uses $_REQUEST now, so AJAX
        functions can be used via GET or POST. Input validation fixes.
  * WB: Added an option to allow AJAXInterface::isRequiredIDValid() to accept
        0 and negative values. 0 is accepted by default; negatives are not.
  * WB: Added AJAXInterface::outputXMLSuccessPage() method to clean things up
        a bit.
  * WB: ERROR_NO_PERMISSION is now a global constant instead of several module
        constants.
  * WB: Added StringUtility::replaceOnce() method which acts like str_replace
        except replaces only the first occurrence of $needle in $haystack.
  * WB: Status names only get replaced once in change-status activity notes
        for candidates now. Every occurrence of the word "Interviewing"
        shouldn't be highlighted; just the first one.
  * WB: Cleaned up StringUtility::makeREGEXPString() and moved it to BooleanDB.
  * WB: Fixed StringUtility::makeSearchableUSZip() to always return safe
        output, cleaned it up a bit, and moved it to the Anomalous class.
  * WB: StringUtility::makeCityStateString() now trims whitespace from its
        parameters.
  * WB: Anomalous::getCityStateByZip() now always returns an array with a city
        and state element.

11/10/2006
  * BH: Added filter engine.
  * BH: Changed previously abstracted templates into filters.
  * WB: Unscreened candidates' star rating images revert back to the proper
        purple image on mouseout after mouseover now.
  * WB: TemplateUtility::filterRemoveTextBlock() should have been declared as
        static; fixed.
  * WB: Code cleanups to the module schema updater.

11/08/2006
  * BH: Moved around all the post display hooks in Job Orders to happen
        after the initial settings for the template have been assigned.
        I plan on making this change to the other main modules shortly. (TODO)

11/07/2006
  * WB: Small cleanups to import module.
  * BH: Added automatic module specific schema updater.
  * WB: Fixed a possible PHP error in CandidatesUI and JobOrdersUI search
        methods.
  * WB: Fix to calendarDateInput.js to display the proper month name in the
        dropdown list of a DateInput() that has had a value change after
        creation. Thanks to Infoinnov on the CATS Forums.

11/06/2006
  * WB: Fixed a possible remote code execution vulnerability in the AJAX
        Delegation Module introduced in r1117.
  * WB: AJAX function names can now be specified as ?f=module:function when
        calling the AJAX Delegation Module (ajax.php). If no "module:" is
        specified, functions from the main ajax/ directory will be used. If it
        is specified, functions from modules/module/ajax/ will be used.
  * WB: AJAX Delegation Module now sends back an error document if an invalid
        function name is specified.
  * BH: Added barebones framework for a timecard module.

11/05/2006
  * WB: General cleanups.

11/03/2006
  * WB: Schema fix; added index on user.date_refreshed for statistics use.
  * WB: Fixed a bug in AJAXInterface::isRequiredIDValid() which caused it to
        throw a PHP error if a parameter wasn't specified at all in the HTTP
        request.

11/02/2006:
  * WB: General cleanups.

10/31/2006
  * WB: Minor AJAX code cleanups.
  * WB: AJAX delegation module now uses $_REQUEST instead of $_POST to ease
        debugging.

10/30/2006
  * WB: Added a way to silence the timeout popups on AJAX requests.

10/27/2006
  * BH: Added departments to Job Orders.
  * WB: Small fixes / cleanups.

10/26/2006
  * WB: Removed support for &loginpassword=password in URLs. The login process
        has become more complicated since this was added, and the method of
        supplying the password in the URL either needs to be removed or updated
        to preform the same tasks as LoginUI::attemptLogin(). I just removed it
        since there is really no added benefit to anyone but the development
        team, it's not worth the extra code, and its use can have security
        implications. loginusername is still supported.
  * WB: Commented out some of the MD5-passwords-specific code in Users.php that
        could cause some unexpected results.
  * WB: Several Users methods were using the wrong DB object reference.
  * WB: Variable naming fix. Variables corresponding to ID fields should be
        named accordingly: user_login_id -> $userLoginID to avoid confusion.
  * WB: General cleanups.
  * BH: Added hook points to UI code for each module.
  * WB: Removed module categories concept; hooks replace the need for this.
  * WB: Added Session::hasUserCategory() method to simplify category checks.
  * BH: Added history logging to contacts.
  * BH: Added enforcement for user levels on server side for all modules.
  * BH: Client/server side now does not allow candidate/JO star rating change
        for users without access.
  * BH: Fixed removal of categories concept bug from r1133.
  * WB: Fixed a few problems with js/submodal/loading.html. Thanks to
        meofcourse on the CATS forums.
  * WB: Fixed an error in Users.php from r1133.

10/25/2006
  * BH: Improvements to transferRelativeURI.
  * WB: index.php now also checks for the existence of session_start().
  * WB: Added a few FIXMEs to makeRelease.sh for before the next release.
  * WB: Reworked the global includes a bit, as this was getting out of hand.
        No globally included files include files of their own anymore, and
        one of Session's former dependencies, ArrayUtility, hasn't been
        required by Session in quite a while, so it no longer needs to be
        included globally. Hooks.php is now globally included. Keeping our
        framework lightweight still needs to be a priority, and includes are
        expensive.
  * WB: Users::add() now uses DatabaseConnection::getLastInsertID() instead of
        SELECT MAX(user_id)...; (how did this one get missed....).

10/24/2006
  * WB: Small bugfix to users listing in Settings from a previous commit.
  * BH: Disabled users are not in dropdowns throughout the system (Mantis #181).
  * WB: Only ACCESS_LEVEL_ROOT users can view System settings pages now.

10/22/2006
  * WB: Changes to DateUtility::fixZeroDate() to make it a bit more useful.

10/20/2006
  * WB: Cleanups to popup code changes from r998; fixed HTML validation issue.
  * WB: Schema change: renamed email_history.email_sent_id to email_history_id
        per naming conventions.

10/19/2006
  * BH: Candidate image now loads through download.php like other attachments.

10/18/2006
  * BH: Added hooks concept.
  * BH: Added hook points into core modules login, activity, calendar,
        candidates, and clients.
  * BH: Added sample MOTD module implementing hooks.

10/13/2006
  * BH: Finished user access restrictions through template changes on all
        pages.
  * BH: Images on dashboard are sized before loading (helps drawing effect).
  * WB: Small HTML fix from r1026.
  * WB: General cleanups.
  * WB: Copyright fixes on a few JavaScript files.
  * WB: Added ModuleUtility::moduleExists() method.
  * WB: Variable case fixes.
  * WB: Added Contacts::getDepartmentIDByName() method.
  * WB: Hopefully fixed an Edit Contact bug caused by Departments code. Thanks
        to merybuck on the CATS Forums for finding this and helping test.
  * WB: Fixed a few PHP errors found by the Automated Tester.

10/12/2006
  * BH: GUI side of user level privileges implemented for job orders and
        candidates - backend code still needs to check user levels.
  * WB: Fixed a small design error in user access level GUI changes: Template
        class should be completely uncoupled from Session code.
  * WB: Read-only related GUI updates.
  * WB: Start of basic download-proxying / mangling. Thanks to madhitz on the
        CATS Forums.
  * WB: Input validation for SettingsUI::viewItemHistory().
  * WB: Fixed a few places where addslashes() was being used where
        htmlspecialchars() should have been used.
  * WB: General cleanups.

10/11/2006
  * BH: Style bug in import fixed.
  * BH: 'site_id' added to extra fields tables.
  * BH: Change site name creates default client.
  * BH: Default client is now hidden if no default client in the system.
  * BH: Fixed bug in schema_changes.
  * BH: Candidate profile images are now deletable.

10/10/2006
  * BH: Removed profile image from add/edit contact.
  * BH: Small HTML cleanups.
  * BH: There is now an undeletable default client for internal postings.
  * BH: Contacts and Job Orders can be assigned to the internal client.
  * BH: Added ENABLE_SINGLE_SESSIO
