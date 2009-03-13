<?php
/*
   * OSATS
   *
   *
   * Open Source GNU License will apply
*/

include_once('./lib/Pipelines.php');
include_once('./lib/TemplateUtility.php');
include_once('./lib/StringUtility.php');
include_once('./lib/osatutil.php');
include_once('./lib/Hooks.php');
include_once('./lib/JobOrders.php');
include_once('./lib/i18n.php');

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

$jobOrderID     = $_REQUEST['joborderID'];
$page           = $_REQUEST['page'];
$entriesPerPage = $_REQUEST['entriesPerPage'];
$sortBy         = $_REQUEST['sortBy'];
$sortDirection  = $_REQUEST['sortDirection'];
$indexFile      = $_REQUEST['indexFile'];
$isPopup        = $_REQUEST['isPopup'] == 1 ? true : false;

$_SESSION['OSATS']->setPipelineEntriesPerPage($entriesPerPage);

$jobOrders = new JobOrders($siteID);
$jobOrdersData = $jobOrders->get($jobOrderID);

/* Get an array of the pipeline data. */
$pipelines = new Pipelines($siteID);
$pipelinesRS = $pipelines->getJobOrderPipeline($jobOrderID);

/* Format pipeline data. */
foreach ($pipelinesRS as $rowIndex => $row) {
  if ($row['submitted'] == '1') {
    $pipelinesRS[$rowIndex]['highlightStyle'] = 'jobLinkSubmitted';
  } else {
    $pipelinesRS[$rowIndex]['highlightStyle'] = 'jobLinkCold';
  }

  $pipelinesRS[$rowIndex]['addedByAbbrName'] = StringUtility::makeInitialName(
    $pipelinesRS[$rowIndex]['addedByFirstName'],
    $pipelinesRS[$rowIndex]['addedByLastName'],
    LAST_NAME_MAXLEN
  );

  if ($row['attachmentPresent'] == 1) {
    $pipelinesRS[$rowIndex]['iconTag'] = '<img src="images/paperclip.gif" alt="" width="16" height="16" />';
  } else {
      $pipelinesRS[$rowIndex]['iconTag'] = '&nbsp;';
  }

  $pipelinesRS[$rowIndex]['ratingLine'] = TemplateUtility::getRatingObject(
    $pipelinesRS[$rowIndex]['ratingValue'],
    $pipelinesRS[$rowIndex]['candidateJobOrderID'],
    $_SESSION['OSATS']->getCookie()
  );
}

/* Sort the data. */
if ($sortBy !== '' && $sortBy !== 'undefined') {
  $sorting = array();
  foreach ($pipelinesRS as $p) {
    $sorting[] = $p[$sortBy];
  }
  if ($sortBy == 'ratingValue') {
    array_multisort($sorting, $sortDirection == 'desc' ? SORT_DESC : SORT_ASC , SORT_NUMERIC, $pipelinesRS);
  } else {
    array_multisort($sorting, $sortDirection == 'desc' ? SORT_DESC : SORT_ASC , SORT_STRING, $pipelinesRS);
  }
}

$minEntry = $entriesPerPage * $page;
$maxEntry = $minEntry + $entriesPerPage;

if ($maxEntry > count($pipelinesRS)) $maxEntry = count($pipelinesRS);


function getSortLink($field, $delimiter = "'", $changeDirection = true) {
  global $sortBy, $sortDirection;

  $s = $delimiter.$field.$delimiter.', ';

  if ($changeDirection) {
    if ($sortBy == $field) {
      if ($sortDirection == 'desc' || $sortDirection == '') {
        $s.= $delimiter.'asc'.$delimiter;
      } else {
        $s.= $delimiter.'desc'.$delimiter;
      }
    } else {
      $s.= $delimiter.'asc'.$delimiter;
    }
  } else {
    if ($sortDirection == 'desc' || $sortDirection == '') {
      $s.= $delimiter.'desc'.$delimiter;
    } else {
      $s.= $delimiter.'asc'.$delimiter;
    }
  }
  return $s;
}

// prints a table-header (needed below). Makes things easier. (MK)
function printTH($field, $fieldLabel, $width) {
  global $jobOrderID, $page, $entriesPerPage, $isPopup;

  $s = '<th align="left" width="'.$width.'" nowrap="nowrap">';
  $s.= '<a href="javascript:void(0);" onclick="PipelineJobOrder_populate('
       .$jobOrderID.', '
       .$page.', '
       .$entriesPerPage.', '
       .getSortLink($field).', '
       . ($isPopup ? 1 :0).', '
       ."'ajaxPipelineTable', "
       ."'".$_SESSION['OSATS']->getCookie()."', "
       ."'ajaxPipelineTableIndicator', "
       ."'".$indexFile."'"
       .');">'
       .$fieldLabel
       .'</a></th>';
  echo $s;
}


if (!eval(Hooks::get('JO_AJAX_GET_PIPELINE'))) return;

?>

<?php echo(TemplateUtility::getRatingsArrayJS()); ?>

<script type="text/javascript">
    PipelineJobOrder_setLimitDefaultVars('<?php echo($sortBy); ?>', '<?php echo($sortDirection); ?>');
    var s = '';
    s += 'Showing entries <?php echo($minEntry + 1); ?> through <?php echo($maxEntry); ?> of <?php echo(count($pipelinesRS)) ?>: ';
    <?php if ($minEntry != 0): ?>
        s += '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page - 1); ?>, <?php echo($entriesPerPage); ?>, <?php echo getSortLink($sortBy, "\'", false); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, \'ajaxPipelineTable\', \'<?php echo($_SESSION['OSATS']->getCookie()); ?>\', \'ajaxPipelineTableIndicator\', \'<?php echo($indexFile); ?>\');">&lt; Previous Page</a>';
    <?php endif; ?>
    <?php if ($maxEntry < count($pipelinesRS)): ?>
        s += '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="PipelineJobOrder_populate(<?php echo($jobOrderID); ?>, <?php echo($page + 1); ?>, <?php echo($entriesPerPage); ?>, <?php echo getSortLink($sortBy, "\'", false); ?>, <?php if ($isPopup) echo(1); else echo(0); ?>, \'ajaxPipelineTable\', \'<?php echo($_SESSION['OSATS']->getCookie()); ?>\', \'ajaxPipelineTableIndicator\', \'<?php echo($indexFile); ?>\');">Next Page &gt;</a>';
    <?php endif; ?>
    <?php if (count($pipelinesRS) <= 15): ?>
        document.getElementById('ajaxPipelineControl').style.display='none';
    <?php endif; ?>

    document.getElementById('ajaxPipelineNavigation').innerHTML = s;
</script>
    <table class="notsortable" id="pipelineTable" width="925">
    <tr>
        <th></th>
        <th align="left" width="10" nowrap="nowrap"></th>
        <?php 
             // rewritten 2009-02-12 by ALQUANTO
             // $field,           $fieldLabel,         $width
        printTH('ratingValue',    __('Match'),          62);
        printTH('firstName',      __('First Name'),     80);
        printTH('lastName',       __('Last Name'),     100);
        printTH('state',          __('Loc'),            40);
        printTH('dateCreatedInt', __('Added'),          60);
        printTH('addedByAbbrName',__('Entered By'),     70);
        printTH('status',         __('Status'),         65);
        printTH('lastActivity',   __('Last Activity'),  65);
        
        if (!$isPopup) echo '<th align="center">Action</th>';
        ?>
    </tr>

    <?php for ($i = $minEntry; $i < $maxEntry; $i++): ?>
        <?php $pipelinesData = $pipelinesRS[$i]; $rowNumber = $i - $minEntry; ?>
        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineRow<?php echo($rowNumber); ?>">
            <td valign="top">
                <span id="pipelineEntryOpen<?php echo($rowNumber); ?>">
                    <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineEntryClose<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineEntryOpen<?php echo($rowNumber); ?>').style.display = 'none'; PipelineDetails_populate(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 'pipelineEntryInner<?php echo($rowNumber); ?>', '<?php echo($_SESSION['OSATS']->getCookie()); ?>');">
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
                <?php if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_EDIT && !$_SESSION['OSATS']->hasUserCategory('sourcer')): ?>
                    <?php if ($pipelinesData['ratingValue'] < 0): ?>
                        <a href="#" id="screenLink<?php echo($pipelinesData['candidateJobOrderID']); ?>" onclick="moImageValue<?php echo($pipelinesData['candidateJobOrderID']); ?> = 0; setRating(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 0, 'moImage<?php echo($pipelinesData['candidateJobOrderID']); ?>', '<?php echo($_SESSION['OSATS']->getCookie()); ?> '); return false;" >
                            <img id="screenImage<?php echo($pipelinesData['candidateJobOrderID']); ?>" src="images/actions/screen.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Mark as Screened"/>
                        </a>
                    <?php else: ?>
                        <img src="images/actions/blank.gif" width="16" height="16" class="absmiddle" alt="" style="border: none;" />
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!isset($frozen)): ?>
                    <?php if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_EDIT): ?>
                        <a href="#" onclick="showPopWin('<?php echo($indexFile); ?>?m=joborders&amp;a=addActivityChangeStatus&amp;jobOrderID=<?php echo($jobOrderID); ?>&amp;candidateID=<?php echo($pipelinesData['candidateID']); ?>', 600, 550, null); return false;">
                            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" style="border: none;"  title="Log an Activity / Change Status" />
                        </a>
                    <?php endif; ?>
                    <?php if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_DELETE): ?>
                        <a href="<?php echo($indexFile); ?>?m=joborders&amp;a=removeFromPipeline&amp;jobOrderID=<?php echo($jobOrderID); ?>&amp;candidateID=<?php echo($pipelinesData['candidateID']); ?>" onclick="javascript:return confirm('Remove <?php echo(str_replace('\'', '\\\'', htmlspecialchars($pipelinesData['firstName']))); ?> <?php echo(str_replace('\'', '\\\'', htmlspecialchars($pipelinesData['lastName']))); ?> from the pipeline?')">
                            <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="remove" style="border: none;" title="Remove from Pipeline"  />
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