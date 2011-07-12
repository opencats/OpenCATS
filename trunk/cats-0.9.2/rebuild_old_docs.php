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

    $result = mysql_query('SELECT * FROM `attachment` WHERE `text` IS NULL');

    include_once('./lib/DocumentToText.php');

    $countOK = 0;
    $countError = 0;
    while ($attachment = mysql_fetch_object($result)) {
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
            $upd = mysql_query($sql);
            if (!$upd) {
               $countError++;
                print('DB error: ' . mysql_error());
            } else {
               $countOK++;
            }
       }
       unset($doc2txt);
    }
    print('Success/Fail counters:' . $countOK . '/' . $countError);
}


//$con = mysql_connect("localhost","root","root");
$con = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
if (!$con)
{
  die('Could not connect: ' . mysql_error());
}
mysql_select_db(DATABASE_NAME, $con);

rebuild_old_docs();
?>
