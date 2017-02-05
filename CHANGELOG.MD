$Id: CHANGELOG 3810 2007-12-05 19:13:25Z brian $

CATS ChangeLog
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
  * BH: Added ENABLE_SINGLE_SESSION setting in config.php. If enabled, only
        one user/browser can be logged into the same user account at the same
        time.
  * BH: Fixed bug with cookies enabled tester not forwarding to the correct
        sites login page.

10/09/2006
  * BH: DIV based popups can now coexist with IFRAME popups.
  * BH: Candidates can now have an image uploaded.

10/07/2006
  * WB: Fixed keypress event bug in calendarDateInput.js when using Firefox.
  * WB: Added tabindex support to calendarDateInput.js.
  * WB: Cleaned up calendarDateInput.js's DateInput() function quite a bit.
        Optional parameters are gone now. '' / -1 is now used to indicate
        "unspecified". The optional parameter stuff was hackish. The whole
        script really needs massive cleaning up. Every time I open it I find
        a new bug...
  * WB: Fixes to tabindexes in several pages.

10/06/2006
  * BH: Added categories feature to users, and applied categories to tab
        visibility. By adding users to different categories, you can write
        code in the system to add or remove features appropriately.
  * BH: Clients -> add looks like edit client now.
  * BH: Clients -> edit has zip code lookup now.
  * BH: Forgot to include image for edit stylesheet, fixed.
  * BH: Misc fixes to mailer, so mail can be sent even if a user isn't logged
        in.
  * BH: HTMLized new site welcome E-Mail.
  * BH: Updated login page for demo account.
  * WB: Only allow ACCESS_LEVEL_ROOT users to login in CATS_SLAVE mode.
  * BH: Synchronized look of add contacts to edit contacts.
  * BH: Candidates -> Edit and Contacts -> Edit have zip code lookup now.
        Thanks to DFelzke on the CATS Forums.

10/05/2006
  * BH: Some changes to the look of edit clients and edit contacts.
  * BH: Misc changes.
  * WB: Added CATS_SLAVE config.php setting. This is to prevent database writes
        on replication slaves for internal use.
  * WB: General cleanups.
  * WB: Added basic query write filtering in DatabaseConnection.php. No changes
        will be allowed to the database in CATS_SLAVE mode.
  * WB: DatabaseConnection.php documentation.

10/04/2006
  * BH: Adding a candidate through modal doesn't delete sources anymore.
  * BH: Editing a client retains billing contact now.
  * BH: History for editing clients added.
  * WB: Header redirects should work with HTTPS now. Found by BucK on the CATS
        Forums.

09/29/2006
  * WB: Fixed a link-path error that can occur on Windows systems. Found by
        brummygit and merybuck on the CATS Forums.
  * WB: Truncation of MRU items was broken; fixed.

09/26/2006
  * BH: Some more changes to item history viewer.
  * WB: doc/CHECKLIST is now updated for 0.7.0.
  * WB: Fixed a PHP error that appears when logging in with a blank password.
        Found by DFelzke on the CATS forums.
  * WB: Minor demo MOTD updates.

09/25/2006
  * Start of version 0.7.0.
  * BH: Added history logger to Job Orders and Candidates (viewable by
        administrator and demo users).
  * BH: Re-enabled some unfinished Administration page entries.
  * BH: Added PDF based report to Job Order details.

09/22/2006
  * WB: Finished SMTP library.
  * WB: AJAX_callCATSFunction() now supports adding additional seconds to the
        timeout to allow certain
        AJAX calls to take longer than the global
        limit. We needed this for e-mail settings testing.
  * BH: Fixed suggest list to work under IE6 when a dropdown control was under
        the area for the suggest box.

09/21/2006
  * BH: Changes to installtest.php - schema_version = 904, admin password
        not revealed.
  * WB: Wrote a new drop-in-replacement SMTP library (based on the old one).
        Supports SMTP AUTH PLAIN authentication now, as well as allowing
        easy addition of CRAM-MD5, NTLM, etc. in the future. Error handling
        is also greatly improved.
  * WB: Fixed a small SMTP bug in Mailer preventing SMTP AUTH from working
        properly even if AUTH PLAIN is supported. Thanks to cbo for reporting
        this one.

09/20/2006
  * BH: Made new testdata file.
  * WB: Added AJAX zip code lookup to Add Candidate and Add Contact.
  * WB: Minor Settings cosmetic fix.
  * WB: Fixes / cleanups to SystemInfo.php.

09/19/2006
  * Start of version 0.6.1.
  * WB: Fixes to scripts/countcode.sh.
  * WB: Deleting a job order now deletes associated attachments.
  * WB: Deleting a client now deletes associated attachments.
  * BH: Fixed test data to not hit an internal MySQL limit.
  * BH: Changed demo MOTD.
  * BH: Added schema_changes to release files.
  * WB: Deleting a pipeline entry now deletes its status history as well.
  * BH: Fixed Maximum Rate Field.
  * WB: Minor cosmetic fixes.
  * WB: Pager navigation page selection dropdown works now on pages where
        navigation is at both the top and bottom of the page.
  * WB: PHP errors no longer occur if schema_changes.sql does not exist.
  * BH: Extra fields can now be checkboxes.
  * BH: Fixed bug involving activity note and changing status more than once.

09/18/2006
  * BH: Finished AJAX E-Mail settings tester.
  * BH: Added E-Mail Logger.
  * BH: Changed Default E-Mail value to PHP mailer.
  * BH/WB: More E-Mail Settings Tweaks.

09/16/2006
  * WB: Add Candidate works again from the Job Orders perspective.
  * WB: Start of AJAX E-Mail settings tester.

09/15/2006
  * WB: Silence PHP errors when a DatabaseConnection query fails.
  * BH: Altered schema files to have each zip code on a new line.
        Schema can now be copy and pasted into MySQL Admin.
  * BH: Login.tpl contains JavaScript that checks if cookies are enabled.
  * BH: Fixed a bug preventing AJAX from working in job orders.
  * BH: Switched Job Orders to use the AJAX suggest rather than client
        dropdown list.
  * WB: Moved e-mail settings from config.php to the Settings module.
  * WB: E-mail is now globally disable-able (this needs a bit of GUI work
        still).
  * WB: General cleanups / etc.
  * WB: Fixed a few cases in SettingsUI where non-static methods were being
        called via self::whatever().
  * BH: Changed some new install logic.
  * WB: Removed a bit of leftovers from a NewVersionCheck upgrade.
  * WB: Fixed a few small bugs in E-Mail Settings.

09/14/2006
  * BH: When posting an activity with the words 'Status change:' only
        the next 1 word is highlighted (the status).
  * BH: Fixed missing PIPELINE_STATUS_CLIENTDECLINED from previous commit.
  * BH: All E-Mail features are disablable.
  * BH: If forced to login, CATS will visit the presented URL before
        logging in right after user is authenticated.
  * BH: Fixed editing activities to handle highlighting.
  * BH: CATS complains to SA and Root users if E-Mail is not configured.
  * BH: CATS makes users set an E-Mail address upon login if none set.
  * BH: Fixed bug where boolean searches could search any site.
  * BH: Fixed issue with saved search if user is browsing using
        http://www.domain.com//index.php.
  * WB: Search Results page for candidates now displays the pager navigation
        properly and generates proper-er HTML 4.01.
  * BH: Fixed bug where hot lists could not be deleted.
  * BH: Fixed bug where candidates would not be removed from hot lists properly.
  * BH: Updating contacts addresses works now.
  * WB/BH: Fixed a JavaScript bug in Internet Explorer caused by missing "var"
           keywords on intended-to-be-local variables.
  * WB: Log Activity / Change Status DHTML behavior fixes.
  * WB/BH: Removed "First Activity" stuff from the Consider Candidate dialogs,
           to provide consistency with the new Log Activity / Change Status
           stuff.

09/13/2006
  * WB/BH: Revised INSTALL documentation.
  * BH: Added release test checklist file to doc directory.
  * BH: Job Order side status changes generate correct E-Mails now.
  * BH: Added a MOTD for demo users instructing them to report bugs via the
        Feedback feature.
  * BH: Fixed bug where could not re-enable a disabled user.
  * BH: Misc cleanups.
  * BH: Added Candidate Replied Status.
  * BH: Fixed change status to consider if a status generates an E-Mail.
  * BH: Change status generates a default activity (again :P).
  * BH: User is only a demo user if demo flag is set.
  * BH: Job Order Pipeline highlights users who are submitted.
  * BH: Added backend support for disabling the change status email.
  * WB: More INSTALL revisions.
  * WB: Added a missing constant for a pipeline status to Pipelines.php.
  * WB: Updates to scripts/qc.sh.
  * WB: HTML cleanups and other cleanups.
  * WB: Fixes to candidate "has been submitted" determination.

09/12/2006
  * WB: Added HTML support to Mailer.
  * BH: Forced Mailer to always use HTML (Arial font).
  * BH: Added change of ownership notification E-Mails.
  * BH: Changed default E-Mail templates.
  * BH: Job Order title and Job Order client name added to fields for
        candidate notification of change of status.
  * BH: First draft of INSTALL documentation revision.
  * WB: Bugfixes to Mailer.

09/11/2006
  * WB: Renamed "*ul=" subtab link parameter to "*al=". It's access-level
        not user-level everywhere else, so it would end up being confusing.
  * WB: Added "*al=" parameters to Import subtabs.
  * WB: Fixed (I think...) a bug in the way we added SimpleTest to the tree
        that caused a PHP error.
  * WB: Ensure that persistent checkbox methods in Session always return
        boolean.
  * WB: Added get/set methods in Session for persistent drop-down lists.
  * WB: All unit test cases pass now.
  * WB: All AJAX test cases pass now.
  * BH: Bugfixes.
  * BH: Removed change ownership email templates from schema.
  * BH: Updated install documentation.
  * BH: Added release generator script.
  * BH: Merged step1/2/3 schema files into cats_schema.sql. Note:
        This will be the last CATS version where the user manually configures
        the schema - future versions will install the default schema or update
        the schema automatically with the use of schema_changes and a new
        wizard.
  * BH: Demo accounts can no longer trigger E-Mails.
  * BH: Previous status now in automated status change e-mails.

09/10/2006
  * WB: Renamed "Passed On" status to "Rejected by Client".
  * WB: Added "N/A" status.

09/09/2006
  * WB: Candidate star-rating system now uses the existing AJAXInterface
        framework.

09/08/2006
  * WB: Change Pipeline Status notification e-mails should work now.
  * WB: Pipeline Status scheduled events are now associated with their
        respective candidate and job order IDs.
  * WB: General cleanups.

09/07/2006
  * BH: Select page added to top of pages with pager.
  * BH: E-Mail templates now only use one percent sign rather than 2.
  * BH: Forced new E-Mail Template schema.
  * BH: <textarea>s and input boxes now use the same fonts.
  * BH: Added fpdf freeware PDF generation library.
  * BH: Login history truncation removed, now just hides logins over 20 pages.
  * WB: Dashboard GUI updates.
  * WB: CATSWebTestCase::login() now checks for an up-to-date DB schema.
  * WB: Automated test cases now abort if $this->login() fails.
  * WB: Fixed a cosmetic bug where the Change Password template will always
        display the notice that demo users cannot modify settings, even if
        demo mode is disabled or the user is now a demo user.
  * WB: Removed some leftovers from when the Calendar module got converted
        to DHTML.
  * WB: HTML 4.01 does not allow end tags or short-end-tags on AREA tags.
  * WB: Fixed a PHP error on the Hot Lists page in the Candidates module.
  * WB: Misc. cleanups.
  * WB: GUI fixes for title images on pages. These were very inconsistent.
  * WB: Eliminated the use of non-w3 compliant (HTML 4.01) align="absmiddle"
        IMG attributes. The effect is now achieved via CSS with the "absmiddle"
        class.
  * WB: Fixed a PHP error in the New Version Check Settings template.
  * WB: Calendar module automated test cases now test the existence of form
        fields for Add Event / Edit Event. Test cases are still not as complete
        as they were for the non-DHTML calendar, but SimpleTest has no support
        for JavaScript.

09/06/2006
  * BH: Custom email message for change status now auto fills in with E-Mail
        template.
  * BH: Changes to login screen GUI.
  * BH: Schema change wizard now detects if the schema version has failed to
        be updated.
  * WB: Misc. cleanups.
  * WB: More automated test cases pass.
  * WB: Eliminated code duplication between JobOrdersUI::onAddCandidateModal()
        and CandidatesUI::onAddCandidate(). More of this will follow now that
        the model is in place.
  * WB: UserInterface::fatal() and UserInterface::fatalModal() now have an
        optional parameter to allow the module directory where the templates
        are found to be overridden.
  * WB: Note header displays properly on the Add Candidate modal template now.
  * WB: Login tests are aborted now if schema is not up to date.
  * WB: Added basic web test cases for the Activities module.
  * BH: Added 'Rating' feature to candidate job orders (job order tab).
  * BH: Added 'Rating' feature to candidate job orders (candidate tab).
  * WB: Eliminated code duplication between onAddActivityChangeStatus()
        and in JobOrdersUI and onAddActivityChangeStatus() in CandidatesUI.
  * WB: More cleanups.
  * WB: Modified simpletest to allow setting of hidden form fields via
        setField(). I hate to have to modify simpletest, but it seems to be
        the only way unless there is an upstream fix.
  * WB: Import module template fixes / cleanups.
  * WB: *MORE* HTML fixes.
  * WB: Tests module GUI updates (functional purposes).
  * WB: Added a few more tests to existing automated test cases.
  * WB: All Contacts tests pass now!
  * WB: Fixed a form validation bug in Add Contact that could allow a user to
        submit the form without having selected a proper client.

09/05/2006
  * WB: Fixed a typo / bug in NewVersionCheck::getDataFromServer().
  * WB: HotLists cleanups and schema updates.
  * BH: Bugfixes.
  * WB: Documentation updates.
  * WB: Fixed calendarDateInput.js to properly handle day numbers that begin
        with a 0.
  * WB: Values of calendarDateInput.js DateInput()'s can now be set via the
        new SetDateInputDate() JavaScript function.
  * WB: Misc. cleanups / fixes.
  * BH: Added E-Mail Template editor.
  * WB: Small HTML escaping fix to Activities module.

09/04/2006
  * WB: More changes to change pipeline status layout / behavior per
        conversation with Asim.
  * WB: Job Orders display fix.
  * WB: Sync pipeline status changes from Candidates to Job Orders.
  * WB: Small update to release guidelines.
  * WB: Added EmailTemplates library for e-mail template handling.
  * WB: Documentation fixes.
  * WB: Encoding fixes. ALWAYS USE utf8 CHARSET AND utf8_unicode_ci COLLATION.
  * WB: E-Mail template system schema updates.
  * WB: Various sweetTitles.js fixes.

09/03/2006
  * WB: More changes to change pipeline status layout / behavior per
        conversation with Asim.

09/02/2006
  * WB: Fixes to scripts/qc.sh.
  * WB: More HTML fixes / cleanups.
  * WB: sorttable.js is now Safari compatible.
  * WB: Clarified some JavaScript copyright information.
  * WB: scripts/qc.sh updates.
  * WB: Show Client tables are now sortable.

09/01/2006
  * WB: Various GUI updates, HTML fixes, etc.
  * WB: RSS module fixes / cleanups.
  * WB: More pages validate to HTML 4.01 Transitional now.
  * WB: Graphs cleanups and HTML output fixes.
  * WB: installtest.php generates valid HTML now.
  * WB: Renamed ResultSetUtility::findKeyValuePair() to findRowByColumnValue().
  * WB: Made sweetTitles tooltips a bit quicker to appear.
  * WB: Moved submodal to js/.

09/01/2006
  * WB: Template cleanups. Don't use htmlspecialchars() in templates, use
        $this->_().
  * WB: Misc. cleanups.
  * BH: Calendar now can start the workweek on Monday.
  * BH: Changes to change pipeline status layout / behavior.

08/31/2006
  * BH: Calendar now looks for allDay flag rather than assuming events at
        12:00 AM are all day events.
  * BH: Minor changes (work in progress)
  * BH: Fixed missing brackets in some templates.
  * WB: Pipeline status fixes. Pipeline status dialog works from Show Job
        Order now.
  * WB: An event's "public" status can now be modified from the Log Activity /
        Change Status dialogs.
  * WB: Misc. cleanups.

08/30/2006
  * PC: Changed info string for job orders to show different information.
  * PC: Added hot list manager to view candidates by hot list page.
  * BH: Fixed departments in contacts.
  * BH: Fixed add contact by client.
  * BH: Lots of other bugfixes.
  * BH: Job orders can now sort by status.
  * BH: Added framework to hide tab by user level - requested by
        http://www.catsone.com/forum/viewtopic.php?id=165. Not in use
        anywhere in the default code.
  * BH: Job order submitted icon for contacts list only shows up if contact
        has a submitted or higher status in at least 1 job order now.
  * BH: Changed layout of home dashboard.
  * BH: Disabled customize dashboard until default dashboard style is
        finalized.
  * WB: Resume uploading on Windows was broken; fixed.
  * WB/BH: Attachments code fixes / cleanups.
  * WB: Forgot an SVN add from a previous commit.
  * WB: Lots of updates to doc/DEVELOPMENT_GUIDELINES. Added release
        instructions and documented a few more style guidelines that were
        previously left out.
  * WB: Lots of CalendarUI.js cleanups and some minor Calendar GUI fixes.

08/29/2006
  * BH: Pipeline graph changes (schema change).
  * WB: Misc. cleanups.
  * WB: Use DatabaseConnection::escapeString() instead of addslashes(). We need
        to be able to support other databases than MySQL later, and different
        databases use different escaping sequences.
  * WB: Added scripts/qc.sh. This is for, basically, grepping the code for
        common errors. It is by no means anywhere near finished; right now it
        is just a replacement for the list of stuff I have to remember to
        manually search the code for / etc. right now.
  * BH: Misc changes.
  * BH: Removed no status column from pipeline graph.
  * BH: Added upcoming events near candidate attachments in candidate view
        (if the candidate has a upcoming event).
  * BH: Quick fixed activity logging so activity text is saved.
  * WB: Fixes to installtest.php. We now do better (Windows compatible)
        permissions testing and, we also check the temp/ dir now.
  * WB: Pipeline status fixes.
  * WB: ActivityEntries fix from r701.
  * WB: Display proper activity type on pipeline status "Finished" screen.
  * WB: Misc. cleanups.
  * WB: Fixed a possible Add Event calendar bug if the date was empty for some
        reason.
  * WB: Fixed a few Calendar::addEvent() and updateEvent() parameters that
        should have been booleans.
  * WB: Calendar documentation fixes.

08/28/2006
  * BH: Fixed new version check for 0.6.0 (again).
  * BH: Added resume preview to all search fields for candidates.
  * BH: Added item preview for all searches (optional open result in new
        window).
  * BH: Misc. bugfixes.
  * WB: Schema fixes; parameter was spelled incorrectly in two tables.
  * WB: Job Orders main listing submitted statistics are live again.
  * WB: Pipeline status graph on dashboard is now live.
  * WB: Various pipeline status fixes.
  * BH: Fixed pipeline status graph on dashboard.
  * WB: Several methods were missing their public/private/protected access
        definition; fixed.
  * WB: Misc. cleanups.

08/25/2006
  * BH: Formatted the calendar so both view and edit events take up the same
        width.
  * BH: Bugfixes.
  * WB: Random number generation fixes.
  * BH: Updated new version check to work correctly for 0.6.0 release.
  * BH: Implemented postback sorting for job orders.
  * WB: Added FileUtility::makeRandomFilename() for use creating when creating
        temporary files.
  * WB: Added FileUtility::isDirectoryWritable(). PHP's is_writable() will
        sometimes return false positives, ESPECIALLY on Windows, when used on
        directory names.
  * WB: Attachments bugfix when trying to preview an attachment that wasn't
        parsed. This is usually caused by users accidentally selecting
        "Resume" mode when uploading, say, an image or a PDF.
  * WB: When uploading an attachment, it is possible that the directory where
        the attachment will be stored already exists and is not writable. We
        can now usually recover from this problem.
  * WB: Removed SysUtility::isUNIX(). It is safe to say that if we are not on
        Windows, we are on UNIX (safer than trying to guess the names of all
        the UNIX variants out there).
  * WB: Renamed SysUtility to SystemUtility.
  * WB: HTML fixes.
  * WB: Added more browser support to BrowserDetection::detect(). Also sped
        this code up a bit... it was using preg_match() in a lot of places
        that a regular expression was not needed.
  * WB: Documentation fixes.

08/24/2006
  * BH: Changed graph preview to go full screen, and eliminate
        the CATS controls from the UI.
  * BH: Changed Profile Settings to look more like administration.
  * BH: Fixed edit user so you can edit yourself (but can't edit access
        level).
  * BH: Commented out unimplemented settings links.
  * BH: Added ErrorModal.tpl to export, graphs, and import.
  * BH: Fixed import to work on PHP 5 through PHP 5.0.4.
  * WB: Pipeline status GUI link on pipeline on candidates page works now.
  * WB: Small Calendar display fixes.
  * BH: Misc bugfixes.
  * WB: Factored quite a bit of redundant attachments code out of the module
        UIs and into FileUtility.
  * BH: Added indicator to candidates to show if candidate is in pipeline.
  * WB: HTML cleanups.
  * WB: Added System Information to Settings -> Administration.
  * WB: General cleanups.
  * WB: Moved popup containers more toward the top of files as it was causing
        errors when a user quickly clicked a popup link before the page had
        fully loaded.
  * WB: Submitted statistics are live again on Show Candidate.
  * WB: Added installation path and OS stats to System Information.
  * WB: Factored out Address Parser JavaScript from js/lib.js and into
        js/addressParser.js. lib.js is included in every template and address
        parsing is only done in candidates/Add.tpl. This saves over 8KB on
        every page download.
  * WB: fatal() fixes in Graphs module.

08/23/2006
  * BH: Some code cleanups, documentation.
  * PC: Candidates hot list clean up and bug fixes.
  * BH: Fixed import bug.
  * PC: Bug fixes for doubleListEditor.js.
  * PC: Clean up hot lists and hot lists editors.
  * BH: Fixed login activity.
  * PC: Fixed bug where candidates with no empty hot lists causes error.
  * BH: More various fixes.
  * BH: Changed subtabs so they can be hidden based on user level.
  * BH: Made administration invisible to non SA users.
  * WB: DHTML effects for pipeline status work now. Server side needs a tiny
        bit of work.
  * WB: Made the job order submission count live again on the Show Job Order
        page.
  * WB: Misc. cleanups and documentation.
  * WB: Added unit test cases for StringUtility::escapeSingleQuotes().
  * WB: Added constants for pipeline status IDs for use in queries, etc.
  * WB: Never set a candidate's pipeline status in Pipelines::setStatus() if
        old status and new status are identical.
  * WB: Robustness fixes to ActivityEntries::add().
  * WB: New pipeline status / add activity GUI works now!
  * BH: Fixed a bug causing activities at exactly midnight not to show up on
        the daily activities pages. Found by DFelzke on the CATS forums.

08/22/2006
  * PC: Implemented hot list manager for candidates.
  * PC: Added support for candidate hot lists (schema change).
  * PC: Candidate hot lists now works
  * PC: GUI changes for the hot list editor.
  * BH: Settings page redesign.
  * BH: Fixed import to work with new extra fields design.
  * BH: Added a graph viewer for the small graphs on the home page.
  * WB: Cleanups to subModal popup container stuff.
  * WB: Improvements to pipeline status DHTML GUI.

08/21/2006
  * WB: Some test cases were breaking do to Departments code; fixed and did a
        few cleanups.
  * WB: Login module cleanups.
  * WB: Changing a candidate's status from the Show Candidate pipeline table
        works now. Although the GUI is about to change massively, I went ahead
        and did this quick change for testing purposes.
  * BH: Calendar on dashboard now only shows time for todays events, and date
        for upcoming events that do not happen today. Also hyperlinks events
        to calendar module.
  * BH: Misc changes.
  * BH: Abstracted out graph drawing functions into GraphGenerator.php.
  * BH: Fixed bug where Import.tpl would not load on non windows machines.
  * BH: Fixed bug for adding image on customize dashboard.
  * BH: Added calendar customization.
  * BH: UI change of dashboard customizer.
  * BH: Changed Candidates, Clients, Contacts to have fixed extra fields
        specified by SA.
  * BH: Fixed bug in contacts where you could not add a contact with no
        department.
  * BH: Fixed logic issue in getThisSubTabsExternal introduced in r577.
  * BH: Added constraints to the demo user preventing demo from submitting
        settings changes and importing data if logged in from a non localhost
        ip number.
  * BH: Started changing import to support the new format for extra fields.

08/20/2006
  * WB: Minor Statistics library refactoring.
  * WB: General cleanups, and lots of them. This will span multiple commits.
  * WB: Several HotList fixes (schema update). It still needs a lot of work.
  * WB: Major GUI updates and general cleanups to installtest.php.
  * WB: Better failure notes in some cases in installtest.php.
  * WB: More web test cases pass now.
  * WB: Small tests module cleanups.
  * WB: Removed non-JavaScript-browser compatibility constructs. We don't
        support these browsers anyway.
  * WB: Created lib/ResultSetUtility.php for result set array functions, as
        we are getting quite a few of them now, and ArrayUtility is included
        globally.
  * WB: Added method ResultSetUtility::getColumnValueByIDValue(). This method
        is used for returning a specific column's value from a specific row
        based on the value of the "ID" column in that row. This saves me from
        having to do an extra database query when we already have the data,
        just not necessarily in the needed format.
  * WB: Lots of improvement on UI aspect of changing a candidate's status.
  * WB: CHANGELOG spelling correction :).
  * WB: Start of test cases for ResultSetUtility.

08/19/2006
  * WB: Removed submit / unsubmit leftovers.
  * WB: GUI changes to Show Job Order page for pipeline status.
  * WB: Submission Report pages use alternating table row colors now.
  * WB: General cleanups.
  * WB: Candidates now get a status of "No Contact" when being added to a
        joborder pipeline.
  * WB: Renamed modules/graphs/graphUI.php to proper GraphsUI.php. The formula
        is ModuleNameUI.php.
  * WB: More general cleanups.
  * WB: More submitted statistics fixes.

08/18/2006
  * BH: Home page layout checkpoint (schema changes).
  * WB/BH: Temp directory fixes. There is now a cats/temp/ directory, which is
           the default temp directory on all systems. This can be changed by
           editing config.php.
  * WB: Schema updates for pipeline status. candidate_joborder's submitted
        field is gone, all existing pipeline entries have their status
        changed to 'Submitted', and entries have been added in
        candidate_joborder_status_history.
  * WB: Submitted statistics in Reports module are now based off of entries in
        candidate_joborder_status_history. Submission counts for individual
        candidates / joborders don't work quite yet (dummy data).

08/17/2006
  * BH: Repaired broken schema_changes.
  * BH: Customize dashboard now allows rearrange of items.
  * BH: Various updates to dashboard.
  * WB: RSS module cleanups.
  * WB: RSS module generates valid RSS now, including XML HTTP headers.
  * WB: Factored out some common code used to determine the string description
        of a job type (H, C, C2H, etc.) into the JobOrders library.

08/16/2006
  * PC: Implemented persistent checkboxes for candidates.
  * PC: Added only hot clients checkbox.
  * PC: Implemented persistent checkboxes for job orders, contacts, and
        clients.
  * BH: Added customizable dashboard for home tab.
  * WB: More pipeline status work. Very very very basic GUI mockup... Need
        suggestions. Also more work on the general UI side of the changes.
  * WB: Pipeline status DB schema fixes.

08/15/2006
  * BH: GD2 is now optional.
  * BH: Implemented some calendar UI suggestions.
  * BH: Added public/private indicators to calendar events.

08/14/2006
  * PC: Fixed bug where action icons appear small after adding client activity.
  * PC: Implemented persistent check Box for candidates only my candidates
        check box.
  * WB: Storage engine / character encoding fixes to schema.
  * BH: Eliminated department row in contacts table.
  * BH: List editor fix for IE6.
  * BH: Changed calendar highlight colors.
  * BH: Initial implementation of Artichow graphing library.
  * BH: Updated installtest to look for GD2.

08/13/2006
  * BH: Added generic list editor.
  * BH: Implemented client departments with list editor.
  * WB: Small HTML fix; missing DIV tag on Clients.tpl.
  * BH: Departments now listed on add client.
  * WB: General cleanups.
  * WB: Fixed accidentally modified config.php from r530.
  * WB: For some reason, there were a lot of "<br \>" tags (not the proper
        "<br />") throughout the code. I guess this just got copied and pasted
        everywhere or something; fixed.
  * WB: Added SECONDS_IN_A_DAY constant to simplify code in a few places.
  * WB: Cleaned up the subtab / external subtab module code a bit.
  * WB: More Export module cleanups.
  * WB: Renamed DateUtility::getPastDate() to subtractDaysFromDate() and found
        a cleaner way to implement it.
  * WB: Lots of Activity module cleanups.
  * BH: Added list editor to candidate source field.


08/12/2006
  * WB: Added some missing HTML ALT tags. Even 'alt=""' is enough to make the
        W3 validator shut up; which is the only real concern right now. We can
        worry about accessibility later.
  * WB: General HTML fixes.
  * WB: Fixed a few input-validation related PHP errors in Export code.
  * WB: Added scripts/svnkeywords.sh. This turns on keyword substitution for
        SVN Id tags, etc. Run this after adding new files.

08/11/2006
  * WB: Revisions to pipeline status framework.
  * WB: Massive code cleanups part one.
  * WB: Fixed PHP errors found during automated testing run.
  * WB: Lots of Export module cleanups.

08/10/2006
  * BH: Added saved searches.
  * BH: Misc bugfixes.
  * PC: Added support for candidates hot lists (schema change)
  * PC: Added ability to mark candidates as hot candidates.
  * PC: Added page to view candidates by hot list.
  * WB: Revisions to pipeline status framework.

08/09/2006
  * PC: Added support for candidates, joborders, contacts to export
        module.
  * PC: Implemented exporter in candidates, joborders, and contacts.
  * BH: Added schema update wizard for dev / svn machines.
  * BH: Changed Maximum Rate field from integer to varchar(255).
  * BH: Changed class names for buttons on add activity DHTML.
  * BH: Changing a client address has the option of changing contacts.
        Resolves:  http://www.catsone.com/forum/viewtopic.php?pid=627#p627
  * BH: Renamed schema_changes.txt to a .sql file.
  * BH: Added customization subtab to settings.
  * WB: Many changes to Mailer and mail configuration in config.php. Added
        SMTP / Sendmail support (needs testing).

08/08/2006
  * PC: Added and implemented export module for clients.
  * PC: Added export.js javascript.
  * PC: Added export library class.
  * BH: Added Alpha DHTML calendar, replacing existing calendar.
  * BH: Added DataDisplay lib file.
  * BH: Miscellaneous code cleanups.
  * BH: Added various icons for representing data.
  * BH: Changed method of reporting DB changes.
  * WB: Start of pipeline status framework (Pipelines.php changes, etc.).

08/07/2006
  * WB: Pipeline status schema revisions.

08/03/2006
  * WB: Added StringUtility::getColumnValues() to assist with export module.

08/02/2006
  * BH: Changed import warning bar to be orange rather than red.
  * BH: Updated schema changes document with import schema.

08/01/2006
  * BH: Pager optimizations.
  * WB: Renamed TESTER_DEMO_MODE to ENABLE_DEMO_MODE in config.php.
  * WB: Only show affiliate link when ENABLE_DEMO_MODE = true.
  * BH: Suggest.js fixes
  * BH: Added import module.
  * WB: Removed some wz_tooltip.js leftovers.
  * WB: Documentation updates to js/suggest.js.
  * WB: Separated $dataItem->get() and $dataItem->getForEditing(). This fixes
        performance issues with Edit pages by not pulling down bunches of
        statistics that aren't necessary for the Edit pages. Thanks to tds
        on the CATS forums for reporting this.
  * PC: Created InfoString class to generate strings used for tooltips
  * PC: Implemented use of InfoString class for Activities module and Joborders

07/31/2006
  * WB: Settings module cleanups.

07/30/2006
  * WB: General cleanups.
  * WB: Header / Footer HTML fixes.
  * WB: NewVersionCheck cleanups.

07/29/2006
  * WB: Lost of JavaScript cleanups... possibly a few bugfixes.
  * WB: Moved js/foreignEntries.js:escapeHTML() to js/lib.js. It might be
        useful for other things.
  * WB: Input validation for getClientNames AJAX function. Never ever ever
        access $_GET / $_POST variables without making sure they actually
        exist. I see / fix this a lot in our code...
  * WB: Session cookie passing from PHP to JavaScript is REQUIRED for our
        secure AJAX interface. Disabling this in clients.js probably broke
        stuff in the release. I have fixed and also added support to
        suggest.js.
  * WB: More JavaScript cleanups.

07/28/2006
  * WB: HTML cleanups to login/Login.tpl. <font> = bad.
  * WB: More massive activity module cleanups.
  * WB: Better formatting of the Regarding column for main / search Activity
        module listing, including truncation.
  * WB: Other misc cleanups.
  * BH: Added extra fields to Clients, Candidates, and Contacts for
        compatibility with import and internationalization.
  * BH: Re-enabled AJAX suggest in contacts\edit.
  * BH: Added external subtab entries framework.

07/27/2006
  * PC: Added Public field to Job orders (schema change).
  * PC: Updated install script and test data to reflect latest schema.
  * PC: Added Public Icon.
  * PC: Added RSS feed module which shows all the public job orders.

07/26/2006
  * WB: Merged sweetTitles CSS into main.css.
  * WB: AJAX zip lookup is now triggered by a button. This was discussed in
        the past but never implemented. Automatic lookup would override custom
        City and State entries when the zip is entered. For example, when
        entering Cognizo's address, the correct city is "Minnetonka", however
        zip lookup corrects it to "Hopkins". This will happen frequently as
        zip codes cover *areas*, not necessarily cities / towns.
  * WB: Fixed info-tips for hot / left company checkboxes for data item Add /
        Edit pages.
  * WB: Merged js/addEvent.js into js/sweetTitles.js. Sweet Titles was the only
        script using this code, as everything else has its own add event code.
        No sense in including two files on every page in which sweetTitles is
        used.
  * WB: When editing a candidate, the website field was not populated; fixed.
        Found / fixed by DFelzke on the CATS Forums.

07/25/2006
  * Start of 0.6.0.

07/24/2006
  * 0.5.5 released.

07/22/2006
  * WB: Fixed JavaScript errors in js/sweetTitles.js preventing titles from
        working on IMG tags.
  * WB: Fixed a small HTML table bug in candidates/Add.tpl causing the page to
        take up more vertical space than required.
  * WB: Don't use sweetTitles.js on Candidates page yet for consistency. We'll
        use it everywhere in 0.6.
  * WB: Small HTML fix in settings/Administration.tpl.
  * WB: Cleanups to Hello module.
  * WB: Documented Hello module.
  * WB: Don't use sweetTitles.js on Clients or Contacts page yet.

07/21/2006
  * BH: Searching for ".net" was broken on keyword search; works now.
  * BH: Installtest checks for file .version and function ctype_digit now.
        This resolves http://www.catsone.com/forum/viewtopic.php?id=128.
  * BH: All search results are now pager sortable.
  * WB: All Clients test cases pass.
  * WB: All Contacts test cases pass (all system test cases pass now).
  * WB: Fixed a nasty little module API bug. When the first module without tab
        text was reached, no more tabs were printed; fixed.
  * WB: Fixed tab indexes in candidates/Add.tpl.
  * WB: Searches on add to Job Order Pipeline dialogs work again.
  * BH: Upgrade now triggers another version of the new install wizard.

07/20/2006
  * BH: Removed $_GET from various templates.
  * BH/WB: Fixed REGEX queries with symbols.
  * BH: Fixed crippling bugs in resume text preview in search.
  * BH: Fixed various bugs in html for some search templates.
  * WB: Small HTML fix.
  * WB: More cleanups to BooleanDB::makeBooleanSQLWhere().
  * WB: Resume search for "not c" doesn't cause an error anymore.
  * WB: Resume search for "or c" / "and c" doesn't cause an error anymore.
  * WB: Resume search for "or" doesn't cause an error anymore.
  * WB: Starting a search with "not" or "and not" will never return results
        now.
  * WB: Small cleanups in Search.php.
  * WB: Use boolean searching for Search Job Orders by Title.
  * WB: Search::searchExcerpt() handles parentheses now.
  * WB: Search template cleanups.
  * WB: Fixed resume preview icon so that it doesn't distort the table row
        width on the Search Candidates Page.
  * WB: Results for Search Candidates by Resume are now sortable.
  * WB: Results fields for Search Candidates by Resume are more consistent
        with Search Candidates by Name, etc. now.
  * WB: Searches that contain single quotes don't break anymore.
  * WB: General code cleanups.
  * WB: Added DatabaseConnection::escapeString() function to escape query
        strings without quoting them.
  * WB: More Activities module cleanups.
  * WB: A bit of work on the release announcement. More to come.
  * WB: Merged back some missing CHANGELOG entries from cats-opensource/.
  * WB: Ran scripts/killwhitespace.sh.
  * WB: More doc/ updates.
  * WB: All Job Orders test cases pass now.
  * WB: Searching for ".net" was broken; works now.
  * WB: All Candidates test cases pass now.

07/19/2006
  * PC: Fixed bug in install, upgrade, and dumpskel scripts.
  * WB: BooleanDB search code uses MySQL REGEXP's instead of a series of OR
        clauses.
  * WB: CHANGELOG cleanups.
  * WB: Cleanups to BooleanDB code (more to come).
  * WB: Converted BooleanDB to a class.
  * WB: Renamed BooleanDB::boolean_sql_where() to makeBooleanSQLWhere().
  * WB: More consistent output for resume search results.
  * WB: Added licensing notice to the top of tooltip javascript.
  * BH: Added elegant MySQL error handler for boolean based searches.
  * BH: Rewrote BooleanDB class.
  * BH: Fixed resume preview highlighting for search by resume.
  * BH: Applied new js popups to bottom of page pager alphabet.
  * WB: Fixed (mostly) and cleaned up (more to do) Search::searchExcerpt().
        No more PHP errors.
  * WB: Search Candidates by Phone Number is a little bit more practical now.
        It now strips all -, ., (, and ) characters before searching, so
        formatting is mostly ignored.
  * WB: More Activities module cleanups.
  * WB: Don't use $_GET in templates, ever. This violates abstraction. Removal
        started.

07/18/2006
  * PC: Replaced wz_tooltips.js with a better implementation.
  * PC: For mated strings appearing in tooltip popup window.
  * BH: Code cleanups, tweaking.
  * BH: Reformatted search to have a dropdown list.
  * BH: Added advanced search for boolean based searches.

07/17/2006
  * PC: Implemented Pager for search activities search results page.
  * BH: Implemented AND/OR/NOT style search operators to various searches.

07/16/2006
  * WB: More general template / UI cleanups.
  * WB: HTML cleanups.
  * WB: Fixed a PHP error in Search Candidates by Resume.

07/15/2006
  * WB: Fixed another PHP error on Search Candidates by Resume.
  * WB: Cleaned up Clients attachment code.
  * WB: Cleaned up Job Orders attachment code.
  * WB: Other general template / UI cleanups.

07/14/2006
  * WB: Fixes to DB upgrade script from 0.5.2 to 0.5.5.
  * WB: Test for the ability to create directories inside attachments/ in
        installtest.php.
  * WB: installtest code formatting.
  * WB: Huge Search Candidates cleanups.
  * WB: Fixed Pager PHP error on Search Candidates by Resume.
  * WB: Added missing ALT attributes to several IMG tags to make pages
        validate.
  * WB: Cleaned up attachments listing on Show Candidate.
  * WB: Added general test cases for Search Candidate by Resume to check for
        PHP errors, etc.

07/09/2006
  * BH: Added alphabet navigation bar to each page with a default sortBy
        parameter if not sorting by an alphabetical field.
  * WB: More Activities cleanups.
  * WB: Input validation for ActivityUI::onSearch().
  * WB: Lots of little cleanups everywhere.
  * WB: Lots of small HTML fixes.
  * WB: Documented Session::getLoginError().
  * WB: All AJAX test cases pass now.
  * WB: Fixed a major typo causing the Add Client page to submit to the Add
        Contact page.
  * WB: All Clients test cases pass now.
  * WB: More automated tests for DateUtility.
  * WB: All test cases pass now (unit included).
  * WB: Small template fixes.
  * WB: Added ENABLE_HOSTNAME_LOOKUP constant to config.php to allow hostname
        lookup in Settings module to be enabled / disabled for performance.
  * WB: Added missing 'static' keyword to StringUtility::makeSearchableUSZip().
  * WB: Added StringUtility::escapeSingleQuotes().
  * WB: "WZ" tooltips work with single quotes in strings now.
  * BH: Replaced onClick call in pager class with onChange event. Pager
        dropdown now works in IE6.

07/08/2006
  * BH: Added one way password encryption for database passwords.
        The 'Forgot Password' feature is now broken as a result.
  * BH: Fixed a bug where site name was never being written to the database.
  * BH: Changed user permissions for what non SA's can access.
  * BH: Disabled password encryption for 0.5.5 release.
  * WB: Various code cleanups.
  * WB: Activities module uses standard Pager functionality now.
  * WB: Removed a debug line in Site Name changing code that was causing header
        output problems.
  * WB: Renamed ClientsByNamePager to ClientsPager.
  * WB: Renamed ContactsByLastModifiedPager to ContactsPager.
  * WB: Renamed CandidatesByLastModifiedPager to CandidatesPager.
  * WB: Fixed case sensitivity issues with install wizard login checking.
  * WB: Renamed default action in Activities module to listByView.
  * WB: Fixed table header widths in Activities module.
  * WB: Added DEFAULT_ADMIN_PASSWORD constant to constants.php instead of
        using hard-coded values where the admin user's password needs to be
        compared against the default password.
  * WB: Implemented "Public Sessions" (modules that don't require
        authentication). This is enabled or disabled by a the value of the
        variable '$this->_authenticationRequired' (boolean) in a module's
        constructor.
  * WB: Small Pager cleanups.
  * WB: Added a few missing meta variables to constructors for the Login and
        Tests modules.

07/07/2006
  * BH: Added new install wizard screens for default user on first login.

07/06/2006
  * PC: Added schema_changes.txt file to track schema changes.
  * PC: Fixed bug with adding events in the calendar.
  * PC: Fixed bugs with editing events and added validator functions.
  * WB: Lots and lots and lots and lots of code cleanups.
  * WB: Fixed Undefined Index PHP error in Activities Module (I need to
        fix the test cases...).
  * WB: All Job Orders test cases pass again.
  * WB: All Candidates test cases pass again.
  * WB: All Settings test cases pass again.
  * WB: scripts/killshitespace.sh ignores lib/simpletest now.
  * WB: Ran scripts/killwhitespace.sh.
  * WB: Renamed systemInfo class to SystemInfo. Class names should always
        start with an uppercase character.

07/05/2006
  * PC: Reformatted Activities module, rewrote comments.
  * PC: Removed Unused Icons.
  * PC: Updated Icons.
  * PC: Added Fax Number field to clients module. (schema change)
  * BH: Minor fix in Settings.tpl handling for site name.

07/04/2006
  * WB: Removed all instances of nobr tags in favor of the nowrap td / th
        attribute. <nobr></nobr> is not w3c compliant.
  * WB: Removed some left over </a> tags present in a few places in templates.
  * WB: Generated HTML <head></head> section validates to HTML 4.01
        Transitional now.
  * WB: Cleaned up the CHANGELOG. This file needs to look professional guys...
  * WB: Changed name of administrator user from "admin cats" to "CATS
        Administrator".
  * WB: Changed many occurrences of <br> to <br />.
  * WB: Fixed a few incorrect HTML attribute names/ids.
  * WB: Cleaned up the new version check form on the Settings->Administration
        page.
  * WB: Fixed column widths on Clients.tpl and some Activities templates; this
        was causing alignment issues with the headers. This was caused by the
        sort indicator changes.
  * BH: Minor fix where index.php was not added to unsubmit candidate.
  * WB: Added nowrap attribute to all sort table headers.
  * BH: Reapplied a few <nobr> tags in favor of IE5 compatibility after
        discussion with WB. nowrap is still necessary everywhere, however.
  * BH: Updated and activated new version notifier.
  * BH: Merged multiple users entries from same day changes in the CHANGELOG.
  * WB: Activity module capitalization error correction.

06/30/2006
  * BH: Standardized some code on settings/administration.
  * BH: Added transparency to favicon.ico (most noticeable in Opera).

06/29/2006
  * PC: Added website field to site candidate info (schema change, bug 0000166).
  * PC: Modified install scripts, upgrade scripts and test data to reflect
        schema change.
  * PC: Revised favicon.ico file.
  * PC: Removed references to sorttable.js from several files.
  * PC: Activities now displays time with date in 12 hour time.
  * PC: Fixed typo in candidates validator.js file.
  * PC: Added class for quicksearch input box.

06/28/2006
  * PC: Added cats favicon.
  * PC: Activity Module now highlights today and yesterday.
  * PC: Fixed image tags for icons throughout the site.
  * PC: Changed the way truncated notes are displayed in activities module.
  * PC: Renamed change to save in administration panel.
  * PC: Removed link to search by date in activities module.
  * PC: Zipped feedback module and added it to example directory.
  * BH: Fixed bug I introduced on LoginUI.php.
  * BH: Removed FeedbackUI.php.

06/27/2006
  * BH: Added ability to disable new version check to system tab (schema change).
  * BH: Edited login screen to be dependent on config file flag for demo mode.
  * BH: Deleted the majority of the home module static content.
  * BH: Fixed a few places where index.php was still being referenced.
  * PC: Updated Install script and installation instructions.
  * PC: Removed Feedback module.

06/26/2006
  * BH: Added a small arrow indicating what column and what direction
        a sort is being applied on.
  * BH: Added <nobr> to two places (Clients/Job ID, Pager column names) where
        IE6 would wrap the text in a narrow column header onto two lines.
  * PC: Updated test data for latest schema.
  * PC: Updated cats database setup sql scripts, and update sql scripts.
  * PC: Added Phone number Search field for Candidates.
  * PC: Updated some Icons.
  * PC: Added validator function to check valid site name in settings
        administration panel.
  * PC: Modified installtest to check database schema.

06/25/2006
  * BH: Changed all occurrences of index.php with function that looks up
        what script is called, so user can rename index.php to index.php5.
        This resolves: http://www.catsone.com/forum/viewtopic.php?id=115
  * BH: Cleaned up look of alphabet page select.
  * BH: Added tooltips to alphabet page select.
  * BH: Modified some tooltips for IE5 Compatibility (z-index issue).

06/24/2006
  * BH: Fixed "Special characters like & break AJAX suggest function" (bug
        0000152).
  * BH: Added modified column to candidate search (bug 0000146).
  * BH: Edited some formatting changes so CATS is usable and presentable
        with a default install of IE 5 (Windows 2000 SP4).
  * BH: Removed '(Most Recently Modified First)' indicator when the user
        is sorting by another method.
  * BH: Added autocomplete="off" to various add / edit entry forms, login page.
  * BH: Made cats remember the 'only my clients/contacts/candidates' setting
        when doing a sort.
  * BH: Added an alphabet for switching pages by first letter of sorted
        field onto contacts, clients, and candidates tabs.
  * BH: Modified page dropdown menu to include range of data on each page.
  * BH: Removed asterisk indicating required data from AddUser and EditUser
        templates.
  * BH: Fixed a bug in new version check that happens on initial installation.
  * BH: Fixed a bug in left company flag that happens when a client has no
        contacts marked still at company (bug 0000064).

06/23/2006
  * BH: Added 'Left Company' field to Edit Contact (schema change, bug 0000064).
  * BH: Added tooltips to checkbox fields in various forms (hot, left company).
  * BH: Added JavaScript powered filtering of clients contacts lists
        for left contacts (bug 0000064).
  * BH: Added email field to user profiles (bug 0000117).
  * BH: Added password reset ability to edit user.
  * PC: Added change site name field to Administration page.
  * PC: Removed documentation for simpletest.

06/22/2006
  * BH: Temporarily disabled AJAX suggest functionality.
  * BH: Fixed a few bugs in AJAX suggest JS.
  * BH: Added automatic update function, currently looks for
        localhost/nothing.php (modified schema).

06/22/2006
  * PC: Changed INSTALL documentation for upgrading from previous versions.
  * PC: Added upgrade Scripts for previous versions of database.
  * PC: Added search by day feature to the activities module.
  * PC: Fixed bug in activity module and refined GUI.
  * PC: Included simpletest php library in lib directory and updated links in
        test files.

06/20/2006
  * PC: Added Activities module.
  * PC: Fixed bug with missing js/pipeline.js file.
  * PC: Added 16x16 candidates icon and modified contacts icon.

06/15/2006
  * AS: Added Boolean Mode for fulltext resume search.
  * AS: Pointed template pager function in fulltext search to Pager.php
        rather than TemplateUtility.php.

06/13/2006
  * PC: Searches for clients now highlights hot clients (bug 0000149).
  * PC: Calendar not alerts user when no event or description is defined.
  * PC: Fixed bug where Job Orders displays last activity.

06/12/2006
  * PC: Quick Search Everything now retains search string.
  * AB: Truncate Key Skills column in candidates list view.
  * AB: Fixed some column truncation stuff.
  * AB: Zipped up "Hello" module and put under examples. Removed
        hello module.

06/11/2006
  * WB: Added postback-sorting to Pager; implemented on several pages (bug
        0000074).

06/07/2006
  * PC: Added highlight table rows javascript and implemented it in
        Job Orders, Clients, Candidates, Contacts (bug 0000058).
  * PC: Fixed headers in tpl files for highlightrows.js (bug 0000058).
  * PC: Fixed syntax error in contacts.

06/06/2006
  * PC: Clients and Contacts Search page now retains search string from
        previous search.
  * PC: Icon work (in progress).

6/5/2006
  * PC: Converted all png icons to transparent gif's. Removed png files.
  * AB: Disabled email notification on demo login.

6/04/2006
  * WB: Added postback-sorting support to Pager (initial commit, bug 0000074).
  * WB: Documented Pager class (bug 0000153).
  * WB: Start of adding postback-sorting to the Candidates module (bug
        0000074).

6/03/2006
  * WB: Lots of minor code cleanups.

6/02/2006
  * BH: Added AJAX suggest lookup of clients in edit contacts form.

6/01/2006
  * PC: Job Orders Search page now retains search string from previous
        search.
  * AB: Minor CSS fixes for Opera.

5/31/2006
  * BH: Added isHot to contacts (modified schema).
  * BH: Search results show hot contacts in red too.
  * WB: Fixed many test cases from failing (a few still fail). NOTE: The
        automated test suite should be run before each commit. Failing tests
        should be fixed BEFORE committing. 63 test cases failing is too much.
  * WB: Test for presence of "can relocate" field for candidates in ATF.
  * WB: Cleanups to Google Maps code in Clients.

05/28/2006
  * WB: Refactored module UI classes to move some common functionality up to
        UserInterface.
  * WB: Fixed a small PHP error in the city/state lookup functions in ajax/.
  * WB: Cleaned up ModuleUtility code quite a bit, including speed
        improvements to directory scan methods.
  * WB: Added MODULES_PATH constant to config.php.
  * WB: Ran scripts/killwhitespace.sh.
  * WB: Added ModuleUtility::_fatal() private method.

05/25/2006
  * AB: Implemented Plugin/Extension framework (still a work in progress).
  * AB: QuickSearch form validation. Will not submit on empty field (bug
        0000071).
  * AB: Took some CSS hacks (for stupid IE) out. Clutters the Javascript
        console.
  * AB: Took Logout tab out. Its now a link on the top right corner. Removed
        today's date; not really useful.
  * AB: Fixed a bug in UserInterface::fatal() that was causing a fatal
        error. fatal() now has a reference to the calling module, so
        TemplateUtility::printTabs in Error.tpl displays properly.
  * AB: Abstracted URL generation in MRU class.
  * AB: MRU items are now removed when a data item is deleted (bug 0000075).

05/24/2006
  * AB: Added key skills column to candidates listing
  * AB: Added AJAX zip lookup for City/State when adding a client
  * AB: Hide AJAX indicator if no response from server

05/19/2006
  * AB: Created a site backup script.
  * AB: Resume Search on "Search Candidates" screen retains last search in
        the edit field.

05/18/2006
  * AB: Clients support attachments now (bug 0000112).
  * AB: Contacts list shows icon if attachments are present (bug 0000136).
  * AB: Job Orders list shows icon if attachments are present (bug 0000128).
  * AB: Removed db dump from version control.
  * AB: Fix for email -> email1 change.
  * AB: Added server response time display in footer.

05/16/2006
  * AB: installtest.php now does not die prematurely if a test fails. Goes
        through all requirements and shows red/green proceed accordingly.
  * AB: Improved search results formatting

05/09/2006
  * AB: Pipeline table on Job Order Details shows attachment icons now.
  * AB: Rearranged repository into trunk, branches, and tags.
  * AB: Removed some files doc folder. They are now in a separate repository,
        cats-core.

05/07/2006
  * AB: Sync, sync, and more sync.
  * AB: Merged all code in to CATS (1 version now).
  * AB: CATS db is now re-indexed with proper index names.
  * AB: Start of version 0.5.5.
  * AB: Fixed a small error with "undefined index for isHot" due to E_ALL on
        catsone.net.
  * AB: Show paperclip icon on candidate listing page if attachment present.
  * AB: Schema changes.

05/04/06:
  * AB: Added timestamp field to attachments display on candidate page

05/01/06:
  * WB: Fixed report data for Yesterday statistics in Reports module (bug
    0000145).

04/30/06
  * WB: Added support for marking clients as hot (bug 0000137).

04/28/06
  * AB: New SysUtility library.
  * AB: Added basic debug/logging function.
  * AB: Added isUNIX(), isWindows() methods to new SysUtility library.

04/27/2006
  * BA: Added 2nd e-mail address for candidates (bug 0000122).
  * BA: Added a "can relocate" checkbox for candidates (bug 0000114).
  * WB: Cleaned up "can relocate" / 2nd email address for candidates changes;
    new db dump (bug 0000122, 0000114).
  * AB: Uploaded attachments for candidates shows proper icon for doc types
    (doc, xls, ppt, pdf, txt, jpg, and generic)

04/17/2006
  * WB: New database field in candidates table: can_relocate (1/0) (new db
    dump) (bug 0000114).
  * WB: Fixed a possible race condition in data item libraries where an
    INSERT can occur between when a row is added and its ID is retrieved from
    the database.
  * WB: Always use sprintf() for SQL queries.

04/16/2006
  * WB: Restructuring of Add / Edit Candidate form fields.
  * WB: More work on pipeline status (new db dump).

04/15/2006
  * WB: Documentation updates in Users.php.
  * WB: Removed Users::getPassword(); no longer needed.

04/14/2006
  * WB: Documented JobOrders::getAll() parameters.
  * WB: Documentation updates in Candidates.php.
  * WB: Added Users::getIDByUsername() method for looking up user ID's by
    username.
  * WB: Forgot Password e-mails now include a to-name (user's full name).
  * WB: Forgot Password e-mails now include a from-name (configurable in
    config.php).

04/13/2006
  * WB: Updates to doc/DEVELOPMENT-GUIDELINES.
  * WB: Renamed DatabaseConnection::makeQueryIDOrNull to makeQueryIDOrNULL().
  * WB: Renamed PASSWORD_RESET_* constants to FORGOT_PASSWORD_* for
    consistency.
  * WB: $onlyHot variable in JobOrders::getAll() should be a boolean, not an
    integer.

04/12/2006
  * WB: Finished renaming Site Settings to Administration internally (bug
    0000027).
  * WB: Added access level checking for internal SettingsUI::onDelete()
    method; security risk.
  * WB: Code cleanups in Settings module to make syncing with -asp easier.
  * WB: Misc. code cleanups.

04/11/2006
  * WB: Added test cases for Current Employer for candidates.

04/10/2006
  * WB: Attachments should work correctly under Windows now. Thanks to
    luxotek on the CATS forums.

04/09/2006
  * WB: New DB dump.
  * WB: Fixed bug in JobOrders::addCandidateModal() caused by fields being
    out of sync with Candidates::add() (found by automated testing).
  * WB: Add a row to the installtest table for query testing by the automated
    tester (new DB dump).
  * WB: Added checking for UPDATE query permissions in installtest.php.
  * WB: Removed last remaining ereg() call in StringUtility (replaced with
    preg_match()).
  * WB: Better test case report messages in unit test cases.

04/08/2006
  * WB: More LICENSE formatting.

04/06/2006
  * WB: Updated database schema dump.
  * WB: Test case documentation updates.
  * WB: Added test cases for adding and editing users in the Settings module
    (bug 0000116).
  * WB: Minor code-formatting fixes (missing spaces between comma and next
    function argument.
  * WB: More robust input validation in Manage Users (Settings module).
  * WB: Start of 0.5.5.
  * WB: Fixed LICENSE formatting.
  * AS: Saved new DB dump to reflect current_employer (bug 0000093).
  * AS: Added ForgotPassword.tpl (bug 0000070).
  * AS: Added logic to ForgotPassword.tpl for error message handling:
    if username not found, unable to send email (bug 0000070).
  * AS: Implemented Mailer class to send password reminder emails (bug
    0000070).
  * AS: Added section to config.php for password reset requests, email
    formatting, etc.
  * AS: Added current_employer to table candidates (bug 0000093).
  * AS: Added current_employer logic to CandidatesUI.php (bug 0000093).
  * AS: Added current_employer parameters to add() and update() in
    lib/Candidates.php (bug 0000093).
  * AS: Added current_employer fields to candidates/Show.tpl, Add.tpl,
    and Edit.tpl (bug 0000093).
  * AS: Renamed "Site Settings" to "Administration" (bug 0000027).
  * AS: Added "Only Hot Jobs" checkbox to JobOrders.tpl template, added
    $isHot parameter to JobOrders::getAll(), and logic to JobOrdersUI.php (bug
    0000094).
  * AS: Added label tags to JobOrders.tpl for checkboxes on top of page.

04/05/2006
  * WB: Fixed text on submit button on Add User page (was "Save", should be
    "Add User").
  * WB: Show proper test descriptions for delete*() methods (for deleting
    test data items) in CATSWebTestCase.
  * WB: Corrected a regular expression in some web test cases that should
    have been matching literal parentheses but were instead matching only the
    item contained within them.

04/04/2006
  * WB: Renamed the printTopRight() method to printHeaderBlock() in the
    TemplateUtility class.
  * WB: Documented TemplateUtility (bug 0000063).
  * WB: Fixed browser detection code to properly detect Konqueror.
  * WB: Improvements to AJAX testing framework.
  * WB: There are now "Select All" checkboxes for individual groups of tests
    as well as the true "Select All" checkbox.
  * WB: Added AJAX test cases for getClientContacts (bug 0000067).
  * WB: Added AJAX test cases for getDataItemJobOrders (bug 0000069).
  * WB: All job orders for the parent client are now available in the
    Regarding dropdown for contacts. Job orders that are actually assigned to
    the contact are marked with a star (*) (bug 0000080).
  * WB: Job orders in the 'Regarding' field when adding activities are now
    sorted by job title (job orders actually assigned to contacts sort higher).
  * WB: Contacts list when adding job orders is now sorted by contact name.
  * WB: Improvements to automated testing for Calendar module (bug 0000115).
  * WB: Automated testing now looks for JavaScript date input fields when
    checking for required fields on Add / Edit pages.

04/03/2006
  * WB: Fixed a major error in JobOrders::onAddCandidateModal(). Reported
    by Mike Kempenich (bug 0000109).
  * WB: Automated testing improvements.
  * WB: Added test cases for Modal Add Candidate window in the Job Orders
    module (bug 0000110).
  * WB: Refactored CATS-ATF so that CATSAJAXTestCase now inherits from
    CATSWebTestCase.
  * WB: Added AJAX test cases for getClientLocation (bug 0000068).

04/02/2006
  * WB: Automated testing improvements.

04/01/2006
  * WB: Removed an incorrectly placed shadow.

03/31/2006
  * WB: Misc. template fixes.
  * WB: Corrected a PHP error caused when adding a job order from the Client
    perspective.

03/30/2006
  * WB: Improvements to installtest.php.

03/28/2006
  * WB: Added support for two more versions of Safari to the Browser
    Detection library.
  * WB: EditUser.tpl called 'onEditUser' instead of 'edit'; fixed. Reported
    by chiefdan92 on the CATS Forums (bug 0000108).

03/27/2006
  * WB: Fixed bug causing incorrect time when editing a calendar event
    with a minutes field of 00. Reported by DFelzke on the CATS Forums (bug
    0000107).

03/26/2006
  * WB: Start of 0.5.2.
  * WB: Updates to scripts/countcode.sh.

03/25/2006
  * WB: Removed 'reportIncorrectlyParsedAddress' functionality (same reason
    Feedback was removed; this does not apply to -opensource).
  * WB: After adding a user, transfer the User Details page instead of the
    Manage Users page.
  * WB: Show '*' next to required fields on Manage Users pages.
  * WB: More automated testing improvements.

03/24/2006
  * WB: Prevent PHP errors if viewResume (candidates) action is called
    without a keyword parameter.
  * WB: Grouped login and logout functionality into CATSWebTestCase::login()
    and CATSWebTestCase::logout() (bug 0000103).
  * WB: Added assertPOST() / assertGET() methods to CATSWebTestCase (bug
    0000103).
  * WB: Start of removing dependencies on sample data from AJAX test cases
    (bug 0000103).
  * WB: Automated testing for Search Contacts by Title.
  * WB: Finished removing dependencies on sample data from web test cases
    (bug 0000103).
  * WB: Corrected a PHP error from last commit.
  * WB: Automated testing of modifying 'recruiter' and 'owner' fields.
  * WB: Automated testing of AJAX functionality no longer depends on sample
    data (bug 0000103).

03/23/2006
  * AS: Renamed all instances of ResumeModal to ResumeView in
    modules/candidates.
  * AS: Yellow highlighting for keywords in ResumeView.
  * AS: Popup icon in modules/candidates/Search.tpl.
  * WB: Added 'This Year' and 'Last Year' summaries to Reports tab. Suggested
    by rvg on the CATS Forums (bug 0000102).
  * WB: Improvements to Automated Testing (bug 0000103).
  * WB: Added more test cases for DateUtility.
  * WB: Fixed small bug found during automated testing improvements:
    DateUtility::getDaysInMonth() should return an integer, not a string.
  * WB: Manage Site Users has been renamed to Manage Users (bug 0000105).
  * WB: Documentation updates.
  * WB: Added functionality to allow the Automated Tester to delete users.
  * WB: Added UserInterface::getTrimmedInput() method to allow for safe
    handling of string variables from forms when some of the expected fields
    are not specified.
  * WB: Removed a few unused JavaScript functions.
  * WB: Job Orders test cases no longer depend on sample data (bug 0000103).
  * WB: Grouped all 4 assertions that need to be run on every single
    page load in the web test cases into method
    CATSWebTestCase::runPageLoadAssertions() (bug 0000103).
  * WB: Added web test cases for new Reports module functionality added
    today; found and fixed two small cosmetic bugs while testing (bug 0000103).

03/22/2006
  * WB: Added Search Contacts by Title functionality. Suggested by Mike
    Kempenich (bug 0000097).

03/20/2006
  * WB: CSS fixes.
  * WB: More automated testing.
  * WB: Finished adding bottom-shadow effect to pages.
  * WB: GUI fixes.

03/19/2006
  * WB: Added an availability date for candidates. Suggested by Mike
    Kempenich (DDL Change, bug 0000090).
  * WB: Template improvements in Manage Site Users code.
  * WB: Fixed a PHP error on AddUser.tpl.
  * WB: Lots of HTML cleanups.
  * WB: More work on Settings web test cases.

03/18/2006
  * WB: Fixed bug causing calendar to schedule events at incorrect times if
    '00' was selected for minute. Thanks to sbreuer and DFelzke on the CATS
    Forums (bug 0000088).

03/14/2006
  * WB: Auto-populate client location information when adding a job order
    from the Client perspective. Reported by Mike Kempenich (bug 0000089).

03/11/2006
  * WB: GUI updates.

03/10/2006
  * WB: Test framework now uses SimpleTest 1.0.1alpha3+. Upgrade required.
  * WB: Added unit test cases for vCard support (bug 0000082).
  * WB: Removed extra spaces from vCard formatted names when a middle name is
    not specified (test cases... yay).
  * WB: Added CATS extension to SimpleTest's UnitTestCase to allow expansion.
  * WB: Added web test cases for submission reports.
  * WB: Added web test cases for vCard support (bug 0000082).
  * WB: vCard fields for e-mail and URL are no longer set if empty.
  * WB: Added more assertions from CATSUnitTestCase / CATSAJAXTestCase to
    CATSWebTestCase.
  * WB: Show OnHold / Full Job Orders on Job Orders display by default (bug
    0000086).
  * WB: Fixed bug in searchExcerpt() causing text chunks from being displayed
    correctly.

03/09/2006
  * WB: Demo Login e-mails just go to asim@ now.
  * WB: In sync with -opensource.
  * WB: Updated News section.


03/06/2006
  * WB: Misc cleanups (mostly related to move of fulltextEncode /
    fulltextDecode).

03/04/2006
  * WB: Fixed AJAX bug (constants.php not included in ajax.php). Reported
    by asmeets on the CATS forums.
  * WB: Misc cleanups / etc.

03/03/2006
  * WB: Removed Google Analytics code that was left over from Demo Site.
  * WB: Documentation updates in index.php / ajax.php.
  * WB: Moved fulltextEncode() and fulltextDecode() methods from Search to
    DatabaseConnection.
  * WB: <pre> tags should use a fixed-width font.
  * WB: Cleanups and optimizations to Search::searchExcerpt().
  * WB: Code cleanups to TemplateUtility.
  * WB: Added script to remove extra whitespace at the end of source files.

03/02/2006
  * WB: Fixed description for Delete access level.
  * WB: Code-formatting fixes for AddressParser.
  * WB: Shell script updates.
  * WB: Better calendar icon on popup calendars.
  * WB: Cleanups to images/.
  * WB: Code cleanups to Resume Search code.

03/01/2006
  * WB: Removed Feedback module. It is not needed in -opensource.
  * WB: Start of 0.5.1.
  * WB: Fixed a few AddressParser bugs.

02/28/2006
  * WB: Moved some things from config.php to constants.php.
  * WB: SVN ignores / release prep.
  * WB: Added Powered by CATS text.
  * WB: More release prep.
  * WB: Removed demo login info from Login.tpl.
  * WB: Start of 0.5 (BETA).

02/25/2006
  * AS: Added ResumeModal.tpl to SVN, was already completed
  * AS: Wrote script to update cats database for fulltext, will not
    post via SVN, will email to cats-core.
  * AS: Finished the fulltext replacement scripts
  * AS: Added fulltextDecode to Search::searchExcerpt(),
    Attachments::getResumeByID(), Attachments::getAll()
  * AS: Added fulltextEncode to search page, Attachments::Add()
  * AS: Added CATS modal headers to ResumeModal.tpl
  * AS: Made a display change to Search::searchExcerpt()
  * WB: Changed copyright holder back to Cognizo Technologies.
  * WB: Improvements to misc/newversion.sh.

02/24/2006
  * WB: Added JavaScript validation for User Administration pages (bug
    0000084).
  * WB: Changed copyright holder back to Cognizo Technologies.

02/23/2006
  * WB: Make sure MySQL module is loaded in index.php. User will just get a
    blank page if MySQL module is not loaded if we don't do this, which is
    confusing.
  * WB: More cleanups to user-related code in SettingsUI.
  * WB: Fixed two dead links (http://localhost/...) on User Administration
    pages.
  * WB: Feedback module passes tests if a mailserver is not present now.
  * WB: When adding a job order from the Clients perspective, contacts were
    not being populated in the Contact drop-down on Add Job Order; fixed.
  * WB: Start of INSTALL document.

02/22/2006
  * WB: More GUI updates.
  * WB: Documentation fixes in JobOrders and Attachments.
  * WB: Code cleanups in Attachments.
  * WB: Fixed broken SQL query in Attachments::add().
  * WB: Use lazy-includes for including MSWordToText.php in CandidatesUI.php.
  * WB: Fixed missing argument in call to MSWordToText::convert() in
        CandidatesUI.php.
  * WB: Calendar GUI updates.
  * WB: Cleanups to Search.tpl in the Candidates module.
  * WB: Fixed an undefined variable error in Search Candidates.
  * WB: Sync'd some search changes from -opensource.
  * WB: Fixed formatting for version string in footer.
  * WB: Sync'd more resume searching stuff from -opensource.
  * WB: Stop using a hack to hide automated tester login activity.

02/20/2006
  * WB: UserInterface::fatal*() methods no longer require an access level.

02/19/2006
  * WB: More GUI updates.
  * WB: Fixed a slow query in Users::get().
  * WB: CSS cleanups.
  * WB: Cleaned up licensing code in Users module a bit.
  * WB: Refactored UserInterface / module UIs so that accessLevel does not
        have to be passed to individual action methods.

02/18/2006
  * WB: Small fix to CATS directory in scripts/update_cats.sh for the
        staging server.
  * WB: UserInterface::isRequiredIDValid() now supports an optional parameter
        to allow IDs of '0'.
  * WB: Uploading attachments works now if resume status is set to false.
  * WB: Minor code-formatting fixes / etc.
  * AB: Updates to GUI / graphics improvements.

02/17/2006
  * WB: Minor variable-naming fixes.

02/15/2006
  * WB: Activity and Pipeline entries are now deleted when the parent client
        is deleted (bug 0000083).
  * WB: Refactored UserInterface so that userID and siteID don't have to be
        passed to each module UI action function.

02/14/2006
  * AS: Added new class: SearchByResumePager to /lib/Search.php, complete
        with mysql fulltext searching.
  * AS: Added new protected var and access method to Pager, totalRows
        and getTotalRows().
  * AS: Added section to /modules/search/Search.tpl for resume search handling
        with pager.
  * AS: Still to do on search function: make + = _PLUS subs, etc. Also need a
        popup or AJAX way to preview resumes?
  * WB: Code-formatting fixes for Users::update().
  * WB: Removed extra function getUserActivities() in Users.php.
  * WB: Small cleanup to ActivityEntries::getAll().

02/07/2006
  * WB: Renamed Users::userExists() method to Users::usernameExists().
  * WB: Users::usernameExists() now returns a boolean.
  * WB: Use DatabaseConnection::makeQueryString() method in Users::add()
        instead of DatabaseConnection::makeQueryStringOrNULL(), as the values are
        not optional.
  * WB: Reverted "inline" error messages; keep using fatal() until we come
        up with a better system-wide solution. Adding a "Back" button to fatal()'s
        might help for now.

02/06/2006
  * AS: Removed all fatal() calls except db error (in AddUser) in the
        AddUser and ProfileSettings areas of the settings tab; now displays
        inline error messages.
  * AS: Added third column to form table in AddUser, but only when an error
        occurs (for error handling).
  * AS: Remember all data but password in AddUser when an error occurs.
  * AS: Added td.tdDataErr to main.css for red error text to display next
        to bad fields.
  * AS: Changed popup errors to inline error in
        modules/settings/validator.js for consistency.
  * WB: Converted a few PC format files to UNIX format.
  * WB: Small code cleanups...
  * WB: Users::getNextUserID() was not needed; user_id is an AUTO_INCREMENT
        column in MySQL.
  * WB: "Manage Site Users" sub-tab should be an active link on Show User.
  * WB: "Manage Site Users" sub-tab should be an active link on Add User.
  * WB: Lots of minor bugfixes to add user code.
  * WB: Made return values for Users::add() (was addUser()) more consistent
        with other data item add() functions.

02/05/2006
  * AS: Added files ViewUser.tpl and AddUser.tpl to display UI for
        user viewing and adding.
  * AS: Added Users::getNextUserID(), Users::userExists($user_name),
        User::addUser(args), and User::getLicenseData(). They all do what
        their names say they do.
  * AS: Made a link from the view user detail page back to the user
        list page. Previously, it was either two non-intuitive clicks or the
        back button.
  * AS: Changed drop down access level menu to radio buttons, same
        DHTML functionality.
  * AS: Roots can no longer create new roots, SAs can no longer create
        new SAs.
  * AS: Added getUserActivities to ActivityEntries.php for a list of
        $limit most recent activities done across clients and jobs. Not
        implemented in the code anywhere yet; originally in Settings->ViewUser
        but removed per Asim.
  * AS: Made it impossible to add a user if the license won't allow it
  * AS: Made it impossible to change a user from disabled if the license is
        full.
  * WB: Fixed a really slow query in Users.php (found a way to do it without
        one of the joins).
  * WB: SVN keyword updates.
  * WB: Minor code-formatting fixes ('if(' -> 'if (', etc.).
  * WB: Changed some of the titles / etc on some of the new Users templates
        for consistency with the rest of the system.
  * WB: Used the standard 'details*' table CSS classes for ViewUser.tpl.
  * WB: Added alternating row colors to login activity table on ViewUser.tpl.
  * WB: Use ACCESS_LEVEL_* constants instead of access_level_id numbers.
  * WB: Fixed a security bug in the settings module; it was possible to
        access some restricted pages by accessing the URLs directly even if the
        user's access level doesn't permit it.
  * WB: Don't show both the accessLevelDescriptionLong field AND the
        accessLevelDescription field on User Details; accessLevelDescriptionLong
        contains all required info and more.
  * WB: Renamed settings/ViewUser.tpl to settings/ShowUser.tpl for
        consistency.
  * WB: Renamed all references to "View User" to "Show User" or "User
        Details".
  * WB: Removed some redundant WHERE conditions in a few SQL queries.
  * WB: Removed some redundant access level checking in some Settings module
        templates.
  * WB: Forgot a few things relating to the previous changelog entry on last
        commit.
  * WB: Added titles to access level radio buttons on Add Site User.
  * WB: Fixed a few copy-and-paste leftovers in Add Site User ('editUserForm'
        -> 'addUserForm', etc.).
  * WB: Fixed font size on access level description on Add Site User.
  * WB: Assigned accessLevel to fatal() calls in settings module.

02/04/2006
  * AS: added check in onEditUser() in modules/settings/SettingsUI.php
        to see if editor and editee were the same person; disabled option
        elements don't seem to submit.
  * AS: Changed $_GET['u'] in modules/settings/Users.tpl and
        /modules/settings/SettingsUI.php to $_GET['userID'] for the sake of
        consistency.
  * AS: "Back to Details" is now "Cancel" in modules/settings/EditUser.tpl
  * AS: Added modules/settings/EditUser.tpl to show a user edit screen
        accessible from the user list in the settings page. Made it impossible
        for a user to change his or her own security, regardless the level.
  * AS: Edited lib/Users.php to include get($id) function for just one
        user, an update($id) function for one user, getAccessLevels() to select
        all the possible user access levels (for display in a select element),
        and getLastLoginAttempts($id, $num) to display the last $num login attempts
        by user $id.
  * AS: Edited modules/settings/Users.tpl to include a link on the first
        and last name of each user affiliated with a site, but only for users
        with 400+ security.
  * AS: Edited modules/settings/SettingsUI.php to include two more
        actions, editUser and onEditUser
  * WB: Added 'user_licenses' column to database (new db dump).
  * WB: Small code-formatting fixes.

02/03/2006
  * WB: Code formatting fixes in DateUtility.php.
  * WB: More documentation in Users.php.

02/01/2006
  * WB: Code formatting fixes in DateUtility.php.
  * WB: MSWordToText can now convert multiple documents with one instance.
  * WB: Added getReturnCode() and getRawOutput() methods to MSWordToText for
        error handling.
  * WB: Documented MSWordToText.php.

01/31/2006
  * WB: Show "BETA TESTER" or "DEMO USER" in subject lines for such feedback
        e-mails.
  * WB: Show site name in feedback e-mails.
  * WB: Renamed 'hot' to 'isHot' (hot -> is_hot in database) for style
        consistency.
  * WB: Updates to doc/DEVELOPMENT-GUIDELINES (more to follow).
  * WB: A couple of small fixes to vCard support (bug 0000078).
  * WB: Renamed "Search Everything" to "Quick Search" internally to avoid
        confusion.
  * WB: Cleanups to TemplateUtility. Reduced code duplication in header
        printing methods.
  * WB: Fixed text encoding for modal dialogs.
  * WB: Misc. copyright updates and header fixes.
  * WB: Removed "File Author" field from headers. This is a maintenance
        hassle.
  * WB: Started a doc/AUTHORS file to list significant contributors to CATS.
  * WB: Method documentation for the Template library (bug 0000081).
  * WB: Unset $template after use in Template::display() to prevent variable
        name conflicts.

01/30/2006
  * WB: Synch'd some UserInterface support methods for server-side sorting on
    data tables from CATSAdmin: isSortDirectionValid() and isSortByValid().
  * WB: Cleanups to checkbox state checking in module UIs.
  * WB: Small documentation fix in CATSUtility().
  * WB: Added DatabaseConnection::getLastInsertID() method to support an
    upcoming change to properly fetch last AUTO_INCREMENT ID from MySQL (we
    are currently doing it wrong) (Synch'd from CATSAdmin).

01/28/2006
  * WB: Started adding names to CHANGELOG entries. Previous entries all by
    Will and Asim.
  * WB: New job order MRU icon.
  * WB: Small cosmetic fix to Login Activity page.

01/27/2006
  * Added the vCard 2.1 specification to doc/.
  * Moved vCard example to doc/NOTES.
  * Small bug fixes / testing to vCard support.
  * Fixed background color of Job Order MRU icon.
  * Added news entry for Calendar and for vCard support.
  * Fixed version string regular expression in the Home module test case for
    real this time.
  * Temporarily disable the quotes system (this needs to be a site setting or
    profile setting).
  * Browser detection code properly detects Safari Web Core v312.3.3.
  * Browser detection code properly reports blank User Agents.
  * Documentation in CalendarUI.php.
  * Added support for deletion of calendar events.
  * Moved VCard library to lib/. After rewrite, this code has no similarity
    to the original vCard code.

01/26/2006
  * Added vCard support to contacts.
  * Use lazy-includes to include vCard library in ContactsUI.php since it is
    only used by one action.

01/25/2006
  * Corrected a regular expression in the Home module version footer test case.
  * Fixed bug where certain characters in XML responses in AJAX code could
    cause the response to be unparsable by JavaScript.
  * Performance improvements to Template::display().

01/23/2006
  * Show "CATS Beta Tester" in "Top Right" area for beta testers (bug 0000059).

01/22/2006
  * Added support for two missing Safari builds to browser detection code.
  * loadModule() bails out now if the module name is invalid (bug 0000062).

01/21/2006
  * Cleaned up browser detection code quite a bit.

01/20/2006
  * Moved browser detection from CATSUtility to lib/BrowserDetection.php.
  * Cleanups to browser detection and hostname resolution in Settings module.

01/19/2006
  * Moved Session::_addLoginHistory() to Users::addLoginHistory().
  * Added a "beta tester" user classification (doesn't really do anything yet).
  * Documented MRU class a bit.
  * Removed unused function call to processLogout() on logout.
  * Fixed bug: "When tabbing through the fields while entering a new client the
    Key Technologies field is skipped over." (feedback entry).
  * Moved loadModule() (index.php) to CATSUtility::loadModule().
  * Added more test cases for the Feedback module (bug 0000061).
  * Show user's IP addresses in feedback e-mails.
  * Documented DatabaseConnection a bit.
  * Don't fatal() out in Feedback module if Mailer can't send an e-mail.
    Feedback is stored in the database anyway, and there is no need to give
    a user a bad impression by fatal()ing out.

01/18/2006
  * Added Session::getFullName() method to reduce code duplication.
  * Show Reply-To name and e-mail address in feedback e-mails.
  * Allow Name and E-Mail address to be left blank, even on demo sites.

01/17/2006
  * You can now specify a reply-to name and e-mail address for demo sites.

01/14/2006
  * Sites can now be marked as a "demo" site in the database to allow for
    demo-specific functionality. This might be a temporary thing.

01/13/2006
  * Fixed a bug in Calendar module that prevented users from scheduling events
    with PM times.
  * Mailer class can support Reply-To headers now.

01/12/2006
  * Added Edit Calendar Event template file and template logic.
  * Less code duplication when generating time drop-downs on Add/Edit Calendar
    Event templates.
  * Added Edit icons for calendar events on Show Date in Calendar module.
  * Added a date_modified column to calendar_event table which is updated on
    edit.
  * Lots of little code formatting / style fixes in module UIs.
  * Method documentation in Calendar.php.
  * You can now edit calendar events.

01/11/2006
  * Fixed form naming JavaScript bug on Add Calendar Event.

01/09/2006
  * Fixed undefined variable bug in Add Job Order.
  * Resumes are now indexed (finishing touches to be added).

01/07/2006
  * More testing of resume parsing.

01/06/2006
  * Fixed incorrectly-used variable name bug in lib/Attachments.php.
  * Testing of resume parsing.

01/05/2006
  * Attachment titles are now generated automatically if a title is not
    specified.
  * You can no longer specify an attachment title at all. It is automatically
    generated.
  * Added methods FileUtility::getFileExtension() and
    FileUtility::getFileWithoutExtension() to allow for better handling of
    attachment uploads.
  * Removed some BBClone stuff temporarily.
  * Use the UserInterface::isChecked() method to see if a job is hot in
    JobOrdersUI.php (reducing code duplication...).
  * Simplified start date logic in JobOrdersUI.php.
  * Phone numbers are formatted correctly when added from the job orders
    perspective now.
  * Removed tight coupling between UserInterface and DatabaseConnection. All
    database string formatting is now done in the respective libraries.

01/04/2006
  * Start of allowing users to retrieve forgotten passwords.

01/03/2006
  * Added AJAX timeout handling to help resolve AJAX problems over unreliable
    networks.
  * Added Google Analytics for demo research.
  * Moved demo system from catsone.net/cats/ to catsone.net/.

01/02/2006
  * Hide the "Add Activity" link when clicked to avoid duplicate AJAX requests.

01/01/2006
  * Updates to Login.tpl CSS/HTML.

12/31/2005
  * 2006 copyright updates.
  * Copyright holder is now CATS, LLC.

12/30/2005
  * You can now add a calendar event to a specific date via the Show Date page.

12/29/2005
  * Notes / Description field for data items renamed to "Miscellaneous".
  * Miscellaneous -> Misc.
  * Calendar events can now be scheduled for a specific time of day (or "all
    day").
  * Show scheduled times on main calendar page.

12/28/2005
  * Add Calendar Event works now.
  * DB schema updates.

12/23/2005
  * Forgot some CVS adds last night.
  * More work on Add Event.

12/22/2005
  * Sort calendar events by time.
  * Show Date in Calendar module works now.
  * Fixed a bug caused by me using the incorrect syntax for checkdate().
  * Renamed the Calendar::getCalendarEventArray() method to
    Calendar::getEventArray().
  * Start of allowing users to add events to the calendar.

12/16/2005
  * Some form buttons didn't have names; fixed.
  * Cleaned up CSS tab code quite a bit (again).
  * Renamed "current" CSS tab class to "active" (as inactive is named
    "inactive"...).
  * Added basic system test cases for Feedback and Calendar.
  * Refactored test framework a bit to make adding new test cases considerably
    easier. Adding a test case is now 2 steps (write test case, add test case
    to available test cases array) instead of 3 (write test case, add test case
    to available test cases array, add checkboxes for test case to form).
  * Added a reset button to the Change Password form.
  * Improved test cases for Settings module.
  * Removed unnecessary blank lines on Home.tpl.
  * Decreased body padding a bit. No noticeable side effects, and we have 2px
    more vertical and horizontal space.
  * Fixed an incorrect login error message (said 'Database Error' instead of
    'Invalid username or password.').
  * Use login page similar to Mambo's [LGPL].
  * Show date on Login page.
  * Fixed a PHP error in Calendar (automated testing rocks).
  * Corrected an error in a test case for the Login module.
  * Added a Reset button to the login page for uniformity.

12/15/2005
  * Events for Calendar are now pulled from the database (getting close!).
  * Made several improvements to ArrayUtility::findKeyValuePair():
      * It now returns the offset in the main array in which it found the
        specified key/value pair.
      * You can specify the number of matches to skip before it returns a
        match. This way, more than one matching key/value pair can be found
        by using a simple loop.
      * Split "strict" functionality into a separate function to increase
        performance.
      * Added test cases for new functionality.

12/14/2005
  * Style fixes to Calendar.
  * Show current date cell in a darker color on Calendar.
  * Calendar dates are now links to a detailed view of the selected date.
  * Show the current month on the "notes" bar above the calendar.
  * The month and year to display can now be specified in the URL string when
    calling the Calendar module's showCalendar action.
  * Fixed a potential problem in Pager classes where $this->_assignedCriterion
    could be undefined.
  * Added a 'Show Only My Clients' option to clients listing for uniformity.
  * Show more detailed information about the current view in the 'notes' bar
    on all 4 data item modules.
  * Added a faint border to the 'notes' bar.
  * Decreased margins and padding for a few elements at the top of the page.
    No noticeable side effects, and we have about 10px more vertical space.
  * ContactsUI::listAll() should have been named ContactsUI::listByView().
  * CandidatesUI::listAll() should have been named CandidatesUI::listByView().
  * Use lazy includes when including the Search library in modules. This should
    be a fairly significant performance improvement.
  * Cleaned up many switch statements throughout the code (mainly removing code
    duplication in default: branches by adding fallthroughs).
  * 'notes' bar is now correctly aligned with the right edge of the listing
    tables.
  * More CSS cleanups.
  * Updated Project file.
  * Improvements to navigation in Calendar.
  * Testing calendar events.

12/13/2005
  * Start of Calendar module / tabs.
  * Created Calendar DB schema.
  * Made tabs slightly smaller and also increased space in the tab row by
    increasing it's width (doesn't have any side effects).
  * Added Calendar module UI.
  * Fixed a small bug when displaying the last activity note for a job order
    if the activity notes field is NULL.
  * Added AJAX test cases for Edit Activity.
  * CSS cleanups.
  * Made tabs even smaller.
  * Added a demo calendar.
  * Added DateUtility::getStartingWeekday() method to support Calendar.
  * Start of Calendar class.
  * Added Calendar::getCalendarRows() method to support Calendar.
  * Calendar page is now auto-generated (still needs work).
  * scripts/codecount.sh now shows a grand total.
  * scripts/codecount.sh now separates thousands with a ',' to make output
    more clear.

12/12/2005
  * Improvements to AJAX test framework (added new assert methods, etc.).
  * Wrote test cases for adding and deleting activity entries.
  * Fix phone number formatting on Search Everything.

12/09/2005
  * Start of test cases for AJAX server-side functions.
  * Added MS Project file to repository.
  * Improvements to AJAX test cases.
  * More updates to test framework.

12/08/2005
  * The number of clients per page is now configurable in config.php as was
    the number of contacts per page already.
  * Sort candidates by last modified on Candidates page and use a pager.
  * Added "Only My Candidates" checkbox to the Candidates page.
  * Show 'None' instead of ",." on search output if owner is NULL on Candidates
    page.
  * Fixed a PHP error if you try to run the action 'attemptLogin' on the Login
    module without any POST data.
  * Last modified date for candidates was not set when adding a candidate
    (fixed).
  * Removed a bunch of no-longer-needed pipeline notes code.
  * Forgot to include Pager.php in Candidates.php.
  * Show last activity note in candidate pipeline on Show Job Order (will be
    improved).
  * On "Consider for Job Order" searches, instead of adding pipeline notes
    after selecting a candidate, we add a first activity note to the candidate
    (associated with the job order).
  * Users.php was being included in a bunch of places where it doesn't need to
    be (Session requires it to be included globally).
  * Minor HTML fixes.
  * Cleanups to CATS Test Framework.
  * Start if AJAX test cases.

12/07/2005
  * Switch to using "onclick" attribute in activity.js with setAttribute()
    instead of element.onclick, as the element.onclick events seem to be
    getting lost during cloning.
  * Fixed a few activity-related template problems in the Contacts module.
  * Added a joborder_status table and 'Placed' and 'Lost' status flags in
    preparation for switching to a flag-based job order status system.
  * Renamed ATSUtility to CATSUtility.

12/06/2005
  * Separate out client location retrieval from lib.js.
  * Better error handling for editing activity entries.
  * Removed some leftover JavaScript in Add Activity code which was causing
    duplicate ID problems when adding activities.
  * Users now have a flag which can prevent them from changing their password.
    This may be useful for group accounts, etc.
  * Removed one FIXME.
  * Fixed activity action icon spacing.
  * Prevent duplicate Edit / Delete clicks on activity table action icons.
  * Documented Mailer class.

12/05/2005
  * JobOrders::getAll() can return all job orders associated with a candidate
    now.
  * Added getDataItemJobOrders AJAX function for retrieving all job orders
    associated with a data item.
  * More progress on assigning an activity entry to a job order.
  * A PHP error occurring will now trigger a JavaScript error in the AJAX code.
  * My query logic for retrieving candidate job orders was messed up;
    getDataItemJobOrders AJAX function works now.
  * Reverted previous changes to JobOrders::getAll().
  * Add activity (including assigning a job order) works now. Edit still needs
    work.
  * Indent JOINs in a way that makes them less confusing (to me, at least) in
    SQL queries.
  * Edit activity AJAX works now.
  * Use a spinner when clicking Add Activity.
  * Separate out client contact retrieval from lib.js.

12/04/2005
  * Split up pipeline JavaScript from lib.js.

12/02/2005
  * AJAX code can allow activity entries to be associated with a job order
    internally. I need to commit so I can do some code restructuring without
    losing the ability to see my changes easily with svn diff. Don;t update
    on CATSOne; some JS code is not working right now.
  * Start of simplifying some of the Activity DHTML stuff in lib.js.
  * Moved activity JavaScript code to js/activity.js. No need to include 800
    lines of unused code in every single page.
  * More cleanups to activity JavaScript.
  * New activity code seems to work now. It needs a bit of refinement and a
    spinner still. Don't update CATSOne yet.

12/01/2005
  * Newly-created activity entries are now properly aligned.
  * Added a basic Mailer class that adapts PHPMailer.
  * Added error handling for Mailer class.
  * Send notification e-mails when feedback is received.
  * Added PHPMailer English language file to the repository.
  * Don't strip HTML from fatal() error messages.
  * Activity entries can be associated with a job order ID now.
  * Allow activity entries to be associated with a job order internally.
  * Added proper header to login/LoginUI.php.
  * Basic support for displaying the job order associated with an activity
    entry.

11/30/2005
  * Start of Feedback User Interface.
  * New settings icon.
  * Rename candidate "Notes" to "Description" on Add Candidate.
  * Added Feedback form.
  * Added "feedback" table to the database (new DB dump).
  * Feedback form works now.
  * Added Error template to Feedback module.
  * Added form validation for the feedback form.
  * Show a success page after feedback is submitted.
  * Added a news section to the home module.
  * Use smaller fonts for automated tests output (9pt instead of 12px).
  * Don't allow a user to even SEE the "Add Job Order" page unless there is at
    least one client in the system.

11/29/2005
  * AddressParser can handle address blocks with e-mail addresses attached to
    the ends of lines, etc., now (by removing the e-mail address from the
    buffer after it is extracted).
  * AddressParser can handle addresses where the "address line one" is the last
    line in the address block now.
  * Added StringUtility::removeEmptyLines() method to replace buggy blank-line
    removal code in AddressParser.
  * Added StringUtility::removeEmailAddress() method to remove e-mail addresses
    from a string.
  * Removed my [Will] techmonkeys e-mail address from test cases (oops). I get
    enough spam as it is :).
  * Use assertIdentical() instead of assertTrue($x === $y) in test cases.
  * Added test cases for StringUtility::removeEmailAddress().
  * Added test cases for StringUtility::removeEmptyLines().
  * Modified Show Contact and Show Client so that long notes don't stretch
    summary table.
  * Documentation updates.
  * Added Feedback tab (not yet functional).
  * Tabs are now slightly smaller so they all fit.

11/28/2005
  * When an activity is added, deleted, or updated, the "parent" data item's
    modified timestamp is updated now.
  * Cleaned up Activity_fillTypeSelect() in lib.js quite a bit.
  * Added multiple types of calls for activities: LVM, Missed, Talked.
  * Merged some CATS notes from my [Will] personal notes file into doc/NOTES.
  * Merged joborders/AddCandidateModal.tpl and candidates/Add.tpl into a
    conditional template. This was a maintenance nightmare.
  * Fixed a few formatting problems with Show.tpl conditional templates for
    candidates and joborders modules.
  * 'assigned_to' is now 'recruiter' internally to avoid confusion.
  * Change Password and the Login module now trim whitespace from usernames
    and passwords. This helps if a user copies and pastes a username / password
    into the form.

11/27/2005
  * Made recruiter default to the current user on Add Job Order.

11/23/2005
  * Added assertCATSFatalErrors() and assertNoCATSFatalErrors() methods to
    simplify web test cases.
  * Show 'Days Old' on Job Order Details.
  * Fixed JavaScript form validation for Change Password.

11/22/2005
  * Added assertHTTPResponseOk() method to simplify web test cases.
  * Fixed an error in search test cases for all modules.
  * Test for PHP errors everywhere during web tests.
  * Put Owner and Created fields on the same place on the Details page for all
    data item modules.
  * Improved tests for Settings module (found one bug already! (see below)).
  * First and Last name links on Login Activity page pointed to the contacts
    module; fixed (they shouldn't point to anything until we have a Show User
    page).
  * Re-enable contact select list and hide spinner of an error occurs within
    contacts auto-population JavaScript code.
  * Explicitly use GET on view-selector forms.
  * Split up custom SimpleTest extensions / test framework classes into their
    own files.

11/21/2005
  * Finished eliminating "underscore_style" variable names. A few more commits
    will probably follow in the next few hours. This is a pretty big commit.
  * Fixed job order searching on Search Everything.
  * Show 'None' instead of ",." on search output if owner is NULL on Search
    Candidates.
  * Show 'None' instead of ",." on search output if owner is NULL on Search
    Contacts.
  * We now fatal() out of Show and Edit pages for data items if the ID doesn't
    appear to exist in the database.
  * Moved some web testing configuration data to config.php.

11/20/2005
  * Show client billing contact on Client Details.
  * Show a spinner while waiting for a response from getParsedAddress.
  * Start of eliminating "underscore_style" variable names. This was getting
    to the point where it was making the code difficult to maintain.

11/18/2005
  * Fixed minimum page height in Internet Explorer (using an ugly CSS hack
    since IE sucks).
  * Disable edit box and arrow button while waiting for a response from the
    server-side address parser on Add Candidate.
  * Support a billing contact for a client in the database.
  * Less-confusing variable names in some of the existing code in lib.js.
  * Show job orders assigned to a contact on its details page.
  * Show contact phone number and e-mail on Job Order Details.
  * Fixed CATS logo from being chopped-off by Internet Explorer.
  * Use AJAX_ENCODING everywhere it needs to be used.
  * Add HTML_ENCODING constant for use instead of hard-coding UTF-8.
  * Added support for client billing contacts.

11/17/2005
  * Use AJAX client contact auto-population on Edit Job Order.
  * Populate the dropdown list of client contacts from the server and select
    the current contact in the dropdown list by default when a job order is
    edited.
  * Show assigned-to contact on Job Order Details.
  * Added Users::changePassword() method to support Change Password in the
    Settings module.
  * Moved login status flags to Users.php.
  * The settings module can fatal() now (the Error.tpl template was missing).
  * UserInterface::fatal() and UserInterface::fatalModal() support access
    levels now.
  * Users can change their passwords now.
  * Use the same header in tests.css as main.css.
  * Cleaned up LoginUI.php a bit and made it more consistent with the other
    module UIs.
  * Moved login.css to login/ module directory.
  * Don't alert() on non-fatal error messages in client-contact retrieval.
  * alert() on fatal error error messages in client location retrieval.
  * Show main module listing tables even if empty.
  * Don't show pager navigation if there is only one page.
  * Fixed a couple of incorrect links for settings subtabs.

11/16/2005
  * We aren't using JavaScript date validation anymore, so I'm moving it to
    doc/todo.txt (which will be renamed to NOTES shortly).
  * Renamed doc/todo.txt to doc/NOTES.
  * Slightly more robust error handling for AJAX client side JavaScript.
  * We now use AJAX to auto-populate a dropdown list of client contacts when
    a client is selected when adding a job order.
  * AJAX address parsing code clears fields for which it didn't get a response
    now.
  * General cleanups to AJAX JavaScript code.
  * Fixed Owner field wrapping on Candidate Pipeline display on Job Order
    Details.

11/15/2005
  * Added getClientContacts AJAX "function" (start of auto-populating the
    contacts dropdown list when selecting a client).
  * Added Clients::getContactsArray() support method for getClientContacts
    AJAX "function".
  * Show contact owners on Client Details.
  * Use action icons for e-mail on Contacts table on Client Details.
  * Test for invalid query errors after clicking the main module link in test
    cases (just got one and we weren't testing for it).
  * JobOrdersUI supports assigning a job order to a contact now... just have
    to do a bit more AJAX / DHTML stuff (NOTE: need to import latest DB dump).
  * Factored-out some of the logic for "optional IDs" (contacts, owners, etc.)
    into UserInterface.
  * Created assertNoQueryErrors() expectation to simplify testing for invalid
    query errors.

11/14/2005
  * Moved Login test cases from UnitTests.php to WebTests.php (must have been
    lost in the split).
  * Made output of some test cases more user-friendly.
  * Removed ShowPopup.tpl (leftovers).
  * Added fields for assigning a job order to a contact (right now, None is the
    only choice).
  * Better test cases for search pages.
  * Don't preform assertions for setting fields.
  * Don't use case-insensitive regular expression matching in test cases.
  * Added CATSWebTestCase class (extends WebTestCase) to allow for better test
    reuse and more descriptive output messages.

11/12/2005
  * Test HTTP response codes in web test cases.
  * Simplify login in individual web test cases. Use a single post query
    instead of browser emulation functions.
  * Login and password information for test user are now constants so they can
    be changed in one place.

11/11/2005
  * Send back an AJAX error in getClientLocation if Clients::getLocationArray()
    returned an empty array.
  * Use client location auto-population on Edit Job Order also.
  * Renamed Search Everything text from Search All to Quick Search.
  * Added AJAXInterface::isRequiredIDValid() method for checking POST IDs.
  * Input validation for AJAX functions. This is a step toward making our AJAX
    stuff a lot more robust.
  * Client location auto-population JavaScript sets city and state to blank now
    if an error occurs.
  * Added a quick little code counter script (scripts/countcode.sh).
  * Renamed main2.css to main.css.test so it doesn't get counted by the
    countcode script.
  * Don't search job orders by start date in Search Everything (how did that
    happen?)
  * Show proper columns on job order pipeline on client details.
  * Move functionality of Clients::getJobOrders to JobOrders library.
  * Move functionality of Clients::getContacts to Contacts library.
  * Added flags for job order status (and combinations thereof) (start of
    converting job order status to a flag-based system).
  * Combined get*() job order functions. Using status flags allows us to
    easily combine these functions using a switch. This will allow for easier
    code maintenance and eliminates a ton of duplicated code.

11/10/2005
  * Added Clients::getLocationArray() support method for getClientLocation
    AJAX "function".
  * Errors from ajax.php are now proper XML.
  * Refactored "secure AJAX" "functions" a bit. Login checking is now done in
    SecureAJAXInterface.
  * Moved AJAX encoding settings to config.php.
  * Show primary phone number on clients search.
  * Refactor AJAX JavaScript code a bit to reduce function call overhead
    between readyState changes by checking http.readyState sooner.
  * Removed unused preventDefault() function in lib.js.
  * Auto-populate job order location fields when selecting client (AJAX).

11/09/2005
  * "Top right" text is no longer bold.
  * Database connection errors die() now.
  * Show key technologies in clients search.
  * Normalize contact phone numbers on add and edit.
  * Allow contacts to be assigned to "None".
  * Allow clients to be assigned to "None".
  * Allow candidates to be assigned to "None".
  * Allow job orders to be assigned to "None".
  * Added getClientLocation AJAX "function" (start of auto-populating client
    location on job orders).

11/08/2005
  * No empty searches in Candidates module.
  * No empty searches in Clients module.
  * No empty searches in Contacts module.
  * No empty searches in Job Orders module.
  * Show candidate owner in job order pipeline.
  * Show job order owner in candidate pipeline.
  * Show "Never" on Manage Site Users if they never logged in successfully
   (and same thing for unsuccessful).
  * Show pipeline and submitted counts for candidates.
  * Make candidate display more consistent with job order display by moving
    notes and attachments out of the "main" summary table, renaming notes to
    description, and changing "Job Order History" to "Job Order Pipeline".
  * Separate pipeline and submitted counts on job order display.
  * Attempt to normalize phone numbers when adding and editing candidates.
  * Attempt to normalize phone numbers when adding and editing clients.
  * Smaller CATS logo for more page space.
  * Merged Show.tpl and ShowPopup.tpl in candidates module (job orders next);
    this was a maintenance nightmare.
  * Merged Show.tpl and ShowPopup.tpl in job orders module. See above.

11/07/2005
  * Remove client contacts when deleting a client.
  * New, more compact pager navigation.
  * Remove client job orders when deleting a client.
  * Use new "detailsInside" and "detailsOutside" table classes for Show Job
    Order.
  * Added test cases for editing clients.
  * Added test cases for Search Clients.
  * Added test cases for editing contacts.
  * Added test cases for Search Contacts.

11/04/2005
  * Contact details makes better use of horizontal space now.
  * Automated testing of adding, editing, and deleting a candidate.
  * Add 'department' to the expected field list for contacts in testing code.
  * Split test case files into Unit and Web test cases.
  * Renamed 'FT' (Full Time) job order type to 'H' (Hire).
  * Added a 'C2H' (Contract to Hire) job order type.
  * Show job order type description on details page.
  * Template cleanups.
  * Search everything finds contacts again (oops).
  * Reduce calls to DatabaseConnection::makeQueryString($wildCardString) in
    search functions.
  * Added test cases for Search Job Orders.
  * Added test cases for Search Candidates.
  * Set focus back on the correct field when using the second / third search
    fields on search pages.

11/03/2005
  * Fixed a typo in a variable name on Show Client.
  * StringUtility::extractURL() is a bit more robust now.
  * Client details makes better use of horizontal space now.
  * Contacts have a department field now.
  * You can no longer search contacts by title.
  * Renamed Notes to Internal Notes in the Job Orders module.

11/02/2005
  * Added JavaScript delete confirmation for attachments.
  * Removed key skills searching on "Add Candidate to This Job Order Pipeline"
    dialog.
  * Removed minimum rates for job orders.
  * Removed StringUtility::getRateStringFromMinMax().
  * Candidate details makes better use of horizontal space now.

11/01/2005
  * Attachment metadata entries can now contain a "stored filename" as well as
    the original filename.
  * Update date_modified when an attachment is created.
  * Attachment metadata entries can now contain whether or not an attachment is
    a resume.
  * More progress on handling attachment uploads (still not complete).
  * Uploading attachments works now. Word parsing isn't tied in yet and it
    needs some cosmetic improvements, though.
  * Transfer the correct page after an attachment upload.
  * Fixed a typo on the Close button on Add This Candidate to Job Order
    Pipeline dialog.
  * Fixed DHTML numeric table sorting so that it sorts by numeric value instead
    of ASCII value.
  * DHTML table sorting can properly sort "short" dd-mm-yy dates now.
  * Use fatalModal() instead of fatal() for erroring-out in attachments
    dialogs.
  * Added attachment deletion.

10/31/2005
  * Start of attachment uploads.
  * Fixed modal dialogs so they no longer have bottom scrollbars.
  * CSS cleanups.
  * Changed default URL for scripts/getremotebuild.sh.
  * Create attachment initial dialog works now.

10/30/2005
  * StringUtility::tokenize() always returns an array now.
  * You can now search for a client by key skills.

10/29/2005
  * Login page uses its own CSS file since there is no overlap in what is used
    on the Login page and what is used inside the application.
  * Login page has no scrollbar now.
  * More automated testing of adding a client.
  * Clients and Contacts added by the automated tester are deleted
    automatically now.

10/24/2005
  * Correctly center the login page (broken when the body width was limited
    to 955px.
  * Refactored MRU out of Session and into a new MRU class.
  * Rafactored parts of login validation out of Session and into User to allow
    Change Password to reuse code.
  * Make sure session.auto_start is disabled in php.ini in index.php.
  * A few cleanups in UserInterface.
  * Documented UserInterface methods.
  * Removed unused / unneeded method Session::getUserAgent().
  * Renamed TemplateUtility::getAlternatingRowClass() method to
    TemplateUtility::printAlternatingRowClass() since it echo's data.
  * Added Search::searchClientsByKeyTechnologies().
  * Use named constants instead of global variables for configuring database
    connection information.
  * Cleaned up the AddressParser code a bit.
  * AddressParser can handle 'addresses' like:

        Will Buckner
        will@cognizo.com

    Now. (Also added test cases to ensure this doesn't break.)

10/21/2005
  * Renamed Sites to Users.
  * Better inactive tab colors.
  * Add Contact works again (oops).
  * Don't show "MRU:" if there are no MRU entries.
  * Automated testing for adding a client.
  * Automated testing detects query error messages everywhere now.
  * Fixed Add Job Order tab indexes.
  * CSS changes to the login page.
  * Disabled users can no longer log in.
  * Added a "Disabled" access level instead of using a separate "enabled"
    field.
  * AddressParser can handle addresses street addresses with a comma after
    the house number now.
  * Added a few unit tests for StringUtility::isStreetAddress().
  * Renamed 'Assigned To' to 'Recruiter' for added clarity.
  * Start of user management page under settings.
  * Populating drop-down client lists is a lot more efficient now.
  * scripts/newversion.sh modifies main.css too now.
  * Show "Top Right" information aligned with the right edge of the main panel.
  * Changed page titles from "Cognizo ATS" to "CATS".
  * Lots of CSS cleanups.
  * Fixes to tests.css needed from previous commit.
  * Template / CSS fixes.

10/20/2005
  * Start of renaming Engagement Notes to Pipeline Notes (takes two commits).
  * Finished renaming Engagement Notes to Pipeline Notes.
  * More action icons updates.
  * Show unowned data item owners as "None" on Search Everything.
  * Moved e-mail to an action icon on Candidates listing for consistency.
  * Show Primary Phone on Search Everything clients listing.
  * Show phone numbers and e-mail action on Search Everything for candidates.
  * All of the "data access" classes (the data item classes, plus Search,
    Pipelines, etc.)  now use only one DatabaseConnection object. This speeds
    things up quite a bit when multiple methods are called per class instance.
  * Add an MRU entry when editing a job order.
  * Moved MRU icons to images/mru/.
  * Refactored MRU code a bit so that more of the "work" is being done in
    Session::addMRU().
  * Starting AntiWord Word Parser.
  * Don't use absolute URIs for test cases; use relative ones and get the
    absolute URL from ATSUtility::getAbsoluteURI().
  * Testing AntiWord Word Parser class.
  * Refactored "data access" classes so that a siteID is only passed to the
    constructor, and handled internally from there on.

10/19/2005
  * "Consider" searches work again (broken yesterday it looks like).
  * Added Key Technologies to clients.
  * Main module listing tables don't slide around as much now (and not at all
    in IE). There needs to be one column without a width to allow the browser
    to know what to do with "extra space". The width of this column is still
    "absolute" to the browser, as long as the table width is absolute.
  * Main module listing action icons are now vertically aligned to the top of
    the row to make things look better if a row DOES wrap.
  * Images / icon updates.
  * Show notes in the proper location on Client Details.
  * Only show contacts with phone numbers on the cold call list.
  * Put a bit of spacing between the bottom of the active "panel" and the
    footer quote.
  * Code cleanups and performance fixes to sorttable.js.

10/18/2005
  * Refactored search code a bit so that wild card string operations are done
    in the Search library.
  * Display unassigned contacts owner as "None".
  * Separate Active and OnHold / Full job orders in view selection.
  * A few more tiny fixes to js/sorttable.js.
  * Tcl's HTTP package sucks and getremotebuild.tcl only worked like 40% of
    the time; rewrote in bash with curl.
  * Merged UserInterface::isRequired[GET|POST]ID() into isRequiredID().
  * Added UserInterface::isChecked() for testing to see if a checkbox on a
    form was checked.
  * Cleaned up "View Selector" CSS a bit.
  * Added code counting awk script to scripts/. This isn't a complete solution
    yet, as it's only per file:

        richard@sales01 ~/.htdocs/cats $ cat $(find . -name '*.php' -print | \
        grep -v phpmailer) | scripts/countfilecode.awk
        Blank lines:     1642
        Comment lines:   2103
        Brace lines:     1727
        Real lines:      7116
        Total lines:     12588
        richard@sales01 ~/.htdocs/cats $

  * Added "Only My Contacts" checkbox to Contacts page.
  * Pager can handle empty result sets now.
  * Don't show Automated Tester logins on Login Activity pages.
  * Refactored pager navigation at bottom of pages into TemplateUtility class.
  * Don't generate a bunch of unneeded whitespace in TemplateUtility. It's
    just slowing down page loads and doesn't make the template code any more
    readable (of course).

10/17/2005
  * Start of fixed column widths.
  * Use a pager for displaying the Clients listing.
  * Make current page bold on page listing at bottom of paged tables.
  * Add previous and next buttons to page listing at bottom of paged tables.
  * Pager correctly handles invalid page numbers now.
  * Use all fixed-width columns on the Clients listing.
  * Fixed an variable name typo in scripts/update_cats.sh.
  * scripts/update_cats.sh works on www.jasamba.com now.
  * scripts/update_cats.sh (really) works on www.jasamba.com now.
  * More fixed-width columns.
  * Show (None) in e-mail column for contacts with no e-mail address on file.
  * Fixed lots of table row wrapping issues.
  * Moved statistics to Reports in preparation for TODO list on main page.
  * A couple of minor cleanups to js/sorttable.js.
  * A bit of reports refactoring.
  * Start of testing for Reports and Settings.

10/14/2005
  * Allow viewing of unsuccessful logins.
  * Changed Search Everything text from 'Search:' to 'Search All:'.
  * Added a test case to attempt login with a valid username but an invalid
    password.
  * Show e-mail addresses as links on contact details page.

10/13/2005
  * Start of unit testing for DatabaseConnection.
  * Renamed some string utility functions from stringIs*() to is*().
  * Reduced some code duplication in TestCases.php.
  * More cleanups to test cases.
  * Remove 'test' prefixes for test cases on test results page (simpletest only
    executes class methods that start with 'test').
  * scripts/newversion.sh changes the build number back to 1000 after the
    version number is bumped now.
  * More work on DatabaseConnection unit testing.
  * Start of allowing users to change their own passwords.
  * Cleaned up form validation JavaScript a bit in all modules.
  * Form validation for Profile Settings page.
  * Added flags / constants for access levels.
  * Add Login Activity stub page to Settings module.
  * Show recent login activity on Settings: Login Activity.
  * Moved Login Activity stuff out of the Sites library. Users code will also
    be moved to a new Users library.
  * Show key skills on candidates search page.
  * Start of testing for clients and contacts.
  * Track unsuccessful logins for known users.
  * Give the Automated Testing System it's own login.

10/12/2005
  * Added version-bumper script.
  ! Start of version 0.3.
  * Scripts updates.
  * Client-side form validation for minimum and maximum rate when adding and
    editing job orders.

10/11/2005
  * Tests CSS fixes.
  * Sort contacts by last modified.
  * Added contacts paging on the contacts main page.
  * Removed getLastX() methods. These are replaced by paging.
  * Handle empty record sets by returning an empty array instead of false in
    DatabaseConnection. This reduces code duplication.
  * Library SQL function cleanups.
  * Added test cases for phone number and e-mail address extraction.
  * E-mail address extraction can handle multi-"dot" nospam addresses like
    this now:

        help [at] lists [dot] help [dot] org

  * Added a few more phone number validation / extraction test cases.
  * Added unit testing for ArrayUtility.
  * Found and fixed a bug found while unit testing ArrayUtility. implodeRange()
    had an off-by-two error.
  * More tests user interface updates.

10/10/2005
  * Huge cleanups in Statistics.php / reporting stuff.
  * Added last month's statistics on home page.
  * Switch some icons to the Crystal LGPL set.
  * Cleanups to images/.
  * htmlspecialchars() was being run twice on engagement notes data, causing
    single quotes to display as "&#39;", for example.
  * Cancel event works when editing engagement notes now.
  * AddressParser can now be used more than once from the same instance
    (although not concurrently).
  * Added AddressParser unit testing using addresses from the sample address
    list (and a few more).
  * Tests output is a lot nicer now.

10/09/2005
  * More automated testing.
  * URL detection / extraction in StringUtility can handle IP addresses as
    well as localhost addresses.
  * More automated testing (found the previous bug).

10/07/2005
  * Activity stuff works from candidates perspective now.
  * Update date_modified when items are added or removed.
  * Added Cold Call List.
  * Added CATS Developer access level, level 600 (above Root Administrator).
    These users can run system tests.
  * Added "System Tests" link on the home page for access level 600 users
    (developers). The idea here is to make accessing the tests module easy to
    encourage frequent testing.
  * Start of individual test selection in tests module.
  * More individual test selection (will finish at home). Note: Tests are not
    currently working.

10/06/2005
  * Form focus on all search pages.
  * Started porting activity stuff to candidates perspective.
  * Do newline to <br /> conversion on candidate notes.
  * Trim candidates notes to 500 characters until '[More]' is clicked (like
    job orders).
  * Moved toggle*() code to lib.js so that all modules can easily use it
    without code duplication.
  * Show 'Unassigned ' on clients search listing for clients with no owner.
  * Trim contact and client notes to 500 characters until '[More]' is clicked
    (like job orders and candidates).
  * Getting a random quote is now faster and more random.
  * Modified database structure to support modification timestamps.
  * Refactoring statistics code a bit.
  * Last Week and Last Month statistics are actually accurate now. Instead of
    using WEEK(date_created) = WEEK(NOW()) -1, use YEARWEEK(date_created) =
    YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 7 DAY)).
  * Show job order owner on submission report.
  * Further abstracted activity entry JavaScript so that we can use it for
    candidates, too.
  * All modules are now in modules/.
  * Updated scripts/update_cats.sh so that it works with subversion.
  * Fixed a JavaScript form validation error.

10/05/2005
  * Fixed address parser bug when adding a candidate from the job orders
    perspective.
  * Fixed the DOM delete event bug from last night... forgot a 'var' keyword,
    thus making a global variable instead of a local one.
  * Allow tests to execute longer than 30 seconds (up to 5 minutes now).
  * Show test execution time on Test.php.
  * Abstracted Activity stuff so that it can be easily used from the candidates
    perspective as well.
  * Converted DateUtility unit tests to SimpleTest cases.
  * Converted more StringUtility unit tests to SimpleTest cases.
  * More work on the testing framework. Working on allowing user to select test
    groups to run.

10/04/2005
  * Remove the add activity form from the bottom of the table on Show Contact
    after it has been submitted.
  * Start of dynamic action creation for newly added activities on Show
    Contact.
  * Show '(No Notes)' if there are no activity notes.
  * Dynamically created activity entry actions work now.
  * Stop default action on dynamic link events created by Activity_add().
  * Deleting activities works on the server side now.
  * Turned off some alert debugging in lib.js.
  * Cleaned up tooltip code quite a bit (it was really bothering me ;).
  * Fixed output of TemplateUtility::getAlternatingRowClass().
  * A bit of AJAX abstraction in lib.js.
  * Use normal HTML tooltips. JS tooltip code we are using is extremely buggy
    and is messing up the event model.
  * Still trying to figure out a weird DOM-related bug. When you delete one
    activity, all other dynamically created activities break until the page is
    refreshed.

10/03/2005
  * Start of automated testing for Candidates module.
  * More work on "Add Activity Entry" JavaScript / AJAX backend.
  * Added Last Month's Submission Report for Mike.
  * config.php is back. We're using compiled code caching now, so include
    overhead cost is basically nonexistant.
  * We're using a delegation module for AJAX stuff now. This lets us make the
    code more modular.
  * XML returned for an AJAX error is now just the <errormessage /> and
    <errorcode /> tags.
  * Added AJAXInterface::outputXMLErrorPage() to make outputting errors more
    uniform.
  * "Add Activity Entry" AJAX stuff works now. Delete is next.
  * More JavaScript cleanups in lib.js.
  * Start of Delete for activities.

10/02/2005
  * Added basic testing for adding, removing, and editing a job order. This
    is by no means a complete test; but it does test basic functionality.
  * Found an error already; $trimmedStartDate didn't exist in JobOrdersUI in
    some cases where it should. Fixed.
  * Added id attributes for a few links that were difficult to tell SimpleTest
    to click by name.
  * More test automation; found another StringUtility bug during testing;
    fixed. not@valid...com is not a valid e-mail address :).
  * More StringUtility testing; fixed another regex problem with e-mail
    validation.

9/30/2005
  * Improvements to AJAX Activity Notes editing.
  * Editing activities works now.
  * Small JavaScript fix to engagement notes code on candidates/Show.tpl.
  * Start of ability to add an activity entry entry via AJAX.
  * Added This Week's Submission Report (was a quick thing and Mike needed it).
  * Reporting of incorrectly parsed addresses work now. I need to write a
    little viewer page, but, phpMyAdmin works for now.
  * Naming consistency and big cleanups in lib.js.
  * Job Order salary is a text field now.

9/29/2005
  * Added AJAXInterface and SecureAJAXInterface class. Using in
    ajax/editEngagementNotes.php.
  * Improvements to address parser. It is a bit more robust now.
  * ajax/getParsedAddress is using *real* AJAX (using XML) now.
  * This is using out new AJAXInterface class.
  * Added contact_activity and candidate_activity tables (new DB dump).
  * Implemented the library and database side of Activity.
  * More progress on activity stuff.
  * AJAX editing of Activity *NOTES* works now. Type editing needs more work.

9/28/2005
  * Candidate ownership.
  * SQL cleanups.
  * Sync ShowPopup with Show for candidates and job orders. These need to be
    merged together I think... could be a maintenance nightmare.
  * "Owned" -> "Owner".
  * HTML fixes.
  * Everything supports ownership now.
  * Show owner on main contacts listing.
  * More HTML fixes (table wrapping/alignment).
  * Improvements to dumpdb.sh (use extended inserts).
  * Added Session::getCookie().
  * Removed ErrorHandler for now, as there's not currently a use for it. We
    might need to add this back in some form later, but, right now, fatal()ing
    out works fine.
  * AJAX with sessions!
  * We're using *real* AJAX (using XML) for ajax/editEngagementNotes.php. Now
    we can actually have notes that contain commas :).

9/27/2005
  * More encoding fixes.
  * Fixed Add Job Order error.
  * Fixed width on job orders table.
  * Separate pipeline and submitted on main job order listing.
  * Smaller width for client name on main job order listing.
  * Don't show "(No Notes)" in edit box when editing engagement notes.
  * Fixed Add Job Order tab indexes.
  * More testing stuff.
  * Fixed Edit Job Order tab indexes.
  * Start of true "smoke 'n burn" testing with SimpleTest.
  * Found one bug already! Some browser's don't set certain request
    information, and PHP doesn't even create array indexes for them in
    $_SERVER, so errors are triggered; fixed.
  * HTML fixes to Login.tpl.
  * Many improvements to our [start of a] testing framework. I think we could
    have a complete smoke-n-burn test fairly soon.
  * Job orders can be owned by another user now.
  * SQL optimizations.
  * CVSIGNORE all of the little temp files that MS Word likes to make in doc/.
  * Show owner instead of entered-by for job orders everywhere except details.
  * "Only My Job Orders" checkbox now shows job orders OWNED by the current
    user.
  * A bit of refactoring... Search::searchJobOrdersByClientID ->
    Clients::getJobOrders() and Search::searchContactsByClientID ->
    Clients::getContacts().
  * More 'owner' stuff.
  * More test framework updates.
  * Home is now tested by smoke-n-burn testing.

9/26/2005
  * subModal transparency fix (for real this time).
  * Caching fixes.
  * Show Today's candidates instead of the last 15.
  * Fixed dumpdb.sh.
  * Fixed "Back to Search" link on joborders/ConsiderSearchModal.tpl.
  * Job Orders modal windows can properly error now.
  * Firefox transparency makes modal windows too slow to be usable; reverted.
  * Progress on Quick Add Candidate.
  * Fixed Edit Contact action link from clients perspective.
  * "Quick Add" works now from the job orders modal dialog.
  * Anchoring / JavaScript link fixes on Job Order Details.
  * Job Order notes can be truncated just like the description can now.
  * HTML fixes.
  * Added "Job Openings".
  * Focus on first field on edit page forms.
  * We have a Full status now.
  * SQL Optimizations.
  * Fixed (hopefully) some UTF-8 encoding problems.

9/25/2005
  * Testing DHTML engagement notes stuff.
  * subModal transparency fix.
  * Oops, AddressParser AJAX code got broken during the switchover.
  * AJAX testing for engagement notes stuff.
  * Editing engagement notes via JavaScript dynamic DOM and AJAX works now.
    I have no doubt that this is a tiny bit buggy / not fully cross-browser
    compatible. It is not yet tested in IE, but it works on my copy of Firefox.
    The AJAX server side script is a giant security hole right now. I have to
    figure out a way to pass it the current user's session so that we can make
    sure that the user is logged in, and is only modifying data that he/she
    should have access to, but it's 1:23, so I'll work on that tomorrow :)

9/23/2005
  * Added ArrayUtility::findKeyValuePair().
  * Don't allow candidates to be added to the pipeline twice.
  * More HTML 4.01 compliance.
  * Show hot job orders in red on candidate pipeline.
  * Show engagement notes on pipeline from job order perspective.
  * More reports stuff (stopping work on reports until after migration
    for now; this last thing was something that the guys needed today.)
  * Show engagement notes on pipeline from candidate perspective.
  * Use POST for login.
  * "Only My Job Orders" checkbox now shows job orders assigned to OR entered
    by the current user.
  * "Add Job Order" sub-tab was 'selected' when on the Edit Job Order page.
  * A few minor optimizations.
  * Resolved a DOM 'id' conflict on ConsiderSearchModal pages.
  * Fixed form focus for consider-search modal dialogs.
  * Show key skills on consider candidate search from job orders perspective.
  * Optimization to template logic on ConsiderSearchModal pages.
  * SQL optimizations.
  * Don't let dates wrap on main job orders page.

9/22/2005
  * More testing.
  * Start of Reports.
  * Autofocus on first field on all Add pages.
  * Today's statistics work now. DAY() is the day number of the month, so
    Today's Statistics was showing total statistics from all dates in all
    months with a "day number" the same as today's.
  * More work on "CATS Testing Procedures".
  * Candidate e-mail addresses are now links on the details page.
  * Client gets selected on the add page now when you add a job order from
    the clients perspective.
  * More work on "CATS Testing Procedures" (testing is effective already...
    finding lots of stuff).
  * Populate entered-by for clients.
  * Show entered-by stuff on clients pages.
  * Show user's full name instead of username on details pages for assigned-to
    and entered-by.
  * Fixes to top-right information.
  * Cleaned up and sped up TemplateUtility.
  * Adding a client works now.
  * Display web site as a link on Client Details.
  * Website is saved when editing a client now.
  * New Reports icon.
  * Client gets selected on the add page now when you add a contact from the
    clients perspective.
  * A bit more reports progress.
  * Working on an (efficient) way to not allow candidates to be added to the
    pipeline twice, while being user-friendly.

9/21/2005
  * Date picker stuff works now.
  * "Popup" pages display correctly now.
  * Start of attachments.
  * Testing preparation.
  * Misc HTML / Template fixes.
  * Disabled attachments for now.
  * Prepare for modal dialogs on Job Orders and Candidates main pages
    (action links).
  * Start of Reports.
  * Consider action icons work on Job Orders and Candidates listings.

9/20/2005
  * Login form validation.
  * Add Job Order form validation.
  * Edit Job Order form validation.
  * CSS fixes.
  * Server-side validation fixes.
  * Add Candidate form validation.
  * Edit Candidate form validation.
  * Add and Edit Client form validation.
  * More template cleanups.
  * Add Contact form validation.
  * Data Item form validation is finished now.
  * Show Job Order is a bit more horizontal.
  * Show pipeline statistics on Show Job Order
  * Testing some pager code.

9/16/2005
  * Login error handling.

9/15/2005
  * Add Contact displays now.
  * DB dump synch from ATS.
  * Fixes to Show Contact.
  * Adding a contact works now.
  * Edit Contact displays now.
  * Editing a contact works now.
  * More fixes to Show Contact.
  * Abstract _GET from templates for IDs.
  * Handle searching in handleRequest() in data item modules.
  * Start of Search in JobOrders.
  * Converted a few "magic numbers" to class constants.
  * Query cleanups in Search.php.
  * Searching for candidates works now.
  * Searching for job orders works now.
  * Removed searchCandidatesByCandidateID() (WTF... Candidates::get() does
    the same thing :)).
  * Searching for clients work now.
  * Searching for contacts works now.
  * Use sorttable on search pages.
  * Search everything works now.

9/14/2005
  * Use our own template engine now. This is about 3 times faster than Savant3,
    and 100% compatible with the way that we were using Savant3.
  * Add Client displays now.
  * Adding a Client works now.
  * Moved fatal*() stuff up to UserInterface.
  * Finished Edit Client UI logic.
  * Lots of little template cleanups.
  * Client Edit page displays now
  * Added support for client notes.
  * Show Client works now.
  * Show job titles in bold on Show Job Order.
  * JobOrders SQL fixes.
  * Deleting a client works now.
  * Editing a client works now.
  * More template file fixes.
  * Fixed client notes; work now.
  * Start of ContactsUI.
  * Deleting a contact works now.
  * Contacts close to finished.

9/13/2005
  * Job Order Add Page displays now.
  * More Job Orders Add Page template stuff.
  * Adding a job order works now.
  * Added fatal() / fatalModal() methods to Job Orders module.
  * Fixed a couple of fixRequiredInput() errors in CandidatesUI.php.
  * Use fixed-width textboxes on candidates/Edit.tpl.
  * Start of Edit Job Order.
  * Edit template displays now.
  * CSS fixes.
  * Editing a job order works now.
  * More progress on "Add Candidate to This Job Order Pipeline" search.
  * "Add Candidate to This Job Order Pipeline" search gets to Add
    Engagement Notes now.
  * Small fix to subModal so that it always sets the correct title.
  * Small bugfix to CandidatesUI::onAddEngagementNotes().
  * "Add Candidate to This Job Order Pipeline" works now.
  * More subModal fixes.

9/12/2005
  * Submit and Unsubmit for candidates works now.
  * Removing a candidate from a pipeline works now.
  * A candidate is "removed" from a pipeline, not deleted... fixed wording
    in code.
  * Refactored pipeline stuff into a new Pipelines "data item", since the
    functions otherwise could be associated with either a candidate or a
    job order.
  * Use fatalModal() instead of fatal() when failed to add candidate to
    pipeline.
  * Start of Show Job Order.
  * index.php is a lot faster.
  * Show Job Order works now.
  * Cleanups to joborders/Show.tpl.
  * Pipeline table displays on Show Job Order now.
  * Big cleanups in DateUtility.php.
  * Added remove / submit / unsubmit pipeline functionality from the Show
    Job Order page.
  * Modal dialog pops up for "Add Candidate to This Job Order Pipeline".
  * Start of UI logic for modal dialog stuff on Job Orders page.
  * Removing a candidate from a pipeline from the job orders prospective
    works now.
  * Deleting a job order works now.

9/10/2005
  * A couple of bug fixes.
  * Job Order search functions get entered-by-user information now.
  * MRU list works now.

9/09/2005
  * Finished search function conversion.
  * Searching in the modal window works correctly now, just have to handle
    sending the selection back to the parent window.
  * Modal pages can "fatal error" now.
  * Better GET 'ID' validation.
  * More progress on modal window stuff... almost done.
  * Cleaned up CandidatesUI a bit.
  * No more '_' on private UI function names.
  * POST validation in CandidatesUI.
  * Cleaned up CandidatesUI a bit more.
  * The Add to Job Order Pipeline dialog works entirely now.
  * More validation.
  * Misc library fixes.

9/08/2005
  * HTML fixes (ran w3c validator).
  * Show Candidate works now.
  * Small fixed to candidates/Show.tpl.
  * Edit Candidate logic and display finished (just have to convert action).
  * Moved sorttable.js to js/.
  * Link fix on candidates/Add.tpl.
  * Added image alt tags for w3c compliance.
  * Don't use _ for protected functions.
  * Edit Candidate works now.
  * Delete Candidate works now.
  * Candidates library conversion is now complete.
  * Start of unlinking attachments and candidates. Any data item will be able
    to have an attachment.
  * We're using HTML 4.01 Transitional now. Compliance testing/fixing can
    happen later.
  * Start using subModal instead of return page stuff.
  * Start of Add to Job Order Pipeline in Candidates module (using subModal).
  * Search logic mostly finished for Add to Job Order Pipeline in Candidates
    module.
  * Start of search function conversion (THESE ARE STILL BROKEN; will finish
    at home.)

9/07/2005
  * Abstracted action-checking a bit in UserInterface classes.
  * Contacts page works now.
  * Converted Settings.
  * Converted is_numeric($string) -> ctype_digit((string)$string), since
    '1.233' and '1e4' are 'numeric'.
  * Started implementing more actions for the candidates module.
  * magic_quotes_gpc fixes.
  * Added UserInterface::_isPostBack() for determining whether or not we
    are doing a postback for a form action.
  * Converted Sites.php.
  * We have a basic way to bail out and throw a fatal error on a page now.
  * A good but of the CandidatesUI logic is done now.
  * Adding a candidate works now. No form validation or return pages.
  * Start of Show Candidate.
  * Library cleanups.
  * Show Candidate logic finished (just have to convert template).

9/06/2005
  * More library fixes; almost ready to start conversion of data item
    templates.
  * A couple of login bug fixes.
  * Converted delete functions in data item classes.
  * Converted get functions in data item classes.
  * Converted Candidates::getLastX().
  * Started Recent Candidates page conversion.
  * Candidates displays now.
  * Make Recent Candidates page even faster... 209ms on my local machine,
    vs. 3,139ms for Prado version.
  * Added Candidates::getAll().
  * Added Clients::getLastX().
  * Converted Clients::getAll().
  * Added Contacts::getAll().
  * Converted Contacts::getLastX().
  * Fixed form focus on login page.
  * Logout works now.
  * Module loading is faster now and can properly handle JobOrdersUI (more
    than first letter uppercase).
  * Converted the Active Job Orders page logic.
  * Merged functionality of JobOrders::get*AssignedTo() to the normal get*()
    functions to improve maintainability.
  * get() methods should use getAssoc() instead of getAllAssoc() for single
    records.
  * Started converting search functions.
  * Active Job Orders page displays correctly now.
  * View postback stuff on Active Job Orders works now.
  * Fixed source headers.
  * StringUtility is now static.
  * Prevent static utility classes from being instantiated.
  * Made appropriate utility classes static.
  * CVSIGNORE fixes.
  * Clients module works now.

9/04/2005
  * More big library cleanups.

9/02/2005
  * Made 'home' the default module if none is specified.
  * <Module>UI classes are now extensions of UserInterface.
  * Converted HomePage.php to HomeUI.php.
  * Statistics.php uses new database stuff.
  * loadModule() is faster.
  * Moved searchEverything.tpl and topright.tpl to functions in
    TemplateUtility.
  * Home.tpl almost finished.
  * Synched realname changes from ats/ tree.
  * Moved getQuote() to ATSUtility. Don't need all that object overhead for
    something so simple.
  * Updates to doc/DEVELOPMENT-GUIDELINES.
  * footer.tpl moved to function in TemplateUtility.
  * Home loads now.
  * Home works.
  * Moved search functions to lib/Search.php.
  * Added DatabaseConnection::getAllAssoc().
  * Search.php uses new database code.

9/01/2005
  * Start of Prado->Savant migration.
  * Start of delegation module (index.php).
  * Added basic config.php.
  * ATSUtility::printTPLHeader() prints <body> tag now.
  * Login page displays now.
  * Started session stuff.
  * Session code sortof-almost works.
  * I can successfully log in now.

ATS 0.1 (Old)
---------
9/01/2005
  * JavaScript modal fixes.
  * Changed description back to notes.
  ! FORK: Start of CATS 0.2. Changes above.

8/31/2005
  * Add update_ats.sh to CVS.
  * Changes to candidate_history structure.
  * Added functions Candidates::addCandidateHistory() and
    Candidates::delCandidateHistory().
  * Added Candidates::getHistory().
  * Added PHPMailer to the repository under lib/phpmailer/.
  * Remove "Show Recent" links.
  * Add CVS Id tags to templates.
  * Added docs/DEVELOPMENT-GUIDELINES doc stub (used Eggdrop doc format).
  * Use fixed width on Add Candidate and Add Contact tables.
  * Fixed buttons on Add Candidate and Add Contact.
  * Added key_skills column to candidate.
  * Made deleteCandidate() clear that candidate's history as well.
  * Finished "key skills" support (search coming next).
  * Added a clearMRU() (not used yet).
  * Search candidates by skills works from Candidates Search page.
  * Made TValidators red again, since Prado made the default black in 2.0.2.
  * Keep browsers from caching so much stuff.
  * Add pages work again; fixed postback error.
  * Show client name on contact details page.
  * Changing a contacts client works now.
  * Show client name on Candidate Details.
  * Show client names as links on Job Order Details and Contact Details.
  * When adding a contact from the Client Details page, select the proper
    client ID.
  * Load lib.js in ATSUtility::printTPLHeader(). This shouldn't break
    anything, but, JavaScript source files should be included in <head>.
    Special cases should be fixed on a case-by-case basis if problems arise.
  * Start of subModal stuff.
  * Call ATSUser::startTimer() in ATSUtility::printTPLHeader().
  * ATSUtility::printTPLHeader() can load JavaScript files now.
  * Changing a contacts client really works now.
  * When adding a contact from the Client Details page, select the proper
    client ID (for real this time :).
  * Use sorttable on search pages.

8/30/2005
  * AddressParser can handle trailing commas in addresses now.
  * Add extra canvas space around contacts icon used on contacts page header.
  * Renamed contact2.gif to contact_MRU.gif.
  * New "Add Client" icon.
  * Indexing fixes for 'title' field.
  * A bit of DHTML experimentation on JobOrdersEditPage on the Pipeline.
  * Don't use TButton for "Clear" buttons.
  * Several little CSS fixes.
  * Search Everything shows contacts matching a client name now.
  * Support the 'title' field throughout contacts.
  * Use inputbox class on Contacts Edit page.
  * Fixed buttons on Contacts Edit page.
  * Fixed buttons on Candidates Edit page.
  * Fixed Contacts page tab indexes.
  * Use inputbox class on Clients Edit page.
  * Fixed buttons on JobOrders Edit page.
  * Moved *Count() methods to a new Statistics library.
  * Today statistics work now.
  * Keep IE from caching search results.
  * Show title on contacts pages.
  * Show e-mail addresses as links on Contacts page.
  * More form cleanups.
  * SQL fixes in Candidates library.

8/29/2005
  * Typo fix for Job Order links in MRU list.
  * Use correct candidate and client MRU icons.
  * Use correct job order MRU icons.
  * Error handling and security fixes in CandidatesEditPage and
    CandidatesPopupPage.
  * Fixed error message format in ErrorHandler.
  * Error handling and security fixes in ClientsEditPage.
  * Error handling and security fixes in ContactsEditPage.
  * Error handling and security fixes in JobOrdersEditPage.
  * Better abstraction in *EditPage.
  * No actions on CandidatesPopupPage.
  * Make sure popup windows get focus.
  * Fixed a JavaScript error when searching for a job order to consider a
    client for.
  * Don't show normal actions on "Consider for a Job" search pages. Only show
    the "New Window" action.
  * Renamed lastViewed to MRU.
  * Use tdData class on Candidates, Clients, and Contacts.
  * IE font-weight CSS fix for MRU text.
  * tdVertical / tdData fonts were hard to read... too small. Maybe we should
    try a different font style?
  * Contacts work with MRU now.
  * Removed isStringNumeric from StringUtility. PHP has a built-in function
    is_numeric().
  * CandidatesEditPage is no longer one giant, ugly form.
  * Added missing ')' on "topright.tpl" header.
  * Don't show a comma on "city, state" on clients page.
  * Show Entered By next to Created on details pages. This condenses the page
    more.
  * Show City, State, and Zip next to eachother on details pages.
  * Start of "Hot Jobs" support.
  * More IE CSS fixes.
  * Fixed Job Order Details when php E_NOTICE is enabled.
  * Show 'hot' job titles in red on details page.
  * More fonts stuff.
  * Only allow active jobs to be hot.
  * Show job titles before client names on active job orders table.
  * Show hot job titles in red on active job orders table.
  * Attachments work on FS1 now.
  * Make Job Orders Search table fill the normal 925px width.
  * Show hot job titles in red on job order search table. Closing bug 0000048.

8/26/2005
  * No more 'Rate Min' and 'Rate Max' on the details page. It is now formatted
    on one line.
  * Forgot to CVS add build.tcl to CVS.
  * More horizontal layout on Job Orders Edit Page.
  * Use form focus events on Job Orders Edit Page.
  * More horizontal layout on Job Orders Add Page.
  * Use form focus events on Job Orders Search Page.
  * Start of MRU list: CSS in place...
  * Added local->remote version-matcher:

      $ scripts/getremotebuild.tcl http://fs1/ats
      Local:  0.1 (BETA) build 1015
      Remote: 0.1 (BETA) build 1010
      VERSION MISMATCH!

  * Moved version and build grabbing to lib/ATSUtility.php.
  * Removed utils.php.
  * Cleaned up the tree a bit; doing a re-checkout would be a good idea.
  * Moved <head> tag generation to ATSUtility.
  * Fixed Contacts table width on Client Details.
  * Fixed row alternation on Client Details.
  * Added MRU list.
  * Changed fonts to pixel sizes.

8/25/2005
  * Lots of CSS cleanups; not perfect yet, but I need to commit this before
    I go in...
  * Even more CSS cleanups!
  * Fixed default focus on CandidatesSearchPage.tpl.
  * CSS looks better in IE now.
  * Use form focus events on JobOrdersAddPage.
  * Added URL field to contacts pages (bug 0000049).
  * CSS font color fixes.
  * Start of URL validation / extraction. Added StringUtility::extractURL().
  * Show URLs as links on Client Details.
  * Require Client Name of ClientsEditPage.
  * Use form focus events on ClientsAddPage.
  * Use Prado regex validation on client URLs. Bug 0000049 is now closed.
  * Fixed table widths on SearchEverythingPage.
  * Trim user input before adding to DB.
  * Delete client works again.
  * Use form focus events on ContactsAddPage.
  * Added build number. To commit from now on:

      Add this alias:
        alias bcommit="scripts/build.tcl; cvs -z8 commit"

      bash ~/ats$ bcommit

8/24/2005
  * Search Everything doesn't search by city, state, or zip anymore.
  * Basic backtracing in ErrorHandler.
  * Store the user agent in the login history table.
  * Passwords are now case sensitive on login.
  * Basic login error handling.
  * Removed public set functions for class data that should be read only
    outside of ATSUser.

  * Implemented access levels:
        100     Read    Read Only - A standard user; can view data on the
                        system in a read-only mode.
        200     Edit    Edit - All lower access, plus the ability to edit
                        information on the system.
        300     Delete  Delete - All lower access, plus the ability to edit
                        information on the system.
        400     SA      Site Administrator - All lower access, plus the ability
                        to add, edit, and remove site users; as well as the
                        ability to edit site settings.
        500     Root    Root Administrator - All lower access, plus the ability
                        to add, edit, and remove sites; as well as the ability
                        to assign Site Administrator status to a user.

  * User / Site info at top of page looks slightly better now.
  * Added stubs for Manage Site Users and Site Settings on the Settings tab
    for users with an access level of 400 and above.
  * Fixed Manage Site Users link.
  * Entered-by is no longer changed to the current user's ID when editing.
  * Start dates work on JobOrdersAddPage now.
  * Show proper assigned-to user on JobOrdersEditPage.
  * Moved Entered By next to Assigned To on JobOrdersEditPage.
  * Slightly more monster-ish colors. It still seems to suck, but I can't
    seem to make the monster colors work any better really.
  * Input Box change-color-on-focus works now.
  * Better contacts image.
  * Quote and Copyright Notice are now correctly centered under the main div.
  * Fixes to oldblue.css.
  * The local tab is now clickable... after much css-hackery. This closes
    bug 0000008.
  * Fixed default fonts.
  * Fixed a database column typo in SearchEverything.tpl.
  * Correctly use oddTableRow / evenTableRow on SearchEverythingPage.tpl.
  * Massive CSS cleanups.
  * Use safe default fonts in main.css.

8/23/2005
  * Deleting attachments works now. This closes bug 0000040.
  * Fixed help message when considering a candidate for a job order (reported
    by Jim).
  * Use non-capturing subpaterns instead of capturing subpaterns for speed in a
    few of StringUtility's expressions.
  * Use the "extraction method" for finding e-mail addresses with AddressParser.
  * Better testing of the extractor functions in testStringUtility.php.
  * AddressParser can now handle text before an e-mail address (it can actually
    pretty much extract an e-mail address out of any given block of text now).
  * Phone number parsing in AddressParser can recognize (C)/(M), (H), and (W)
    for cell / mobile, home, and work (respectively) now.
  * Added Jason Moon's popup calendar to the tree (having issues making it
    work with Prado though).
  * Cleanups to JobOrdersEditPage. No more use of TRepeaters for a single row
    of data.
  * Basic error handling.
  * Cleaned up CandidatesEditPage.
  * We can now parse an address with an e-mail address between name and
    address.
  * Added stubs for INSTALL and FAQ.

8/22/2005
  * Search Everything works. This closes bug 0000035.
  * Logout is now a tab.
  * More indexing (new database dump).
  * Added assigned_to_contact (start of joborder->contact associations) to
    joborder table (new DB dump).
  * Added date_submitted to candidate_joborder (new DB dump).
  * Fixed Search Everything location on JobOrdersEditPage.
  * We now support submission dates (new DB dump).
  * More stuff related to changing the default phone number type in
    AddressParser.
  * Search Everything results are now sortable.
  * Removed "consider" icons and actions from Consider Candidate search on
    job orders page. The Candidate's profile can be viewed in a new window by
    clicking the "New Window" action icon.
  * Cleaned up JobOrdersEditPage php.
  * Split up first and last names of candidates on job order details pipeline.
  * Removed actions from Search Everything results page.
  * Renamed "Consider for a Job" to "Add to Job Order Pipeline".
  * Fixed Add Contact and Add Client tabindexes.
  * Center popup windows.
  * Fixed an address parser bug.
  * Use clearReturnPage unless setReturnPage needs to be used.
  * Fixed ContactsPage tooltips.
  * Submitted status works now.
  * Show date submitted on pipeline from job orders perspective.
  * Show date submitted on pipeline from candidates perspective.
  * Everything uses a siteID now. This closes bug 0000036.
  * Login form redesign. This closes bug 0000032.

8/19/2005
  * Don't show time created on recent contacts listing (consistency).
  * Use '%m-%d-%y (%h:%i %p)' for date formats on the details pages (easier to
    read than '%h:%i %p %m-%d-%y').
  * Optimized getJobOrder() SQL.
  * Return true from addCandidateToPipeline() on success.
  * General cleanups to JobOrders library. All library functions in this file
    that access the DB require a siteID now.
  * Added JobOrders::unsubmitCandidate().
  * Fixed a bug preventing you from editing and saving a job order if a start
    date was not entered.
  * Address parser works with '.' in the city name and no space after it now.
  * Edit contact works now.
  * Clients search page has been converted to the new format and works now.
  * Fixed date formats in several SQL queries.
  * Set focus on search box on Candidate Search page.
  * Show Action and Created on Candidate Search page.
  * Set focus on search box on Client Search page.
  * Searching contacts by name works now. Searching by client name is next.
  * Searching contacts by client name works now. This closes bug 0000005.
  * Action icons for Submit and Delete. Bug 0000016 is now closed.
  * Added 'unsubmit' to pipelines.
  * Fixed Add Contact page title.
  * Show contacts on client details page.
  * Deleting a contact from the contacts main page works now.
  * Fixed usage instructions for freeform box on Add Candidate page.
  * We are now using address parsing on Add Contact.
  * Start of support for changing "default" phone number type in AddressParser.
  * More playing with testing APIs.

8/18/2005
  * Name parsing can handle Lastname, First M. now, as well as names like
    "Wellford, Lee Anne M." or "Lee Anne M. Wellford".
  * New colors. I think it looks better than it did at least... This closes
    bug 0000030.
  * New link colors for sub-tabs.
  * The address parser can handle '.' in an e-mail address now.
  * Added misc/SampleAddressesToParse.txt: sample addresses to test the parser.
  * Phone number and city state zip matching uses PCRE now (faster, and a
    simplified expression).
  * Start of phone number parsing...
  * Created a simple regression-testing framework for the StringUtility regex
    functions (misc/testStringUtility.php).
  * Phone number parsing works.
  * A couple of IE fixes.
  * Moved Autofill box to middle on add candidates page.
  * No more colored stripe at the bottom of the contents div in IE. This
    closes bug 0000002.
  * General library cleanups to Clients and Candidates (rest tonight/tomorrow).
    All library functions in these files that access the DB require a siteID
    now.
  * Fixed a bug with the AddressParser AJAX stuff in IE.
  * Everything seems to look ok in both IE and Firefox now.

8/17/2005
  * AJAX code doesn't use global variables anymore. Multiple AJAX requests on
    one page will no longer break.
  * get*() methods have bodies now in AddressParser class.
  * Implemented AddressParser::setAddress().
  * URL-Encode POST data in AJAX side of address parser before POST'ing it.
  * AddressParser works now.
  * Added new database dump (data from fs1 kept). No more InnoDB tables.
  * Only show date created, not time, on job order / candidate searches.
  * Made "Consider" the default action for "Add to Pipeline" searches.
  * Added "Show Candidate", "Show Client", and "Show Job Order" (where
    appropriate) icons to "Add to Pipeline" searches.
  * Show date created on Job Orders: Consider Candidate pipeline search.
  * Fixed SQL error in a candidate search function.
  * Don't show created time (just date) on "Recent Candidates" page (for
    consistency).
  * Added a blank index.php to ajax/, attachments/, and js/ keep people from
    getting a directory index.
  * Renamed ajax/parseAddress.php to ajax/getParsedAddress.php. AJAX actions
    will now take the format of ajax/get<Whatever>.php.
  * Fixed inputbox text font size.
  * Address parser can handle last names like O'Connor, O'Brian, etc.
  * Address parser can handle single character names now.
  * Moved parse box to the right side of the other input boxes on Add
    Candidate.
  * Added two more inline images.
  * Put Thumbs.db on cvsignore in images/.
  * Address parser can handle multiple-word cities now.
  * Fixed ArrayUtility::implodeRange(). It now actually does the imploding
    instead of just slicing the array.
  * Fixed a little bug in main.css: "text-align=left;" -> "text-align: left;".
  * urldecode() the _POST data in AJAX stuff because the automatic decoding
    in PHP doesn't seem to decode encoded single quotes, etc.
  * Allow tabs between elements in "City, State, Zip" in AddressParser.
  * Address parser can handle PO boxes and Rural Routes now.

8/16/2005
  * Renamed getHttpObject() to getXMLHttpObject() and made it able to detect
    more IE versions correctly.
  * Make parseAddress() (was parse_address()) add a random string to the POST
    data to keep IE from caching it.
  * Added an elementID parameter to parseAddress(). This is a step towards
    allowing more than one "AJAX thing" on a page.
  * Zip Code Lookup AJAX stuff now uses new getXMLHttpObject().
  * Added a few StringUtility functions for attempting to guess what part
    of "contact information" a string might be.
  * Added isStringFullName().
  * Improved phone number extension matching.
  * AJAX address parsing works (but not for phone numbers) on the Add Candidate
    page. This will be cleaned up and moved to a class soon.
  * Start of AddressParser class.

8/15/2005
  * Added "Only My Job Orders" view mode checkbox to Job Orders page. This
    closes bug 0000018.
  * deleteJobOrder now deletes associations from candidate_joborder.
  * Fixed JobOrders::addCandidateToPipeline(); it wouldn't let a candidate be
    considered for more than one job, and was not checking the siteID.
  * Delete any candidate->joborder associations when deleting a job order.
  * Show e-mail address on Contacts page.
  * Reformatted Add Job Order HTML.
  * More "Add" and "New" consistency.
  * returnPage code now allows for a different return page for each referring
    page to allow for multi-depth returns.
  * Added searchJobOrdersByJobOrderID().
  * Improvements to "Consider for a Job" page from Candidates page perspective.
  * Added missing site_id column to candidate_attachment (new database dump).
  * Check site_id in attachment functions.
  * Use empty() in ATSUser instead of $username = '', etc.
  * Fixed returnPage code for pages that don't have returnPages.
  * Forgot to rename EntryTable in JobOrdersEditPage.php; fixed.
  * Search by candidate ID wasn't pulling the e-mail from the db; fixed.

8/14/2005
  * More descriptive tooltip text on candidates page.
  * Fixed a missing quote in CandidatesPage.tpl
  * CandidatesEditPage displays now.
  * Make "Show Recent Candidates" the "Active" sub-tab when on that page.

8/12/2005
  * Start of Add Contact page.
  * Show All Job Orders is now also only one line.
  * Indexing fixes for user_id column in user table (new database dump).
  * Removed resume_path stuff from candidates code as it has been replaced
    by attachments.
  * Indexing fixes to candidate table (new database dump).
  * Converted text to tooltips on Action icons on Clients and Candidates pages.
  * Converted text to tooltips on Action icons on Contacts page.
  * Removed client_contact table.
  * Fixes to contact table structure (new database dump).
  * Optimized job order queries.
  * Fixed some table indexes (new database dump).
  * Header File Author updates.
  * Improvements to the Misc module.
  * Added Contacts::addContact() method.
  * Added Clients::getClientsIDArray() for populating the client dropdowns.
  * Add Contact works now.
  * Candidates::getCandidate() and Candidates::getAttachments() now require
    a site ID.
  * Created ContactsAddPage module.
  * Added siteID requirement to several more database library functions.
  * Added Contacts::getContact().
  * Show entered_by_user on Contact Details.
  * Edit form display on ContactsEditPage works correctly now.
  * More updates to Edit Contact code.

8/11/2005
  * Added blank SettingsPage module.
  * Settings link works now.
  * Created a candidate_joborder_notes table for use with specific engagement
    notes. New database dump.
  * Started Settings module.
  * Created some JavaScript validation library functions, including a
    MM-DD-YY date validator.
  * UNIX-format js/lib.js.
  * "Portions Copyright" header on js/lib.js (we can't claim full Copyright
    since it isn't all our code).
  * Start of new form validation code (Prado's sucks and never works).
    Server-side date validation code always returns false right now, as it
    hasn't been written yet.
  * Start of new DateUtility library.
  * Reformatted footers.
  * Cleaned up JavaScript date validation code a bit.
  * Server-side date validation works now. This closes bug 0000020.
  * Added DateUtility::convertMMDDYYToYYYYMMDD() for converting dates to a
    format MySQL likes.
  * Start dates get added to the database now when editing. Add still needs
    to allow for start dates.
  * More date validation fixes.

8/10/2005
  * No longer allow duplicate candidates to be added to the pipeline. Still
    needs error handling. This closes bug 0000012.
  * Moved js include in AJAX parse address demo to <head></head> block. It
    works on my machine now.
  * Added some methods to ATSUser.php to allow "return page" support for
    returning to a page after an action is preformed.
  * Use new returnPage code for Add Client on Add Job Order.
  * UNIX-format stuff in the ajax/ directory.
  * Allow a candidate to be added from the Consider Candidate page on Job
    Orders Edit page (uses new returnPage code).
  * Changed "Add New Client" on Add Job Order to "New Client" for consistency.
  * Added icon to Add Job Order on Client Details.
  * Added actions to job orders listing.
  * Added actions to clients listing.
  * Added icon to Consider Candidate on Job Order Details.
  * Added icon to Consider on Consider Candidate search results Action column.
  * Added start_date and end_date fields to joborder (new database dump).
  * Added actions to Contacts page.
  * HTML quoting fixes.
  * Fixed "delete_inline" icon (wasn't vertically centered).
  * Cleaned up Search Candidates page.
  * "Search Client" -> "Search Clients".
  * Show e-mail address on candidate searches.
  * More copyright headers.
  * Added a start date to job orders. This closes bug 0000003. Still needs
    datepicker / date validation. Modification fields don't actually modify
    the database yet.
  * Fixed "Assigned To" and "Entered By" being mixed up on several job
    orders pages.
  * Added icons for attachments.

8/9/2005
  * "Delete" link works on Candidates listing now (with confirmation). This
    closes bug 0000013.
  * "Consider" works from Candidates page now.
  * Added missing columns to some of the Job Orders listings.
  * Added "Delete" link on show contact page.
  * Improved "Consider for a Job" from show candidate page.
  * Add Candidate works now (fixed MySQL escaping error).
  * Fixed SQL error in job order search functions.
  * Renamed getJobOrderDetails() to getJobOrder().
  * Cleaned up getJobOrder().
  * Added delete button to job order details. This closes bug 0000009.
  * Submissions stats work now. This closes bug 0000010.
  * New database dump: started candidate_history table, indexing fixes on
    joborder table.
  * Attachments work now (still no way to delete yet).
  * Remove attachments for a client when the client is deleted.
  * Fixed Add Client "referrer" stuff.
  * Add Job Order works now (fixed MySQL escaping error). All
    mysql_real_escape_string() errors are fixed now.
  * "Add New Client" from Add Job Order works now.
  * Cleaned up HTML on Add Job Order.
  * Id -> ID in functions for consistency.
  * Job Order Search pages / functions now only show jobs from the current
    site ID.
  * "Consider for a Job" page now only shows Active / OnHold job orders.
  * addJobOrder now returns a job order ID.
  * You are now taken to the Job Order Details page when you add a job order.
  * Made candidate name bold on Candidate Details page for consistency.
  * Created an index on 'status' for joborder table (new DB dump).
  * Changed "Show" navigation link for Contacts and Candidates to "Show
    Recent".
  * Made pipeline table on Client Details sortable. This closes bug 0000007.
  * Form validation on Add New Candidate works now (this is out of order, but
    I seem to have fixed it some time today). This closes bug 0000014.
  * Fixed a tab error on Contacts.

8/8/2005
  * "Edit Job Order" -> "Edit" on JobOrdersEditPage.tpl.
  * Added interview statistics to HomePage.
  * Added total submissions to "To Date" on HomePage.
  * Renamed "ClientsDetailsPage" to "ClientsEditPage" for consistency.
  * Fixed "address_1" and "address1" variable naming consistency (use
    "address1").
  * Created a new database dump for above change.
  * Edit client works now (fixed MySQL escaping error).
  * Moved searchJobOrdersByID() from Candidates library to JobOrders.
  * Cleaned up Add Candidate To Pipeline on Job Orders.
  * Wildcard is no longer required for searching for candidates on Add
    Candidate To Pipeline on Job Orders.
  * Add Candidate To Pipeline on Job Orders now allows searching by either
    "Firstname Lastname" or "Lastname Firstname" or "Lastname, Firstname"
    automatically.
  * Added copyright header to library files.
  * Removed JobOrderCandidatePage*; moved functionality to JobOrdersEditPage*.
  * Candidate names are now links in Add Candidate To Pipeline on Job Orders;
    Add Candidate To Pipeline actually works now.
  * Fixed date_created sorting / format on job orders lists.
  * Removed "Action" from job orders lists.
  * Candidates search table on Add Candidate To Pipeline on Job Orders is
    sortable now.
  * More statistics on home page.
  * A few cosmetic fixes here and there.
  * "Id" -> "ID" in variable names.
  * Added getLastXContacts() to contacts library.
  * Show last 15 contacts on Contacts page.
  * New database dump; changes to contacts table structure.
  * Another new database dump.. e-mail addresses don't need to be TEXT columns.
  * Contacts page works now.
  * Moved quotes.sql to db/.
  * Updated js/prado_validator.js to latest version.
  * Fixed bug 0000001 (Mantis). Login fields render evenly on IE now.
  * Use zip code autocomplete on Add Candidate page.
  * Add icons for actions on Candidates listing.

8/7/2005
  * Fixed a couple of attachments-related fatal errors.
  * Attachments almost work now. Attachments will be delayed until a few more
    high-priority things are finished.
  * Don't use data binding for client details page.
  * Cleaned up client details page.

8/5/2005
  * Fixed a few more links.
  * Clear button works on add candidate form now.
  * Login no longer requires a site ID.
  * Prado is a little faster now... used a speedup hack from the Prado forums.
    Copy ./prado_patches/prado.php over prado/framework/prado.php.
  * Fixes to sottable.js; works with alternating rows now.
  * Candidates page takes you to the detail view for that candidate upon add.
  * addCandidate() returns a candidate ID now.
  * More HTML cleanups.
  * More tables are sortable now.
  * Fixed data/number regex matching in sorttable.js.
  * Sped up sorttable.js's resorting function a bit.
  * Basic SQL error handling for addCandidate().
  * Start of candidate attachments: added Candidates::getNextAttachmentID().
  * Show attachments on candidate details page.

8/4/2005
  * Add new candidate works now (fixed MySQL escaping error).
  * Fixed home page link from candidate details.
  * Fixed more broken links.
  * Lots of HTML cleanups.
  * Added client contacts tab.
  * Added Contacts page (empty for now).
  * Added "lib/Clients.php" class library.
  * More HTML cleanups.
  * Wildcard is no longer required for searching by job title on search job
    orders.
  * Use include_once() for including libraries, so that libraries can include
    other libraries.
  * Use DatabaseUtility::escapeString() on searchJobOrdersByTitle().
  * Cleaned up job order search page.
  * Move searchJobOrdersByTitle() from Candidates library to JobOrders library.
  * Fixed "enter button submits wrong form" but on job order search page.
  * Created candidate_resume table for storing binary resume data as well as
    related metadata (see new data dump).
  * Renamed candidate_resume to candidate_attachment; no longer storing in
    the MySQL table.
  * Candidates page reformatting / cleanups.
  * Started cleaning up sorttables.js in preparation of fixing table
    "alternating row" bug.

8/3/2005
  * Renamed addToPipeline() to addCandidateToPipeline().
  * "Del" link works now on pipeline table on view job order details page.
  * Editing a job order now brings the user back to the job order details page.
  * Adding candidates to the pipeline from the job order details page works now.
  * Added "Assigned To" column on job order view pages.
  * Cleaned up main.css formatting.
  * Added JavaScript delete confirmation for deleting a candidate from a job
    order pipeline.
  * Changed "Contract" status to "C" (note: import new database dump after
    upgrading; this involved changes to the table design and data of
    "joborder").
  * Submit now works from job order details page pipeline (with confirmation
    as well).
  * Added new database dump in db/.
  * Fixed sort order of job orders on show page. Order by days old and then
    by date created.
  * Changed "shorthand" '<?' tags to the '<?php' format.
  * UNIX-format sorttable.js.
  * CSS fixes.
  * Converted getLast15Candidates($siteID) to getLastXCandidates($siteID, X)
    for future flexibility.
  * Fixed "date created" sorting bug on candidates view page.
  * Editing candidates works now (fixed MySQL escaping error).
  * "Consider for a Job" on candidates page works now.
  * You can now delete a candidate from a pipeline from that candidate's
    details page.
  * More indenting/formatting.

8/2/2005
  * Indenting/reformatting.

8/1/2005
  * Indenting/reformatting of library files.
  * Explicitly declare library public class members as public.
  * Changed TRepeater / dataBind() usage in HomePage.* to TLabel / setText().
  * All statistics on home page other than submission counts work now.
  * Fixed "last week" logic in GetLastWeeksJobOrders() (WEEK(NOW() - 1)
    returns the week number for "one second ago", where (WEEK(NOW()) - 1)
    returns one week number before NOW().
  * "Count" functions now return integers instead of record sets.
    _____________________________________________________________________

   Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
