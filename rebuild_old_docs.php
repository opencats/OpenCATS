<?php

// This tool will reindex your *.odt, *.rtf and *.docx formats
// Feature to reindex those files has nbeen added by patch on 2011 July, 08
// by Inuits Company. Email: dennis.povshedny@gmail.com
//
// If you used version 0.9.2 or earlier for a while all your RTF, DOCX and ODT
// candidate attachments are not indexed. By running this script once you may
// index all such attachments.
//
// Please run this from CATS root directory.
//

require_once 'config.php';

function rebuild_old_docs() {

	global $con;
    $result = mysqli_query($con, 'SELECT * FROM `attachment` WHERE `text` IS NULL');

    include_once(LEGACY_ROOT . '/lib/DocumentToText.php');

    $countOK = 0;
    $countError = 0;
    while ($attachment = mysqli_fetch_object($result)) {
        $doc2txt = new DocumentToText();
        $doc2txt->convert('attachments/' . $attachment->directory_name . $attachment->stored_filename,
                $doc2txt->getDocumentType('attachments/' . $attachment->directory_name . $attachment->stored_filename));
        if ($doc2txt->isError())
        {
            $countError++;
            print('Error while converting ' . $attachment->stored_filename . " file\n");
        }
        else
        {
            $extractedText = $doc2txt->getString();
            print('File ' . $attachment->stored_filename." reindexed.\n");
            $sql = 'UPDATE `attachment` SET `text` = \'' . addslashes($extractedText) . '\', `md5_sum_text` = \'' . md5($extractedText) . '\'  WHERE `attachment_id` = ' . $attachment->attachment_id;
            $upd = mysqli_query($con, $sql);
            if (!$upd) {
               $countError++;
				 				$error = "errno: " . $upd->connect_errno . ", ";
				 				$error .= "error: " . $upd->connect_error;
                print('DB error: ' . $error);
            } else {
               $countOK++;
            }
       }
       unset($doc2txt);
    }
    print('Success/Fail counters:' . $countOK . '/' . $countError);
}


//$con = mysql_connect("localhost","root","root");
$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
if (!$con)
{
		$error = "errno: " . mysqli_connect_errno() . ", ";
		$error .= "error: " . mysqli_connect_error();
		die('Could not connect: ' . $error);
}
mysqli_select_db($con, DATABASE_NAME);

rebuild_old_docs();
?>
