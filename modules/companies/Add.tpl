<?php
TemplateUtility::printHeader(
    'Companies',
    array(
        'modules/companies/validator.js',
        'js/sweetTitles.js',
        'js/listEditor.js',
        'js/addressParser.js'
    )
);
?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>

<main id="main" class="container-fluid py-2 oc-company-add-page">
<div id="contents" class="oc-company-add-content">

<section class="oc-page-header mb-2">
<h1 class="h5 fw-semibold mb-0">Add Company</h1>
</section>

<form
name="addCompanyForm"
id="addCompanyForm"
action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName() . '?m=companies&a=add'
); ?>"
method="post"
onsubmit="return checkAddForm(document.addCompanyForm);"
autocomplete="off"
>
<input type="hidden" name="postback" id="postback" value="postback">

<section class="card mb-2 oc-company-basic-information">
<div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
Basic Information
</div>

<div class="card-body p-2">
<div class="row g-3">

<div class="col-12 col-lg-7">
<div class="row g-2 align-items-center mb-2">
<label
id="nameLabel"
for="name"
    class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
    >
    Company Name
    </label>
    <div class="col-sm-8 col-xl-9">
    <div class="d-flex align-items-center gap-1">
    <input
    type="text"
    name="name"
    id="name"
    class="form-control form-control-sm"
    >
    <span class="text-danger" title="Required">*</span>
    </div>
    </div>
    </div>

    <div class="row g-2 align-items-center mb-2">
    <label
    id="phone1Label"
    for="phone1"
        class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
        >
        Primary Phone
        </label>
        <div class="col-sm-8 col-xl-9">
        <input
        type="text"
        name="phone1"
        id="phone1"
        class="form-control form-control-sm"
        >
        </div>
        </div>

        <div class="row g-2 align-items-center mb-2">
        <label
        id="phone2Label"
        for="phone2"
            class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
            >
            Secondary Phone
            </label>
            <div class="col-sm-8 col-xl-9">
            <input
            type="text"
            name="phone2"
            id="phone2"
            class="form-control form-control-sm"
            >
            </div>
            </div>

            <div class="row g-2 align-items-center mb-2">
            <label
            id="faxNumberLabel"
            for="faxNumber"
                class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                >
                Fax Number
                </label>
                <div class="col-sm-8 col-xl-9">
                <input
                type="text"
                name="faxNumber"
                id="faxNumber"
                class="form-control form-control-sm"
                >
                </div>
                </div>

                <div class="row g-2 align-items-center mb-2">
                <label
                id="addressLabel"
                for="address"
                    class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                    >
                    Address
                    </label>
                    <div class="col-sm-8 col-xl-9">
                    <input
                    type="text"
                    name="address"
                    id="address"
                    class="form-control form-control-sm"
                    >
                    </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                    <label
                    id="address2Label"
                    for="address2"
                        class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                        >
                        Address 2
                        </label>
                        <div class="col-sm-8 col-xl-9">
                        <input
                        type="text"
                        name="address2"
                        id="address2"
                        class="form-control form-control-sm"
                        >
                        </div>
                        </div>

                        <div class="row g-2 align-items-center mb-2">
                        <label
                        id="cityLabel"
                        for="city"
                            class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                            >
                            City
                            </label>
                            <div class="col-sm-8 col-xl-9">
                            <input
                            type="text"
                            name="city"
                            id="city"
                            class="form-control form-control-sm"
                            >
                            </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                            <label
                            id="stateLabel"
                            for="state"
                                class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                                >
                                State
                                </label>
                                <div class="col-sm-8 col-xl-9">
                                <input
                                type="text"
                                name="state"
                                id="state"
                                class="form-control form-control-sm"
                                >
                                </div>
                                </div>

                                <div class="row g-2 align-items-center mb-2">
                                <label
                                id="zipLabel"
                                for="zip"
                                    class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                                    >
                                    Postal Code
                                    </label>
                                    <div class="col-sm-8 col-xl-9">
                                    <div class="input-group input-group-sm">
                                    <input
                                    type="text"
                                    name="zip"
                                    id="zip"
                                    class="form-control"
                                    >
                                    <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="CityState_populate('zip', 'ajaxIndicator');"
                                    >
                                    Lookup
                                    </button>
                                    </div>

                                    <img
                                    src="images/indicator2.gif"
                                    alt="AJAX"
                                    id="ajaxIndicator"
                                    class="mt-1"
                                    style="visibility: hidden;"
                                    >
                                    </div>
                                    </div>

                                    <div class="row g-2 align-items-center mb-2">
                                    <label
                                    id="countryLabel"
                                    for="country"
                                        class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                                        >
                                        Country
                                        </label>
                                        <div class="col-sm-8 col-xl-9">
                                        <?php echo TemplateUtility::getCountrySelectHTML(
                                            'country',
                                            '',
                                            true,
                                            'form-select form-select-sm',
                                            ''
                                        ); ?>
                                        </div>
                                        </div>

                                        <div class="row g-2 align-items-center mb-2">
                                        <label
                                        id="urlLabel"
                                        for="url"
                                            class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                                            >
                                            Web Site
                                            </label>
                                            <div class="col-sm-8 col-xl-9">
                                            <input
                                            type="text"
                                            name="url"
                                            id="url"
                                            class="form-control form-control-sm"
                                            >
                                            </div>
                                            </div>

                                            <div class="row g-2 align-items-center mb-2">
                                            <label
                                            id="departmentsLabel"
                                            for="departmentsSelect"
                                                class="col-sm-4 col-xl-3 col-form-label col-form-label-sm"
                                                >
                                                Departments
                                                </label>
                                                <div class="col-sm-8 col-xl-9">
                                                <select
                                                tabindex="3"
                                                id="departmentsSelect"
                                                name="departmentsSelect"
                                                class="form-select form-select-sm"
                                                onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentsSelect', 'departmentsCSV'); } this.value = 'num';"
                                                >
                                                <option value="edit">(Edit Departments)</option>
                                                <option value="num" selected>No Departments</option>
                                                <option value="nullline">-------------------------------</option>
                                                </select>

                                                <input
                                                type="hidden"
                                                id="departmentsCSV"
                                                name="departmentsCSV"
                                                value=""
                                                >
                                                </div>
                                                </div>

                                                <div class="row g-2 align-items-center">
                                                <div class="col-sm-4 col-xl-3">
                                                <span
                                                id="isHotLabel"
                                                class="form-label small mb-0"
                                                >
                                                Hot Company
                                                </span>
                                                </div>

                                                <div class="col-sm-8 col-xl-9">
                                                <div class="form-check mb-0">
                                                <input
                                                type="checkbox"
                                                id="isHot"
                                                name="isHot"
                                                class="form-check-input"
                                                >
                                                <label
                                                class="form-check-label"
                                                for="isHot"
                                                    >
                                                    Mark as hot
                                                    </label>
                                                    </div>
                                                    </div>
                                                    </div>
                                                    </div>

                                                    <div class="col-12 col-lg-5">
                                                    <div class="border rounded p-2 bg-body-tertiary h-100">
                                                    <label
                                                    for="addressBlock"
                                                        class="form-label small fw-semibold"
                                                        >
                                                        Paste Address
                                                        </label>

                                                        <?php
                                                        $freeformTop =
                                                        '<p class="freeformtop small text-body-secondary">' .
                                                        'Cut and paste freeform address here.</p>';
                                                    ?>
                                                    <?php eval(Hooks::get('CANDIDATE_TEMPLATE_ABOVE_FREEFORM')); ?>
                                                    <?php echo $freeformTop; ?>

                                                    <textarea
                                                    class="form-control form-control-sm"
                                                    tabindex="90"
                                                    name="addressBlock"
                                                    id="addressBlock"
                                                    rows="7"
                                                    ></textarea>

                                                    <div class="d-flex align-items-center gap-2 mt-2">
                                                    <button
                                                    id="arrowButton"
                                                    tabindex="91"
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    onclick="AddressParser_parse('addressBlock', 'company', 'addressParserIndicator', 'arrowButton'); document.addCompanyForm.name.focus();"
                                                    >
                                                    Parse Address
                                                    </button>

                                                    <img
                                                    src="images/indicator2.gif"
                                                    id="addressParserIndicator"
                                                    alt=""
                                                    width="16"
                                                    height="16"
                                                    style="visibility: hidden;"
                                                    >
                                                    </div>

                                                    <?php
                                                    $freeformBottom =
                                                    '<p class="freeformbottom small text-body-secondary mt-2 mb-0">' .
                                                    'Cut and paste freeform address here.</p>';
                                                    ?>
                                                    <?php eval(Hooks::get('CANDIDATE_TEMPLATE_BELOW_FREEFORM')); ?>
                                                    <?php echo $freeformBottom; ?>
                                                    </div>
                                                    </div>

                                                    </div>
                                                    </div>
                                                    </section>

                                                    <section class="card mb-2 oc-company-other">
                                                    <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">
                                                    Other
                                                    </div>

                                                    <div class="card-body p-2">
                                                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                                                    <div class="row g-2 align-items-center mb-2">
                                                    <div
                                                    class="col-sm-4 col-lg-3"
                                                    id="extraFieldTd<?php echo Template::escapeAttr($i); ?>"
                                                    >
                                                    <label
                                                    id="extraFieldLbl<?php echo Template::escapeAttr($i); ?>"
                                                    class="form-label small mb-0"
                                                    >
                                                    <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                                                    </label>
                                                    </div>

                                                    <div
                                                    class="col-sm-8 col-lg-9"
                                                    id="extraFieldData<?php echo Template::escapeAttr($i); ?>"
                                                    >
                                                    <?php echo $this->extraFieldRS[$i]['addHTML']; ?>
                                                    </div>
                                                    </div>
                                                    <?php endfor; ?>

                                                    <div class="row g-2 align-items-center mb-2">
                                                    <label
                                                    id="keyTechnologiesLabel"
                                                    for="keyTechnologies"
                                                        class="col-sm-4 col-lg-3 col-form-label col-form-label-sm"
                                                        >
                                                        Key Technologies
                                                        </label>

                                                        <div class="col-sm-8 col-lg-9">
                                                        <input
                                                        type="text"
                                                        name="keyTechnologies"
                                                        id="keyTechnologies"
                                                        class="form-control form-control-sm"
                                                        >
                                                        </div>
                                                        </div>

                                                        <div class="row g-2 mb-0">
                                                        <label
                                                        id="notesLabel"
                                                        for="notes"
                                                            class="col-sm-4 col-lg-3 col-form-label col-form-label-sm"
                                                            >
                                                            Misc. Notes
                                                            </label>

                                                            <div class="col-sm-8 col-lg-9">
                                                            <textarea
                                                            class="form-control form-control-sm"
                                                            name="notes"
                                                            id="notes"
                                                            rows="5"
                                                            ></textarea>
                                                            </div>
                                                            </div>
                                                            </div>
                                                            </section>

                                                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                            Add Company
                                                            </button>

                                                            <button type="reset" class="btn btn-sm btn-outline-secondary">
                                                            Reset
                                                            </button>

                                                            <button
                                                            type="button"
                                                            class="btn btn-sm btn-secondary"
                                                            onclick="goToURL('<?php echo Template::escapeJsAttr(
                                                                CATSUtility::getIndexName() . '?m=companies&a=show'
                                                            ); ?>');"
                                                            >
                                                            Back to Companies
                                                            </button>
                                                            </div>
                                                            </form>

                                                            <script>
                                                            document.addCompanyForm.name.focus();
                                                            </script>

                                                            </div>
                                                            </main>

                                                            <?php TemplateUtility::printFooter(); ?>
