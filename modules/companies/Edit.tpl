<?php
TemplateUtility::printHeader(
    'Companies',
    array(
        'modules/companies/validator.js',
        'js/sweetTitles.js',
        'js/listEditor.js'
    )
);
?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>

<main id="main" class="container-fluid py-2 oc-company-edit-page">
<div id="contents" class="oc-company-edit-content">

<section class="oc-page-header mb-2">
<h1 class="h5 fw-semibold mb-0">Edit Company</h1>
</section>

<form name="editCompanyForm" id="editCompanyForm"
action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName() . '?m=companies&a=edit'
); ?>"
method="post"
onsubmit="return checkEditForm(document.editCompanyForm);"
autocomplete="off">

<input type="hidden" name="postback" id="postback" value="postback">
<input type="hidden" id="companyID" name="companyID"
value="<?php echo Template::escapeAttr($this->companyID); ?>">

<div class="row g-2 mb-2">

<div class="col-12 col-xl-6 d-flex">
<section class="card w-100 oc-company-basic-information">
<div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
Basic Information
</div>

<div class="card-body p-2">

<div class="row g-2 align-items-center mb-2">
<label id="nameLabel" for="name"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Company Name
</label>
<div class="col-sm-8 col-lg-9">
<?php if ($this->data['defaultCompany'] != 1): ?>
<div class="d-flex align-items-center gap-1">
<input type="text" name="name" id="name"
value="<?php $this->_($this->data['name']); ?>"
class="form-control form-control-sm">
<span class="text-danger" title="Required">*</span>
</div>
<?php else: ?>
<div class="form-control-plaintext form-control-sm">
<?php $this->_($this->data['name']); ?>
</div>
<input type="hidden" name="name" id="name"
value="<?php $this->_($this->data['name']); ?>">
<?php endif; ?>
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="billingContactLabel" for="billingContact"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Billing Contact
</label>
<div class="col-sm-8 col-lg-9">
<select tabindex="3" id="billingContact" name="billingContact"
class="form-select form-select-sm">
<option value="-1">None</option>

<?php foreach ($this->contactsRS as $contactsData): ?>
<option
value="<?php $this->_($contactsData['contactID']); ?>"
<?php if ($this->data['billingContact'] == $contactsData['contactID']): ?>selected<?php endif; ?>
>
<?php $this->_($contactsData['lastName']); ?>,
<?php $this->_($contactsData['firstName']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="departmentsLabel" for="departmentsSelect"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Departments
</label>
<div class="col-sm-8 col-lg-9">
<select tabindex="3" id="departmentsSelect"
name="departmentsSelect"
class="form-select form-select-sm"
onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentsSelect', 'departmentsCSV'); } this.value = 'num';">
<option value="edit">(Edit Departments)</option>
<option value="num" selected>
<?php echo count($this->departmentsRS); ?> Departments
</option>
<option value="nullline">-------------------------------</option>

<?php foreach ($this->departmentsRS as $department): ?>
<option value="<?php $this->_($department['name']); ?>">
<?php $this->_($department['name']); ?>
</option>
<?php endforeach; ?>
</select>

<input type="hidden" id="departmentsCSV" name="departmentsCSV"
value="<?php $this->_($this->departmentsString); ?>">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="urlLabel" for="url"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Web Site
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="url" id="url"
value="<?php $this->_($this->data['url']); ?>"
class="form-control form-control-sm">
</div>
</div>

<div class="row g-2 align-items-center">
<div class="col-sm-4 col-lg-3">
<span id="isHotLabel" class="form-label small mb-0">
Hot Company
</span>
</div>
<div class="col-sm-8 col-lg-9">
<div class="form-check mb-0">
<input type="checkbox" id="isHot" name="isHot"
class="form-check-input"
<?php if ($this->data['isHot'] == 1): ?>checked<?php endif; ?>>
<label class="form-check-label" for="isHot">
Mark as hot
</label>
</div>
</div>
</div>

</div>
</section>
</div>

<div class="col-12 col-xl-6 d-flex">
<section class="card w-100 oc-company-contact-information">
<div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
Contact Information
</div>

<div class="card-body p-2">

<div class="row g-2 align-items-center mb-2">
<label id="phone1Label" for="phone1"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Primary Phone
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="phone1" id="phone1"
value="<?php $this->_($this->data['phone1']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="phone2Label" for="phone2"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Secondary Phone
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="phone2" id="phone2"
value="<?php $this->_($this->data['phone2']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="faxNumberLabel" for="faxNumber"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Fax Number
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="faxNumber" id="faxNumber"
value="<?php $this->_($this->data['faxNumber']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="addressLabel" for="address"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Address
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="address" id="address"
value="<?php $this->_($this->data['address']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="address2Label" for="address2"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Address 2
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="address2" id="address2"
value="<?php $this->_($this->data['address2']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="cityLabel" for="city"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
City
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="city" id="city"
value="<?php $this->_($this->data['city']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="stateLabel" for="state"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
State
</label>
<div class="col-sm-8 col-lg-9">
<input type="text" name="state" id="state"
value="<?php $this->_($this->data['state']); ?>"
class="form-control form-control-sm"
onkeydown="document.getElementById('changeAddress').style.display='';">
</div>
</div>

<div class="row g-2 align-items-center mb-2">
<label id="zipLabel" for="zip"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Postal Code
</label>
<div class="col-sm-8 col-lg-9">
<div class="input-group input-group-sm">
<input type="text" name="zip" id="zip"
value="<?php $this->_($this->data['zip']); ?>"
class="form-control"
onkeydown="document.getElementById('changeAddress').style.display='';">

<button type="button"
class="btn btn-outline-secondary"
onclick="CityState_populate('zip', 'ajaxIndicator');">
Lookup
</button>
</div>

<img src="images/indicator2.gif" alt="AJAX"
id="ajaxIndicator" class="mt-1"
style="visibility: hidden;">
</div>
</div>

<div class="row g-2 align-items-center">
<label id="countryLabel" for="country"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Country
</label>
<div class="col-sm-8 col-lg-9">
<?php echo TemplateUtility::getCountrySelectHTML(
    'country',
    $this->data['country'],
    true,
    'form-select form-select-sm',
    ''
); ?>
</div>
</div>

<div id="changeAddress" class="alert alert-light border small mt-2 mb-0"
style="display:none;">
<div class="fw-semibold mb-1">
Synchronize contact addresses?
</div>

<label for="updateContacts" class="form-label mb-1">
Edit all contacts address information to match the company address?
</label>

<select id="updateContacts" name="updateContacts"
class="form-select form-select-sm">
<option value="yes">Yes, synchronize addresses.</option>
<option value="no" selected>No, leave addresses unmodified.</option>
</select>
</div>

</div>
</section>
</div>

</div>

<section class="card mb-2 oc-company-other">
<div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
Other
</div>

<div class="card-body p-2">

<?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
<div class="row g-2 align-items-center mb-2">
<div class="col-sm-4 col-lg-3"
id="extraFieldTd<?php echo Template::escapeAttr($i); ?>">
<label id="extraFieldLbl<?php echo Template::escapeAttr($i); ?>"
class="form-label small mb-0">
<?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
</label>
</div>

<div class="col-sm-8 col-lg-9"
id="extraFieldData<?php echo Template::escapeAttr($i); ?>">
<?php echo $this->extraFieldRS[$i]['editHTML']; ?>
</div>
</div>
<?php endfor; ?>

<div class="row g-2 align-items-start mb-2">
<label id="ownerLabel" for="owner"
class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
Owner
</label>

<div class="col-sm-8 col-lg-9">
<div class="d-flex align-items-center gap-1">
<select id="owner" name="owner"
class="form-select form-select-sm"
<?php if (!$this->emailTemplateDisabled): ?>
onchange="document.getElementById('divOwnershipChange').style.display='';<?php if ($this->canEmail): ?> document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"
<?php endif; ?>>
<option value="-1">None</option>

<?php foreach ($this->usersRS as $usersData): ?>
<option
value="<?php $this->_($usersData['userID']); ?>"
<?php if ($this->data['owner'] == $usersData['userID']): ?>selected<?php endif; ?>
>
<?php $this->_($usersData['lastName']); ?>,
<?php $this->_($usersData['firstName']); ?>
</option>
<?php endforeach; ?>
</select>

<span class="text-danger" title="Required">*</span>
</div>

<div id="divOwnershipChange" class="form-check mt-2"
style="display:none;">
<input type="checkbox"
name="ownershipChange"
id="checkboxOwnershipChange"
class="form-check-input"
<?php if (!$this->canEmail): ?>disabled<?php endif; ?>>

<label class="form-check-label"
for="checkboxOwnershipChange">
    E-Mail new owner of change
    </label>

    <?php if (!$this->canEmail): ?>
    <div class="form-text">
    E-Mail notification is unavailable.
    </div>
    <?php endif; ?>
    </div>
    </div>
    </div>

    <div class="row g-2 align-items-center mb-2">
    <label id="keyTechnologiesLabel" for="keyTechnologies"
    class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
    Key Technologies
    </label>

    <div class="col-sm-8 col-lg-9">
    <input type="text" id="keyTechnologies"
    name="keyTechnologies"
    value="<?php $this->_($this->data['keyTechnologies']); ?>"
    class="form-control form-control-sm">
    </div>
    </div>

    <div class="row g-2">
    <label id="notesLabel" for="notes"
    class="col-sm-4 col-lg-3 col-form-label col-form-label-sm">
    Misc. Notes
    </label>

    <div class="col-sm-8 col-lg-9">
    <textarea class="form-control form-control-sm"
    name="notes" id="notes"
    rows="5"><?php $this->_($this->data['notes']); ?></textarea>
    </div>
    </div>

    </div>
    </section>

    <div class="d-flex flex-wrap justify-content-end gap-2">
    <button type="submit" class="btn btn-sm btn-primary"
    name="submit" id="submit">
    Save
    </button>

    <button type="reset" class="btn btn-sm btn-outline-secondary"
    name="reset" id="reset">
    Reset
    </button>

    <a class="btn btn-sm btn-secondary"
    id="back"
    href="<?php echo Template::escapeUrl(
        CATSUtility::getIndexName() .
        '?m=companies&a=show&companyID=' . $this->companyID
    ); ?>">
    Back to Details
    </a>
    </div>

    </form>

    <script>
    document.editCompanyForm.name.focus();
    </script>

    </div>
    </main>

    <?php TemplateUtility::printFooter(); ?>
