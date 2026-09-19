<?php TemplateUtility::printModalHeader('Candidates', array('js/lists.js'), 'Add to '.$this->dataItemDesc.' Static Lists'); ?>
<main class="container-fluid p-2">
    <p class="small">Select the lists you want to add the item<?php if (count($this->dataItemIDArray) > 1): ?>s<?php endif; ?> to.</p>
    <div id="addToListBox" class="mb-3">
        <input type="hidden" id="dataItemArray" value="<?php $this->_(implode(',', $this->dataItemIDArray)); ?>">
        <?php foreach ($this->savedListsRS as $index => $data): ?>
        <div class="card card-body p-2 mb-2" id="savedListRow<?php echo($data['savedListID']); ?>">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="form-check mb-0 text-break">
                    <input type="checkbox" class="form-check-input" id="savedListRowCheck<?php echo($data['savedListID']); ?>">
                    <label class="form-check-label" for="savedListRowCheck<?php echo($data['savedListID']); ?>">
                        <span id="savedListRowDescriptionArea<?php echo($data['savedListID']); ?>"><?php $this->_($data['description']); ?></span>
                        <span class="text-body-secondary">(<?php echo($data['numberEntries']); ?>)</span>
                    </label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="editListRow(<?php echo($data['savedListID']); ?>);">Edit</button>
            </div>
        </div>
        <div class="card card-body p-2 mb-2" style="display:none;" id="savedListRowEditing<?php echo($data['savedListID']); ?>">
            <label class="form-label small mb-1" for="savedListRowInput<?php echo($data['savedListID']); ?>">List name</label>
            <input type="text" class="form-control form-control-sm mb-2" value="<?php $this->_($data['description']); ?>" id="savedListRowInput<?php echo($data['savedListID']); ?>">
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteListRow(<?php echo($data['savedListID']); ?>, '<?php echo($this->sessionCookie); ?>', <?php echo($data['numberEntries']); ?>);">Delete</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveListRow(<?php echo($data['savedListID']); ?>, '<?php echo($this->sessionCookie); ?>');">Save</button>
            </div>
        </div>
        <div class="alert alert-info py-2" style="display:none;" role="status" id="savedListRowAjaxing<?php echo($data['savedListID']); ?>">
            <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Saving Changes, Please Wait...
        </div>
        <?php endforeach; ?>
        <div class="card card-body p-2 mb-2" style="display:none;" id="savedListNew">
            <label class="form-label small mb-1" for="savedListNewInput">New list name</label>
            <input type="text" class="form-control form-control-sm mb-2" value="" id="savedListNewInput">
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('savedListNew').style.display='none';">Delete</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="commitNewList('<?php echo($this->sessionCookie); ?>', <?php echo($this->dataItemType); ?>);">Save</button>
            </div>
        </div>
        <div class="alert alert-info py-2" style="display:none;" role="status" id="savedListNewAjaxing">
            <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Saving Changes...
        </div>
    </div>
    <!-- Keep display utilities inside the wrappers toggled by lists.js. -->
    <div id="actionArea">
        <div class="d-flex flex-wrap justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addListRow();">New List</button>
            <button type="button" class="btn btn-sm btn-primary" onclick="addItemsToList('<?php echo($this->sessionCookie); ?>', <?php echo($this->dataItemType); ?>);">Add To Lists</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="parentHidePopWin();">Cancel</button>
        </div>
    </div>
    <div class="alert alert-info py-2" style="display:none;" role="status" id="addingToListAjaxing">
        <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Adding to Lists, Please Wait <?php if (count($this->dataItemIDArray) > 20): ?>(This could take awhile)<?php endif; ?>...
    </div>
    <div class="alert alert-success py-2" style="display:none;" role="status" id="addingToListAjaxingComplete">
        Items have been added to lists successfully.
    </div>
    <script type="text/javascript">
        function getCheckedBoxes()
        {
            var checked='';
            <?php foreach ($this->savedListsRS as $index => $data): ?>
            if (document.getElementById("savedListRowCheck<?php echo($data['savedListID']); ?>").checked)
            {
                checked += "<?php echo($data['savedListID']); ?>,";
            }
            <?php endforeach; ?>
            return checked;
        }
    </script>
</main>
</body>
</html>
