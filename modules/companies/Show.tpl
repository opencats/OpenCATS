<?php
include_once(LEGACY_ROOT . '/vendor/autoload.php');

use OpenCATS\UI\QuickActionMenu;

$extraFieldSplit = intdiv(count($this->extraFieldRS), 2);
?>
<?php TemplateUtility::printHeader(
    'Company - ' . $this->data['name'],
    array(
        'js/activity.js',
        'js/sorttable.js',
        'js/attachment.js'
    )
); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>

<script>
window.CATSUserDateFormat = <?php echo Template::escapeJs(
    $_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'
); ?>;
window.CATSTimeFormat24 = <?php echo $_SESSION['CATS']->isTimeFormat24() ? 'true' : 'false'; ?>;
</script>

<?php TemplateUtility::printQuickSearch(); ?>

<main id="main" class="container-fluid py-2 oc-company-show-page">
<div id="contents" class="oc-company-show-content">

<section class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 oc-company-header">
<div class="d-flex align-items-center gap-2">
<div>
<div class="small text-body-secondary">Company</div>
<h1 class="h4 fw-semibold mb-0">
<span class="<?php echo Template::escapeAttr($this->data['titleClass']); ?>">
<?php $this->_($this->data['name']); ?>
</span>
</h1>
</div>

<?php TemplateUtility::printSingleQuickActionMenu(
    new QuickActionMenu(
        DATA_ITEM_COMPANY,
        $this->companyID,
        $_SESSION['CATS']->getAccessLevel('companies.edit')
    )
); ?>
</div>

<div class="d-flex flex-wrap align-items-center gap-1 oc-company-actions">
<?php if ($this->getUserAccessLevel('companies.edit') >= ACCESS_LEVEL_EDIT): ?>
<a
id="edit_link"
class="btn btn-sm btn-primary"
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=companies&a=edit&companyID=' . $this->companyID
); ?>"
>
Edit
</a>
<?php endif; ?>

<?php if (
    $this->getUserAccessLevel('companies.delete') >= ACCESS_LEVEL_DELETE &&
    $this->data['defaultCompany'] != 1
): ?>
<form
id="delete_link"
method="post"
action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName() . '?m=companies&a=delete'
); ?>"
class="d-inline"
onsubmit="return confirm('Delete this company?');"
>
<input type="hidden" name="postback" value="postback">
<input
type="hidden"
name="companyID"
value="<?php echo Template::escapeAttr($this->companyID); ?>"
>
<button type="submit" class="btn btn-sm btn-outline-danger">
Delete
</button>
</form>
<?php endif; ?>

<?php if ($this->privledgedUser): ?>
<a
id="history_link"
class="btn btn-sm btn-outline-secondary"
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=settings&a=viewItemHistory&dataItemType=200&dataItemID=' .
    $this->companyID
); ?>"
>
View History
</a>
<?php endif; ?>
</div>
</section>

<section class="card mb-2 oc-company-details">
<div class="card-header bg-body-subtle py-1 px-2 fw-semibold">
Company Details
</div>

<div class="card-body p-2">
<div class="row g-3 small">
<div class="col-12 col-lg-6">
<dl class="row mb-0">
<dt class="col-sm-4 text-body-secondary fw-semibold">Primary Phone</dt>
<dd class="col-sm-8"><?php $this->_($this->data['phone1']); ?></dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Secondary Phone</dt>
<dd class="col-sm-8"><?php $this->_($this->data['phone2']); ?></dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Fax Number</dt>
<dd class="col-sm-8"><?php $this->_($this->data['faxNumber']); ?></dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Address</dt>
<dd class="col-sm-8">
<?php echo nl2br(Template::escapeHtml($this->data['address'])); ?>

<?php if (!empty($this->data['address2'])): ?>
<br><?php $this->_($this->data['address2']); ?>
<?php endif; ?>

<?php if (
    !empty($this->data['cityAndState']) ||
    !empty($this->data['zip'])
): ?>
<br>
<?php $this->_($this->data['cityAndState']); ?>
<?php $this->_($this->data['zip']); ?>
<?php endif; ?>

<?php if (!empty($this->data['googleMaps'])): ?>
&nbsp;<?php echo $this->data['googleMaps']; ?>
<?php endif; ?>
</dd>

<?php for ($i = 0; $i < $extraFieldSplit; $i++): ?>
<dt class="col-sm-4 text-body-secondary fw-semibold">
<?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>
</dt>
<dd class="col-sm-8">
<?php echo $this->extraFieldRS[$i]['display']; ?>
</dd>
<?php endfor; ?>
</dl>
</div>

<div class="col-12 col-lg-6">
<dl class="row mb-0">
<dt class="col-sm-4 text-body-secondary fw-semibold">Billing Contact</dt>
<dd class="col-sm-8">
<?php if (!empty($this->data['billingContact'])): ?>
<a href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=show&contactID=' .
    $this->data['billingContact']
); ?>">
<?php $this->_($this->data['billingContactFullName']); ?>
</a>
<?php else: ?>
<?php $this->_($this->data['billingContactFullName']); ?>
<?php endif; ?>
</dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Web Site</dt>
<dd class="col-sm-8">
<?php if (!empty($this->data['url'])): ?>
<a
href="<?php echo Template::escapeUrl($this->data['url']); ?>"
target="_blank"
rel="noopener noreferrer"
>
<?php $this->_($this->data['url']); ?>
</a>
<?php endif; ?>
</dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Key Technologies</dt>
<dd class="col-sm-8">
<?php $this->_($this->data['keyTechnologies']); ?>
</dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Created</dt>
<dd class="col-sm-8">
<?php $this->_($this->data['dateCreated']); ?>
<?php if (!empty($this->data['enteredByFullName'])): ?>
(<?php $this->_($this->data['enteredByFullName']); ?>)
<?php endif; ?>
</dd>

<dt class="col-sm-4 text-body-secondary fw-semibold">Owner</dt>
<dd class="col-sm-8">
<?php $this->_($this->data['ownerFullName']); ?>
</dd>

<?php for (
    $i = $extraFieldSplit;
    $i < count($this->extraFieldRS);
    $i++
): ?>
<dt class="col-sm-4 text-body-secondary fw-semibold">
<?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>
</dt>
<dd class="col-sm-8">
<?php echo $this->extraFieldRS[$i]['display']; ?>
</dd>
<?php endfor; ?>
</dl>
</div>
</div>

<?php if (count($this->departmentsRS) > 0): ?>
<div class="border-top mt-2 pt-2 small oc-company-departments">
<span class="fw-semibold">Departments:</span>

<?php foreach ($this->departmentsRS as $departmentRecord): ?>
<span class="badge text-bg-light border fw-normal me-1">
<?php $this->_($departmentRecord['name']); ?>
</span>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>

<section class="card mb-2 oc-company-attachments-notes">
<div class="card-header bg-body-subtle py-1 px-2 fw-semibold">
Attachments &amp; Notes
</div>

<div class="card-body p-2">
<div class="row g-3">
<div class="col-12 col-lg-6">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
<span class="small fw-semibold">Attachments</span>

<?php if (
    $this->getUserAccessLevel('companies.createAttachment') >= ACCESS_LEVEL_EDIT
): ?>
<span class="small">
<?php if (isset($this->attachmentLinkHTML)): ?>
<?php echo $this->attachmentLinkHTML; ?>
<?php else: ?>
<a
href="#"
onclick="showPopWin(<?php echo Template::escapeJsAttr(
    CATSUtility::getIndexName() .
    '?m=companies&a=createAttachment&companyID=' .
    $this->companyID
); ?>, 400, 125, null); return false;"
>
<?php endif; ?>

Add Attachment
</a>
</span>
<?php endif; ?>
</div>

<?php if (count($this->attachmentsRS) > 0): ?>
<div class="table-responsive">
<table class="table table-sm align-middle mb-0">
<tbody>
<?php foreach ($this->attachmentsRS as $attachmentsData): ?>
<tr>
<td>
<?php echo $attachmentsData['retrievalLink']; ?>
<img
src="<?php echo Template::escapeUrl(
    $attachmentsData['attachmentIcon']
); ?>"
alt=""
width="16"
height="16"
>
<?php $this->_($attachmentsData['originalFilename']); ?>
</a>
</td>

<td class="small text-body-secondary text-nowrap">
<?php $this->_($attachmentsData['dateCreated']); ?>
</td>

<td class="text-end">
<?php if (
    $this->getUserAccessLevel(
        'companies.deleteAttachment'
    ) >= ACCESS_LEVEL_DELETE
): ?>
<form
method="post"
action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName() .
    '?m=companies&a=deleteAttachment'
); ?>"
class="d-inline"
onsubmit="return confirm('Delete this attachment?');"
>
<input
type="hidden"
name="postback"
value="postback"
>
<input
type="hidden"
name="companyID"
value="<?php echo Template::escapeAttr(
    $this->companyID
); ?>"
>
<input
type="hidden"
name="attachmentID"
value="<?php echo Template::escapeAttr(
    $attachmentsData['attachmentID']
); ?>"
>
<button
type="submit"
class="btn btn-sm btn-outline-danger"
>
Delete
</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="small text-body-secondary">
No attachments.
</div>
<?php endif; ?>
</div>

<div class="col-12 col-lg-6">
<div class="small fw-semibold mb-1">Misc. Notes</div>

<?php if ($this->isShortNotes): ?>
<div id="shortNotes" class="small">
<?php echo $this->data['shortNotes']; ?>
<span class="moreText">...</span>
<div class="mt-1">
<a
href="#"
class="moreText"
onclick="toggleNotes(); return false;"
>
More
</a>
</div>
</div>

<div id="fullNotes" class="small" style="display:none;">
<?php echo $this->data['notes']; ?>
<div class="mt-1">
<a
href="#"
class="moreText"
onclick="toggleNotes(); return false;"
>
Less
</a>
</div>
</div>
<?php else: ?>
<div id="shortNotes" class="small">
<?php echo $this->data['notes']; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</section>

<section class="card mb-2 oc-company-joborders">
<div class="card-header bg-body-subtle py-1 px-2">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
<span class="fw-semibold">Job Orders</span>

<?php if ($this->getUserAccessLevel('joborders.add') >= ACCESS_LEVEL_EDIT): ?>
<a
class="btn btn-sm btn-primary"
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=joborders&a=add&selected_company_id=' .
    $this->companyID
); ?>"
>
Add Job Order
</a>
<?php endif; ?>
</div>
</div>

<div class="table-responsive">
<table class="sortable table table-sm table-striped table-hover align-middle mb-0">
<thead>
<tr>
<th scope="col" class="text-nowrap">ID</th>
<th scope="col">Title</th>
<th scope="col">Type</th>
<th scope="col">Status</th>
<th scope="col" class="text-nowrap">Created</th>
<th scope="col" class="text-nowrap">Modified</th>
<th scope="col" class="text-nowrap">Start</th>
<th scope="col">Age</th>
<th scope="col">S</th>
<th scope="col">P</th>
<th scope="col">Recruiter</th>
<th scope="col">Owner</th>
<th scope="col" class="text-center">Action</th>
</tr>
</thead>

<tbody>
<?php foreach ($this->jobOrdersRS as $jobOrdersData): ?>
<tr>
<td><?php $this->_($jobOrdersData['jobOrderID']); ?></td>

<td>
<a href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=joborders&a=show&jobOrderID=' .
    $jobOrdersData['jobOrderID']
); ?>">
<?php $this->_($jobOrdersData['title']); ?>
</a>
</td>

<td><?php $this->_($jobOrdersData['type']); ?></td>
<td><?php $this->_($jobOrdersData['status']); ?></td>
<td class="text-nowrap"><?php $this->_($jobOrdersData['dateCreated']); ?></td>
<td class="text-nowrap"><?php $this->_($jobOrdersData['dateModified']); ?></td>
<td class="text-nowrap"><?php $this->_($jobOrdersData['startDate']); ?></td>
<td><?php $this->_($jobOrdersData['daysOld']); ?></td>
<td><?php $this->_($jobOrdersData['submitted']); ?></td>
<td><?php $this->_($jobOrdersData['pipeline']); ?></td>
<td><?php $this->_($jobOrdersData['recruiterAbbrName']); ?></td>
<td><?php $this->_($jobOrdersData['ownerAbbrName']); ?></td>

<td class="text-center">
<?php if (
    $this->getUserAccessLevel('joborders.edit') >= ACCESS_LEVEL_EDIT
): ?>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=joborders&a=edit&jobOrderID=' .
    $jobOrdersData['jobOrderID']
); ?>"
title="Edit"
>
<img
src="images/actions/edit.gif"
width="16"
height="16"
alt="Edit"
>
</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>

<section class="card mb-2 oc-company-contacts">
<div class="card-header bg-body-subtle py-1 px-2">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
<span class="fw-semibold">Contacts</span>

<?php if ($this->getUserAccessLevel('contacts.add') >= ACCESS_LEVEL_EDIT): ?>
<a
class="btn btn-sm btn-primary"
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=add&selected_company_id=' .
    $this->companyID
); ?>"
>
Add Contact
</a>
<?php endif; ?>
</div>
</div>

<div class="table-responsive">
<table class="sortable table table-sm table-striped table-hover align-middle mb-0">
<thead>
<tr>
<th scope="col" class="text-nowrap">First Name</th>
<th scope="col" class="text-nowrap">Last Name</th>
<th scope="col">Title</th>
<th scope="col">Department</th>
<th scope="col" class="text-nowrap">Work Phone</th>
<th scope="col" class="text-nowrap">Cell Phone</th>
<th scope="col" class="text-nowrap">Created</th>
<th scope="col">Owner</th>
<th scope="col" class="text-center">Action</th>
</tr>
</thead>

<tbody>
<?php if (count($this->contactsRSWC) != 0): ?>
<?php foreach ($this->contactsRSWC as $rowNumber => $contactsData): ?>
<tr id="ContactsDefault<?php echo Template::escapeAttr($rowNumber); ?>">
<td>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=show&contactID=' .
    $contactsData['contactID']
); ?>"
class="<?php echo Template::escapeAttr(
    $contactsData['linkClass']
); ?>"
>
<?php $this->_($contactsData['firstName']); ?>
</a>
</td>

<td>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=show&contactID=' .
    $contactsData['contactID']
); ?>"
class="<?php echo Template::escapeAttr(
    $contactsData['linkClass']
); ?>"
>
<?php $this->_($contactsData['lastName']); ?>
</a>
</td>

<td><?php $this->_($contactsData['title']); ?></td>
<td><?php $this->_($contactsData['department']); ?></td>
<td class="text-nowrap"><?php $this->_($contactsData['phoneWork']); ?></td>
<td class="text-nowrap"><?php $this->_($contactsData['phoneCell']); ?></td>
<td class="text-nowrap"><?php $this->_($contactsData['dateCreated']); ?></td>
<td><?php $this->_($contactsData['ownerAbbrName']); ?></td>

<td class="text-center text-nowrap">
<?php if (!empty($contactsData['email1'])): ?>
<a
href="<?php echo Template::escapeUrl(
    'mailto:' . $contactsData['email1']
); ?>"
title="<?php echo Template::escapeAttr(
    'Send E-Mail (' . $contactsData['email1'] . ')'
); ?>"
>
<img
src="images/actions/email.gif"
width="16"
height="16"
alt="Email"
>
</a>
<?php else: ?>
<img
src="images/actions/email_no.gif"
width="16"
height="16"
alt=""
title="No E-Mail Address"
>
<?php endif; ?>

<?php if (
    $this->getUserAccessLevel('contacts.edit') >= ACCESS_LEVEL_EDIT
): ?>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=edit&contactID=' .
    $contactsData['contactID']
); ?>"
title="Edit"
>
<img
src="images/actions/edit.gif"
width="16"
height="16"
alt="Edit"
>
</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

<?php if (
    count($this->contactsRSWC) != count($this->contactsRS) &&
    count($this->contactsRS) != 0
): ?>
<?php foreach ($this->contactsRS as $rowNumber => $contactsData): ?>
<tr
id="ContactsFull<?php echo Template::escapeAttr($rowNumber); ?>"
style="display:none;"
>
<td>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=show&contactID=' .
    $contactsData['contactID']
); ?>"
class="<?php echo Template::escapeAttr(
    $contactsData['linkClass']
); ?>"
>
<?php $this->_($contactsData['firstName']); ?>
</a>
</td>

<td>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=show&contactID=' .
    $contactsData['contactID']
); ?>"
class="<?php echo Template::escapeAttr(
    $contactsData['linkClass']
); ?>"
>
<?php $this->_($contactsData['lastName']); ?>
</a>
</td>

<td><?php $this->_($contactsData['title']); ?></td>
<td><?php $this->_($contactsData['department']); ?></td>
<td class="text-nowrap"><?php $this->_($contactsData['phoneWork']); ?></td>
<td class="text-nowrap"><?php $this->_($contactsData['phoneCell']); ?></td>
<td class="text-nowrap"><?php $this->_($contactsData['dateCreated']); ?></td>
<td><?php $this->_($contactsData['ownerAbbrName']); ?></td>

<td class="text-center text-nowrap">
<?php if (!empty($contactsData['email1'])): ?>
<a
href="<?php echo Template::escapeUrl(
    'mailto:' . $contactsData['email1']
); ?>"
title="<?php echo Template::escapeAttr(
    'Send E-Mail (' . $contactsData['email1'] . ')'
); ?>"
>
<img
src="images/actions/email.gif"
width="16"
height="16"
alt="Email"
>
</a>
<?php else: ?>
<img
src="images/actions/email_no.gif"
width="16"
height="16"
alt=""
title="No E-Mail Address"
>
<?php endif; ?>

<?php if (
    $this->getUserAccessLevel('contacts.edit') >= ACCESS_LEVEL_EDIT
): ?>
<a
href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=edit&contactID=' .
    $contactsData['contactID']
); ?>"
title="Edit"
>
<img
src="images/actions/edit.gif"
width="16"
height="16"
alt="Edit"
>
</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>

<?php if (count($this->contactsRSWC) != count($this->contactsRS)): ?>
<div class="card-footer bg-body py-1 px-2">
<a
href="javascript:void(0)"
id="linkShowAll"
class="small"
onclick="<?php echo Template::escapeAttr(
    'javascript:for (i = 0; i < ' .
    count($this->contactsRSWC) .
    '; i++) document.getElementById(\'ContactsDefault\'+i).style.display=\'none\'; ' .
    'for (i = 0; i < ' .
    count($this->contactsRS) .
    '; i++) document.getElementById(\'ContactsFull\'+i).style.display=\'\'; ' .
    'document.getElementById(\'linkShowAll\').style.display=\'none\'; ' .
    'document.getElementById(\'linkHideSome\').style.display=\'\';'
); ?>"
>
Show contacts who have left
(<?php echo count($this->contactsRS) - count($this->contactsRSWC); ?>)
</a>

<a
href="javascript:void(0)"
id="linkHideSome"
class="small"
style="display:none;"
onclick="<?php echo Template::escapeAttr(
    'javascript:for (i = 0; i < ' .
    count($this->contactsRSWC) .
    '; i++) document.getElementById(\'ContactsDefault\'+i).style.display=\'\'; ' .
    'for (i = 0; i < ' .
    count($this->contactsRS) .
    '; i++) document.getElementById(\'ContactsFull\'+i).style.display=\'none\'; ' .
    'document.getElementById(\'linkShowAll\').style.display=\'\'; ' .
    'document.getElementById(\'linkHideSome\').style.display=\'none\';'
); ?>"
>
Hide contacts who have left
(<?php echo count($this->contactsRS) - count($this->contactsRSWC); ?>)
</a>
</div>
<?php endif; ?>
</section>

<section class="card oc-company-activity">
<div class="card-header bg-body-subtle py-1 px-2 fw-semibold">
Activity
</div>

<div class="table-responsive">
<table
id="activityTable"
class="sortable table table-sm table-striped table-hover align-middle mb-0"
>
<thead>
<tr>
<th scope="col" class="text-nowrap">Date</th>
<th scope="col">Type</th>
<th scope="col">Regarding</th>
<th scope="col">Contact</th>
<th scope="col">Notes</th>
<th scope="col" class="text-nowrap">Entered By</th>
<th scope="col" class="text-center">Action</th>
</tr>
</thead>

<tbody>
<?php foreach ($this->activityRS as $activityData): ?>
<tr>
<td
id="activityDate<?php echo Template::escapeAttr(
    $activityData['activityID']
); ?>"
class="text-nowrap"
>
<?php $this->_($activityData['dateCreated']); ?>
</td>

<td id="activityType<?php echo Template::escapeAttr(
    $activityData['activityID']
); ?>">
<?php $this->_($activityData['typeDescription']); ?>
</td>

<td
id="activityRegarding<?php echo Template::escapeAttr(
    $activityData['activityID']
); ?>"
data-joborder-id="<?php echo Template::escapeAttr(
    isset($activityData['jobOrderID'])
    ? $activityData['jobOrderID']
    : ''
); ?>"
>
<?php $this->_($activityData['regarding']); ?>
</td>

<td>
<?php if (!empty($activityData['contactID'])): ?>
<a href="<?php echo Template::escapeUrl(
    CATSUtility::getIndexName() .
    '?m=contacts&a=show&contactID=' .
    $activityData['contactID']
); ?>">
<?php $this->_($activityData['contactFullName']); ?>
</a>
<?php else: ?>
<?php $this->_($activityData['contactFullName']); ?>
<?php endif; ?>
</td>

<td id="activityNotes<?php echo Template::escapeAttr(
    $activityData['activityID']
); ?>">
<?php echo nl2br(
    Template::escapeHtml($activityData['notes'])
); ?>
</td>

<td><?php $this->_($activityData['enteredByAbbrName']); ?></td>

<td class="text-center text-nowrap">
<?php if (
    $this->getUserAccessLevel(
        'contacts.editActivity'
    ) >= ACCESS_LEVEL_EDIT
): ?>
<a
href="#"
id="editActivity<?php echo Template::escapeAttr(
    $activityData['activityID']
); ?>"
onclick="Activity_editEntry(
    <?php echo (int) $activityData['activityID']; ?>,
    <?php echo (int) $activityData['contactID']; ?>,
    <?php echo (int) DATA_ITEM_CONTACT; ?>,
    <?php echo Template::escapeJsAttr(
        $this->sessionCookie
    ); ?>
    ); return false;"
    title="Edit"
    >
    <img
    src="images/actions/edit.gif"
    width="16"
    height="16"
    alt="Edit"
    >
    </a>
    <?php endif; ?>

    <?php if (
        $this->getUserAccessLevel(
            'contacts.deleteActivity'
        ) >= ACCESS_LEVEL_EDIT
    ): ?>
    <a
    href="#"
    id="deleteActivity<?php echo Template::escapeAttr(
        $activityData['activityID']
    ); ?>"
    onclick="Activity_deleteEntry(
        <?php echo (int) $activityData['activityID']; ?>,
        <?php echo Template::escapeJsAttr(
            $this->sessionCookie
        ); ?>
        ); return false;"
        title="Delete"
        >
        <img
        src="images/actions/delete.gif"
        width="16"
        height="16"
        alt="Delete"
        >
        </a>
        <?php endif; ?>
        </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
        </div>
        </section>

        </div>
        </main>

        <?php TemplateUtility::printFooter(); ?>
