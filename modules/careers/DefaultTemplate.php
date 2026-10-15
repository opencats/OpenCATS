<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

// Canonical Bootstrap portal seed for installation and migration 394.
// Stored in the existing template table; administrator overrides stay in the site table.
return array(
    'Left' => <<<'HTML'

HTML,
    'Header' => <<<'HTML'
<div id="container" class="container py-4">
<header class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom pb-3 mb-4">
<div class="fs-4 fw-semibold"><siteName> <span class="text-body-secondary">Careers</span></div>
<nav aria-label="Career portal" class="d-flex flex-wrap gap-3">
<a-LinkMain>Return to Main</a>
<a-ListAll>Show All Jobs</a>
<a href="<rssURL>" id="rssFeed">RSS Feed</a>
</nav>
</header>
<main>
HTML,
    'Footer' => <<<'HTML'
</main>
</div>
HTML,
    'CSS' => <<<'HTML'
/* OpenCATS Bootstrap 5.3 */
#container { overflow-wrap: anywhere; }
#descriptive img, #descriptive iframe { max-width: 100%; }
#descriptive { overflow-x: auto; }
#container .inputBoxArea { min-height: 8rem; }
#container .form-control, #container .form-select { max-width: 100%; }

HTML,
    'Content - Main' => <<<'HTML'
<section id="careerContent" class="card card-body p-4">
<registeredCandidate>
<h1 class="h3">Available Openings at <siteName></h1>
<div id="descriptive">
<p>Change your life today by becoming an integral part of our winning team.</p>
<p>If you are interested, we invite you to view the <a-ListAll>current opening positions</a> at our company.</p>
<registeredLoginTitle><h2 class="h4 mt-4">Have you applied with us before?</h2></registeredLoginTitle>
<registeredLogin>
</div>
</section>
HTML,
    'Content - Search Results' => <<<'HTML'
<section id="careerContent" class="card card-body">
<registeredCandidate>
<h1 class="h3 mb-3">Current Available Openings, Recently Posted Jobs: <numberOfSearchResults></h1>
<div class="table-responsive">
<searchResultsTable>
</div>
</section>
HTML,
    'Content - Job Details' => <<<'HTML'
<article id="careerContent" class="card card-body p-4">
<registeredCandidate>
<h1 class="h3 mb-4">Position Details: <title></h1>
<dl id="detailsTable" class="row">
<dt class="col-sm-3">Location:</dt><dd class="col-sm-9"><location></dd>
<dt class="col-sm-3">Openings:</dt><dd class="col-sm-9"><openings></dd>
<dt class="col-sm-3">Salary Range:</dt><dd class="col-sm-9"><salary></dd>
</dl>
<div id="descriptive" class="border-top pt-3 mb-4"><h2 class="h5">Description:</h2><description></div>
<div id="detailsTools"><a-applyToJob id="applyToPosition" class="btn btn-primary">Apply to Position</a></div>
</article>
HTML,
    'Content - Apply for Position' => <<<'HTML'
<section id="careerContent">
<h1 class="h3 mb-4">Applying to: <title></h1>
<div class="row g-4">
<div class="col-lg-6"><div class="card card-body h-100">
<h2 class="h5">1. Import Resume (or CV) and Populate Fields</h2>
<div class="mb-4"><input-resumeUploadPreview></div>
<h2 class="h5">2. Tell us about yourself</h2>
<p class="text-body-secondary">All fields marked with asterisk (*) are required.</p>
<div class="mb-3"><label class="form-label" id="firstNameLabel" for="firstName">*First Name:</label>
<input-firstName></div>
<div class="mb-3"><label class="form-label" id="lastNameLabel" for="lastName">*Last Name:</label>
<input-lastName></div>
<div class="mb-3"><label class="form-label" id="emailLabel" for="email">*Email Address:</label>
<input-email></div>
<div class="mb-3"><label class="form-label" id="emailConfirmLabel" for="emailconfirm">*Confirm Email:</label>
<input-emailconfirm></div>
</div></div>
<div class="col-lg-6"><div class="card card-body h-100">
<h2 class="h5">3. How may we contact you?</h2>
<div class="mb-3"><label class="form-label" id="homePhoneLabel" for="phoneHome">Home Phone:</label>
<input-phone-home></div>
<div class="mb-3"><label class="form-label" id="mobilePhoneLabel" for="phoneCell">Mobile Phone:</label>
<input-phone-cell></div>
<div class="mb-3"><label class="form-label" id="workPhoneLabel" for="phone">Work Phone:</label>
<input-phone></div>
<div class="mb-3"><label class="form-label" id="bestTimeLabel" for="bestTimeToCall">Best time to call:</label>
<input-best-time-to-call></div>
<div class="mb-3"><label class="form-label" id="mailingAddressLabel" for="address">Mailing Address:</label>
<input-address></div>
<div class="mb-3"><label class="form-label" id="cityProvinceLabel" for="city">City/Province:</label>
<input-city></div>
<div class="mb-3"><label class="form-label" id="stateCountryLabel" for="state">State:</label>
<input-state></div>
<div class="mb-3"><label class="form-label" id="countryLabel" for="country">Country:</label>
<input-country></div>
<div class="mb-3"><label class="form-label" id="zipPostalLabel" for="zip">Zip/Postal Code:</label>
<input-zip></div>
<h2 class="h5">4. Additional Information</h2>
<div class="mb-3"><label class="form-label" id="keySkillsLabel" for="keySkills">Key Skills:</label>
<input-keySkills></div><div class="mb-3"><label class="form-label" id="captchaLabel" for="captcha">*Captcha:</label>
<input-captcha req></div>
<div><button type="button" class="btn btn-primary" id="submitApplicationNow" onclick="if (applyValidate()) { document.applyToJobForm.submit(); }">Submit Application Now</button></div>
</div></div>
</div>
</section>
HTML,
    'Content - Questionnaire' => <<<'HTML'
<questionnaire>
<div class="mt-4"><submit value="Continue"></div>
HTML,
    'Content - Thanks for your Submission' => <<<'HTML'
<section id="careerContent" class="card card-body p-4">
<h1 class="h3">Application Submitted For: <title></h1>
<div id="descriptive" class="alert alert-success mt-3">
<p>Please check your email inbox &#8212; You should receive an email confirmation of your application.</p>
<p class="mb-0">Thank you for submitting your application to us. We will review it shortly and make contact with you soon.</p>
</div>
<div><a-jobDetails>Return to position details</a></div>
</section>
HTML,
    'Content - Candidate Registration' => <<<'HTML'
<section class="mt-3">
<applyContent><h1 class="h3">Applying to <title></h1></applyContent>
<div class="mb-3"><label class="form-label" id="emailLabel" for="email">Enter your e-mail address:</label>
<input-email></div>
<applyContent><div class="form-check mb-3"><input-new><label class="form-check-label" for="isNewYes">I have not registered on this website.</label></div></applyContent>
<div class="form-check mb-3"><input-registered><label class="form-check-label" for="isNewNo">I have registered before</label></div>
<div class="mb-3"><label class="form-label" id="lastNameLabel" for="lastName">Last name:</label>
<input-lastName></div><div class="mb-3"><label class="form-label" id="zipLabel" for="zip">Zip code:</label>
<input-zip></div>
<div class="form-check mb-3"><input-rememberMe><label class="form-check-label" for="rememberMe">Remember my information for future visits</label></div>
<input-submit>
</section>
HTML,
    'Content - Candidate Profile' => <<<'HTML'
<section id="careerContent">
<h1 class="h3">My Profile</h1>
<p>Any changes you make to your profile will be updated on our website for all past and future jobs you apply for.</p>
<div class="row g-4"><div class="col-lg-6"><div class="card card-body h-100">
<h2 class="h5">1. Tell us about yourself</h2>
<p>All fields marked with asterisk (*) are required.</p>
<div class="mb-3"><label class="form-label" id="firstNameLabel" for="firstName">*First Name:</label>
<input-firstName></div>
<div class="mb-3"><label class="form-label" id="lastNameLabel" for="lastName">*Last Name:</label>
<input-lastName></div>
<div class="mb-3"><label class="form-label" id="emailLabel" for="email1">*Email Address:</label>
<input-email1></div>
<input-resume>
</div></div><div class="col-lg-6"><div class="card card-body h-100">
<h2 class="h5">2. How may we contact you?</h2>
<div class="mb-3"><label class="form-label" id="homePhoneLabel" for="phoneHome">Home Phone:</label>
<input-phoneHome></div>
<div class="mb-3"><label class="form-label" id="mobilePhoneLabel" for="phoneCell">Mobile Phone:</label>
<input-phoneCell></div>
<div class="mb-3"><label class="form-label" id="workPhoneLabel" for="phoneWork">Work Phone:</label>
<input-phoneWork></div>
<div class="mb-3"><label class="form-label" id="bestTimeLabel" for="bestTimeToCall">Best time to call:</label>
<input-bestTimeToCall></div>
<div class="mb-3"><label class="form-label" id="mailingAddressLabel" for="address">Mailing Address:</label>
<input-address></div>
<div class="mb-3"><label class="form-label" id="cityProvinceLabel" for="city">City/Province:</label>
<input-city></div>
<div class="mb-3"><label class="form-label" id="stateCountryLabel" for="state">State:</label>
<input-state></div>
<div class="mb-3"><label class="form-label" id="countryLabel" for="country">Country:</label>
<input-country></div>
<div class="mb-3"><label class="form-label" id="zipPostalLabel" for="zip">Zip/Postal Code:</label>
<input-zip></div>
<h2 class="h5">3. Additional Information</h2>
<div class="mb-3"><label class="form-label" id="keySkillsLabel" for="keySkills">Key Skills:</label>
<input-keySkills></div>
<div><input-submit></div>
</div></div></div></section>
HTML
);
