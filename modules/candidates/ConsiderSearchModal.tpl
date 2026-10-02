<?php TemplateUtility::printModalHeader('Candidates', array(), 'Add Candidates to Job Order'); ?>
<main class="container-fluid p-2 oc-candidate-considersearchmodal">

    <?php if (!$this->isFinishedMode): ?>
        <p>Search for a job order below, and then click on the job title to add
        the candidate to the selected job order.</p>

        <div class="card card-body p-2 mb-2">
            <form id="searchByJobTitleForm" name="searchByJobTitleForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch" method="post">
                <input type="hidden" name="postback" id="postback" value="postback">
                <input type="hidden" id="mode_jobtitle" name="mode" value="searchByJobTitle">
                <input type="hidden" id="candidateID_jobtitle" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>">

                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm"><label for="wildCardString_jobTitle">Search by Job Title:</label></div>
                    <div class="col-12 col-sm"><div class="d-flex align-items-center gap-1"><input type="text" class="form-control form-control-sm" id="wildCardString_jobTitle" name="wildCardString"><span class="text-danger" title="Required">*</span></div></div>
                </div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm"><button type="submit" class="btn btn-sm btn-primary" id="searchByJobTitle" name="searchByJobTitle" value="Search by Job Title">Search by Job Title</button></div>
                </div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">&nbsp;</div>
                </div>
            </form>

            <form id="searchByCompanyNameForm" name="searchByCompanyNameForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch" method="post">
                <input type="hidden" name="postback" id="postback" value="postback">
                <input type="hidden" id="mode_companyname" name="mode" value="searchByCompanyName">
                <input type="hidden" id="candidateID_companyname" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>">

                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm"><label for="wildCardString_companyname">Search by Company Name:</label></div>
                    <div class="col-12 col-sm"><div class="d-flex align-items-center gap-1"><input type="text" class="form-control form-control-sm" id="wildCardString_companyname" name="wildCardString"><span class="text-danger" title="Required">*</span></div></div>
                </div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm"><button type="submit" class="btn btn-sm btn-primary" id="searchByCompanyName" name="searchByCompanyName" value="Search by Company Name">Search by Company Name</button></div>
                </div>
            </form>
        </div>

        <?php if (empty($_POST['mode']) || $_POST['mode'] == 'searchByJobTitle'): ?>
            <script>
                document.searchByJobTitleForm.wildCardString.focus();
            </script>
        <?php else: ?>
            <script>
                document.searchByCompanyNameForm.wildCardString.focus();
            </script>
        <?php endif; ?>

        <?php if ($this->isResultsMode): ?>
            <br>
            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Search Results</h2>

            <?php if (!empty($this->rs)): ?>
                <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                    <thead><tr>
                        <th scope="col">Ref. #</th>
                        <th scope="col">Title</th>
                        <th scope="col">Company</th>
                        <th scope="col">Type</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col">Start</th>
                        <th scope="col">Recruiter</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Action</th>
                    </tr></thead><tbody>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td><?php $this->_($data['jobID']); ?></td>
                            <td>
                                <?php if (!$data['inPipeline']): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addToPipeline&amp;getback=getback" style="display:inline;">
                                        <input type="hidden" name="postback" value="postback">
                                        <input type="hidden" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>">
                                        <input type="hidden" name="jobOrderID" value="<?php $this->_($data['jobOrderID']); ?>">
                                        <button type="submit" class="<?php $this->_($data['linkClass']); ?> linkButton">
                                            <?php $this->_($data['title']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['title']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php $this->_($data['companyName']); ?></td>
                            <td><?php $this->_($data['type']); ?></td>
                            <td><?php $this->_($data['status']); ?></td>
                            <td><?php $this->_($data['dateCreated']); ?></td>
                            <td><?php $this->_($data['startDate']); ?></td>
                            <td><?php $this->_($data['recruiterAbbrName']); ?></td>
                            <td><?php $this->_($data['ownerAbbrName']); ?></td>
                            <td>
                                <a href="#" title="Show Job Order" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;display=popup&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>', 'viewJobOrderDetails', 1000, 675, true); return false;">
                                    <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" class="absmiddle">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php else: ?>
                <p>No matching entries found.</p>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="showRecentJobOrders" name="showRecentJobOrders" value="Show Recently Modified Job Orders" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch&amp;candidateIDArrayStored=<?php echo($this->candidateIDArrayStored); ?>';">Show Recently Modified Job Orders</button>
        <?php else: ?>
            <br>
            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Recently Modified Job Orders</h2>

            <?php if (!empty($this->rs)): ?>
                <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                    <thead><tr>
                        <th scope="col">Ref. #</th>
                        <th scope="col">Title</th>
                        <th scope="col">Company</th>
                        <th scope="col">Type</th>
                        <th scope="col">Status</th>
                        <th scope="col">Modified</th>
                        <th scope="col">Start</th>
                        <th scope="col">Recruiter</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Action</th>
                    </tr></thead><tbody>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td><?php $this->_($data['jobID']); ?></td>
                            <td>
                                <?php if (!$data['inPipeline']): ?>
                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addToPipeline&amp;getback=getback" style="display:inline;">
                                        <input type="hidden" name="postback" value="postback">
                                        <input type="hidden" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>">
                                        <input type="hidden" name="jobOrderID" value="<?php $this->_($data['jobOrderID']); ?>">
                                        <button type="submit" class="<?php $this->_($data['linkClass']); ?> linkButton">
                                            <?php $this->_($data['title']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['title']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php $this->_($data['companyName']); ?></td>
                            <td><?php $this->_($data['type']); ?></td>
                            <td><?php $this->_($data['status']); ?></td>
                            <td><?php $this->_($data['dateModified']); ?></td>
                            <td><?php $this->_($data['startDate']); ?></td>
                            <td><?php $this->_($data['recruiterAbbrName']); ?></td>
                            <td><?php $this->_($data['ownerAbbrName']); ?></td>
                            <td>
                                <a href="#" title="Show Job Order" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;display=popup&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>', 'viewJobOrderDetails', 1000, 675, true); return false;">
                                    <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" class="absmiddle">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php else: ?>
                <p>No recent job orders found.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>The <?php if(count($this->candidateIDArray)>1): ?> <?php echo(count($this->candidateIDArray)); ?> candidates have<?php else: ?>candidate has<?php endif; ?> been successfully added to the pipeline for the selected job order.</p>

        <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
            <button type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" class="btn btn-sm btn-outline-secondary">Close</button>
        </form>
    <?php endif; ?>

</main>
    </body>
</html>
