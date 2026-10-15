<div class="mt-3">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
                <span class="h6 d-block">
                <span class="text-success"><?php echo count($this->importedCandidates); ?></span> Candidate<?php echo count($this->importedCandidates) != 1 ? 's' : ''; ?> Imported
                </span>
                <div class="mb-2"></div>
                A candidate has all applicable information (such as name, address, skill set) on file
                and can be added to job orders and included in reports and actions. The uploaded resume
                documents are attached to the candidate record for later viewing and searches.
                <div class="mb-2"></div>
                <b>Imported Candidates:</b>
                <ul class="list-group mb-3">
                    <?php $col = false; for ($i=0; $i<count($this->importedCandidates) && $i<=10; $i++): $candidate = $this->importedCandidates[$i]; ?>
                    <li class="list-group-item text-break"><a href="<?php echo $candidate['url']; ?>"><?php echo $candidate['name']; ?></a> -
                            <?php echo $candidate['location']; ?>
                        </li>
                    <?php if ($i == 10 && count($this->importedCandidates) > 10): ?>
                    <li class="list-group-item text-break">
                            ... <span class="text-body-secondary fst-italic"><?php echo number_format(count($this->importedCandidates)-10,0); ?> candidates not shown</span>
                        </li>
                    <?php endif; ?>
                    <?php endfor; ?>
                </ul>
                <?php if (count($this->importedDuplicates)): ?>
                <br />
                <span class="alert alert-warning d-block">
                <?php echo number_format(count($this->importedDuplicates), 0); ?> candidates were duplicates and not added.
                </span>
                <?php endif; ?>
            </div>
            <div class="col-12 col-lg-6">
                <span class="h6 d-block">
                <span class="text-primary"><?php echo count($this->importedDocuments); ?></span> Resume Document<?php echo count($this->importedDocuments) != 1 ? 's' : ''; ?> Saved
                </span>
                <div class="mb-2"></div>
                A resume document is a file that cannot be converted into a candidate because it's missing key
                information (like the candidate's name). These files have been saved and are full-text searchable. They
                must be converted into candidates manually.
                <div class="mb-2"></div>
                <b>Resume Documents:</b>
                <ul class="list-group mb-3">
                    <?php $col = false; for ($i=0; $i<count($this->importedDocuments) && $i<=10; $i++): $document = $this->importedDocuments[$i]; ?>
                    <li class="list-group-item text-break">
                            <?php echo $document['name']; ?>
                        </li>
                    <?php if ($i == 10 && count($this->importedDocuments) > 10): ?>
                    <li class="list-group-item text-break">
                            ... <span class="text-body-secondary fst-italic"><?php echo number_format(count($this->importedDocuments)-10,0); ?> documents not shown</span>
                        </li>
                    <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </div>
            <div class="col-12">
                <br /><br />
                <span class="h6 d-block">
                <span class="text-danger"><?php echo count($this->importedFailed); ?></span> Document<?php echo count($this->importedFailed) != 1 ? 's' : ''; ?> Failed to be Imported
                </span>
                <div class="mb-2"></div>
                A document fails to import because it's either corrupt or CATS doesn't know how to open it. You
                could try to convert these files to CATS-friendly formats like Microsoft Word, Adobe PDF or as
                plain text files using the appropriate application.
                <div class="mb-2"></div>
                <b>Failed Documents:</b>
                <ul class="list-group mb-3">
                    <?php $col = false; for ($i=0; $i<count($this->importedFailed); $i++): $failed = $this->importedFailed[$i]; ?>
                    <li class="list-group-item text-break">
                            <?php echo $failed['name']; ?>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
    </div>

</div>
