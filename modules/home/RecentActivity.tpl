<?php if ($this->homeActivity === null): ?>
<p class="text-body-secondary mb-0">Recent Activity unavailable.</p>
<?php else: ?>
<?php if (!$this->homeActivity): ?><p class="text-body-secondary mb-1">No recorded Activity for this source.</p><?php endif; ?>
<ol class="list-unstyled mb-1 home-activity-entries">
<?php foreach ($this->homeActivity as $entry): ?>
<li class="border-top py-2">
    <p class="mb-0"><strong><?php echo Template::escapeHtml($entry['typeDescription']); ?></strong> <span class="text-body-secondary"><?php echo Template::escapeHtml($entry['dateCreated']); ?></span></p>
    <p class="mb-0"><?php echo Template::escapeHtml(Tasks::getParentTypes()[$entry['dataItemType']]); ?>: <a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL($entry['dataItemType'], $entry['dataItemID'])); ?>"><?php echo Template::escapeHtml($entry['subjectName'] !== '' ? $entry['subjectName'] : '#' . (int) $entry['dataItemID']); ?></a></p>
    <p class="mb-0 home-preview"><?php echo Template::escapeHtml($entry['notes']); ?></p>
    <p class="text-body-secondary mb-0">Recorded by <?php echo Template::escapeHtml(trim($entry['enteredByFirstName'] . ' ' . $entry['enteredByLastName'])); ?></p>
</li>
<?php endforeach; ?>
</ol>
<a href="<?php echo Template::escapeAttr($this->homeActivityURL); ?>">View record &amp; full Activity</a>
<?php endif; ?>
