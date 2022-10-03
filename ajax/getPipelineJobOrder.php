<?php
/*
 * CATS
 * AJAX Job Order Pipeline HTML Interface
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: getPipelineJobOrder.php 3814 2007-12-06 17:54:28Z brian $
 */

include_once(LEGACY_ROOT . '/lib/Pipelines.php');
include_once(LEGACY_ROOT . '/lib/TemplateUtility.php');
include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(LEGACY_ROOT . '/lib/Hooks.php');
include_once(LEGACY_ROOT . '/lib/JobOrders.php');

$interface = new SecureAJAXInterface();

if (!isset($_REQUEST['joborderID']) ||
    !isset($_REQUEST['page']) ||
    !isset($_REQUEST['entriesPerPage']) ||
    !isset($_REQUEST['sortBy']) ||
    !isset($_REQUEST['sortDirection']))
{
    $interface->outputXMLErrorPage(-1, 'Invalid input.');
    die();
}

$siteID = $interface->getSiteID();

$jobOrderID     = trim(htmlspecialchars($_REQUEST['joborderID']));
$page           = trim(htmlspecialchars($_REQUEST['page']));
$entriesPerPage = trim(htmlspecialchars($_REQUEST['entriesPerPage']));
$sortBy         = trim(htmlspecialchars($_REQUEST['sortBy']));
$sortDirection  = trim(htmlspecialchars($_REQUEST['sortDirection']));
$indexFile      = trim(htmlspecialchars($_REQUEST['indexFile']));
$isPopup        = $_REQUEST['isPopup'] == 1 ? true : false;

$_SESSION['CATS']->setPipelineEntriesPerPage($entriesPerPage);

$jobOrders = new JobOrders($siteID);
$jobOrdersData = $jobOrders->get($jobOrderID);

/* Get an array of the pipeline data. */
$pipelines = new Pipelines($siteID);
$pipelinesRS = $pipelines->getJobOrderPipeline($jobOrderID);

/* Format pipeline data. */
foreach ($pipelinesRS as $rowIndex => $row)
{
    if ($row['submitted'] == '1')
    {
        $pipelinesRS[$rowIndex]['highlightStyle'] = 'jobLinkSubmitted';
    }
    else if($row['isHotCandidate'] == '1')
    {
        $pipelinesRS[$rowIndex]['highlightStyle'] = 'jobLinkHot';
    }
    else
    {
        $pipelinesRS[$rowIndex]['highlightStyle'] = 'jobLinkCold';
    }

    $pipelinesRS[$rowIndex]['addedByAbbrName'] = StringUtility::makeInitialName(
        $pipelinesRS[$rowIndex]['addedByFirstName'],
        $pipelinesRS[$rowIndex]['addedByLastName'],
        LAST_NAME_MAXLEN
    );

    if ($row['attachmentPresent'] == 1)
    {
        $pipelinesRS[$rowIndex]['iconTag'] = '<img src="images/paperclip.gif" alt="" width="16" height="16" />';
    }
    else
    {
        $pipelinesRS[$rowIndex]['iconTag'] = '<img src="images/mru/blank.gif" alt="" width="16" height="16" />';
    }

    if ($row['isDuplicateCandidate'] == 1)
    {
        $pipelinesRS[$rowIndex]['iconTag'] .= '<img src="images/wf_error.gif" alt="" width="16" height="16" title="Duplicate Candidate"/>';
    }

    if($pipelinesRS[$rowIndex]['iconTag'] == '')
    {
        $pipelinesRS[$rowIndex]['iconTag'] .= '&nbsp;';
    }

    $pipelinesRS[$rowIndex]['ratingLine'] = TemplateUtility::getRatingObject(
        $pipelinesRS[$rowIndex]['ratingValue'],
        $pipelinesRS[$rowIndex]['candidateJobOrderID'],
        $_SESSION['CATS']->getCookie()
    );
}

/* Sort the data. */
if ($sortBy !== '' && $sortBy !== 'undefined')
{
    $sorting = array();
    foreach ($pipelinesRS as $p)
    {
        $sorting[] = $p[$sortBy];
    }
    if ($sortBy == 'ratingValue')
    {
        array_multisort($sorting, $sortDirection == 'desc' ? SORT_DESC : SORT_ASC , SORT_NUMERIC, $pipelinesRS);
    }
    else
    {
        array_multisort($sorting, $sortDirection == 'desc' ? SORT_DESC : SORT_ASC , SORT_STRING, $pipelinesRS);
    }
}

$minEntry = $entriesPerPage * $page;
$maxEntry = $minEntry + $entriesPerPage;

if ($maxEntry > count($pipelinesRS))
{
    $maxEntry = count($pipelinesRS);
}


function printSortLink($field, $delimiter = "'", $changeDirection = true)
{
    global $sortBy, $sortDirection;

    echo $delimiter, $field, $delimiter, ', ';

    if ($changeDirection)
    {
        if ($sortBy == $field)
        {
            if ($sortDirection == 'desc' || $sortDirection == '')
            {
                echo $delimiter, 'asc', $delimiter;
            }
            else
            {
                echo $delimiter, 'desc', $delimiter;
            }
        }
        else
        {
            echo $delimiter, 'asc', $delimiter;
        }
    }
    else
    {
        if ($sortDirection == 'desc' || $sortDirection == '')
        {
            echo $delimiter, 'desc', $delimiter;
        }
        else
        {
            echo $delimiter, 'asc', $delimiter;
        }
    }
}

if (!eval(Hooks::get('JO_AJAX_GET_PIPELINE'))) return;

?>

<?php echo(TemplateUtility::getRatingsArrayJS()); ?>

<script type="text/javascript">
    PipelineJobOrder_setLimitDefaultVars('<?php echo($sortBy); ?>', '<?php echo($sortDirection); ?>');
    var s = '';
    s += 'Showing entries <?php echo($minEntry + 1); ?> through <?php echo($maxEntry); ?> of <?php echo(count($pipelinesRS)) ?>: ';
    <?php if ($minEntry != 0): ?>
        s += '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page - 1); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink($sortBy, "\'", false); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, \'ajaxPipelineTable\', \'<?php echo($_SESSION['CATS']->getCookie()); ?>\', \'ajaxPipelineTableIndicator\', \'<?php echo($indexFile); ?>\');">&lt; Previous Page</a>';
    <?php endif; ?>
    <?php if ($maxEntry < count($pipelinesRS)): ?>
        s += '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page + 1); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink($sortBy, "\'", false); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, \'ajaxPipelineTable\', \'<?php echo($_SESSION['CATS']->getCookie()); ?>\', \'ajaxPipelineTableIndicator\', \'<?php echo($indexFile); ?>\');">Next Page &gt;</a>';
    <?php endif; ?>
	<?php if (count($pipelinesRS) <= 15): ?>
        document.getElementById('ajaxPipelineControl').style.display='none';
	<?php endif; ?>

    document.getElementById('ajaxPipelineNavigation').innerHTML = s;
</script>
    <table class="notsortable" id="pipelineTable" width="100%">
    <tr>
        <th></th>
        <th></th>
        <th align="left" width="32" nowrap="nowrap"></th>
        <th align="left" width="62" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('ratingValue'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Match
            </a>
        </th>
        <th align="left" width="80" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('firstName'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                First Name
            </a>
        </th>
        <th align="left" width="100" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('lastName'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Last Name
            </a>
        </th>
        <th align="left" width="40" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('state'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Loc
            </a>
        </th>
        <th align="left" width="60" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('dateCreatedInt'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Added
            </a>
        </th>
        <th align="left" width="70" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('addedByAbbrName'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Entered By
            </a>
        </th>
        <th align="left" width="65" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('status'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Status
            </a>
        </th>
        <th align="left" nowrap="nowrap">
            <a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page); ?>, <?php echo($entriesPerPage); ?>, <?php printSortLink('lastActivity'); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($_SESSION['CATS']->getCookie()); ?>', 'ajaxPipelineTableIndicator', '<?php echo($indexFile); ?>');">
                Last Activity
            </a>
        </th>
<?php if (!$isPopup): ?>
        <th align="center">Action</th>
<?php endif; ?>
    </tr>

    <?php for ($i = $minEntry; $i < $maxEntry; $i++): ?>
        <?php $pipelinesData = $pipelinesRS[$i]; $rowNumber = $i - $minEntry; ?>
        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineRow<?php echo($rowNumber); ?>">
        	<td>
        		<input type="checkbox" id="checked_<?php echo($pipelinesData['candidateID']); ?>" name="checked" value="<?php echo($pipelinesData['candidateID']); ?>"/>
        	</td>
            <td valign="top">
                <span id="pipelineEntryOpen<?php echo($rowNumber); ?>">
                    <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineEntryClose<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineEntryOpen<?php echo($rowNumber); ?>').style.display = 'none'; PipelineDetails_populate(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 'pipelineEntryInner<?php echo($rowNumber); ?>', '<?php echo($_SESSION['CATS']->getCookie()); ?>');">
                        <img src="images/arrow_next.png" alt="" border="0" title="Show History" />
                    </a>
                </span>
                <span id="pipelineEntryClose<?php echo($rowNumber); ?>" style="display: none;">
                    <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineEntryClose<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineEntryOpen<?php echo($rowNumber); ?>').style.display = '';">
                        <img src="images/arrow_down.png" alt="" border="0" title="Hide History"/>
                    </a>
                </span>
            </td>
            <td valign="top">
                <?php echo($pipelinesData['iconTag']); ?>
            </td>
            <td valign="top">
                <?php echo($pipelinesData['ratingLine']); ?>
            </td>
            <td valign="top">
                <a href="<?php echo($indexFile); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($pipelinesData['candidateID']); ?>" class="<?php echo($pipelinesData['highlightStyle']); ?>">
                    <?php echo(htmlspecialchars($pipelinesData['firstName'])); ?>
                </a>
            </td>
            <td valign="top">
                <a href="<?php echo($indexFile); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($pipelinesData['candidateID']); ?>" class="<?php echo($pipelinesData['highlightStyle']); ?>">
                    <?php echo(htmlspecialchars($pipelinesData['lastName'])); ?>
                </a>
            </td>
            <td valign="top" nowrap="nowrap"><?php echo(htmlspecialchars($pipelinesData['state'])); ?></td>
            <td valign="top" nowrap="nowrap"><?php echo(htmlspecialchars($pipelinesData['dateCreated'])); ?></td>
            <td valign="top" nowrap="nowrap"><?php echo(htmlspecialchars($pipelinesData['addedByAbbrName'])); ?></td>
            <td valign="top" nowrap="nowrap"><?php echo(htmlspecialchars($pipelinesData['status'])); ?></td>
            <td valign="top"><?php echo($pipelinesData['lastActivity']); ?></td>
<?php if (!$isPopup): ?>
            <td align="center" nowrap="nowrap">
                <?php if ($_SESSION['CATS']->getAccessLevel('pipelines.screening') >= ACCESS_LEVEL_EDIT && !$_SESSION['CATS']->hasUserCategory('sourcer')): ?>
                    <?php if ($pipelinesData['ratingValue'] < 0): ?>
                        <a href="#" id="screenLink<?php echo($pipelinesData['candidateJobOrderID']); ?>" onclick="moImageValue<?php echo($pipelinesData['candidateJobOrderID']); ?> = 0; setRating(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 0, 'moImage<?php echo($pipelinesData['candidateJobOrderID']); ?>', '<?php echo($_SESSION['CATS']->getCookie()); ?> '); return false;" >
                            <img id="screenImage<?php echo($pipelinesData['candidateJobOrderID']); ?>" src="images/actions/screen.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Mark as Screened"/>
                        </a>
                    <?php else: ?>
                        <img src="images/actions/blank.gif" width="16" height="16" class="absmiddle" alt="" style="border: none;" />
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!isset($frozen)): ?>
                    <?php if ($_SESSION['CATS']->getAccessLevel('pipelines.addActivityChangeStatus') >= ACCESS_LEVEL_EDIT): ?>
                        <a href="#" onclick="showPopWin('<?php echo($indexFile); ?>?m=joborders&amp;a=addActivityChangeStatus&amp;jobOrderID=<?php echo($jobOrderID); ?>&amp;candidateID=<?php echo($pipelinesData['candidateID']); ?>', 600, 550, null); return false;">
                            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" style="border: none;"  title="Log an Activity / Change Status" />
                        </a>
                    <?php endif; ?>
                    <?php if ($_SESSION['CATS']->getAccessLevel('pipelines.removeFromPipeline') >= ACCESS_LEVEL_DELETE): ?>
                        <a href="<?php echo($indexFile); ?>?m=joborders&amp;a=removeFromPipeline&amp;jobOrderID=<?php echo($jobOrderID); ?>&amp;candidateID=<?php echo($pipelinesData['candidateID']); ?>" onclick="javascript:return confirm('Remove <?php echo(str_replace('\'', '\\\'', htmlspecialchars($pipelinesData['firstName']))); ?> <?php echo(str_replace('\'', '\\\'', htmlspecialchars($pipelinesData['lastName']))); ?> from the pipeline?')">
                            <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="remove" style="border: none;" title="Remove from Job Order"  />
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
<?php endif; ?>
        </tr>
        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineDetails<?php echo($rowNumber); ?>" style="display:none;">
            <td colspan="11">
                <center>
                    <table width="98%" border=1 class="detailsOutside" style="margin:5px;">
                        <tr>
                            <td align="left" style="padding: 6px 6px 6px 6px; background-color: white; clear: both;">
                                <div style="overflow: auto; height: 200px;" id="pipelineEntryInner<?php echo($rowNumber); ?>">
                                    <img src="images/indicator.gif" alt="" />&nbsp;&nbsp;Loading pipeline details...
                                </div>
                            </td>
                        </tr>
                    </table>
                </center>
            </td>
        </tr>
    <?php endfor; ?>
</table>

