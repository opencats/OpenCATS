<?php
// array_filetypes.php
// A small (and expandable) array with content type headers associated with several extensions

// first Images
$type['jpg'] = "image/jpeg";
$type['jpeg'] = "image/jpeg";
$type['gif'] = "image/gif";
$type['png'] = "image/png";
$type['bmp'] = "image/bmp";
$type['ico'] = "image/x-ico";

// then systemfiles
$type['exe'] = "application/x-executable";
$type['sh'] = "application/x-sh";
$type['so'] = "application/x-shared-library";

// what if someone opens our file in our  ziplib?
$type['txt'] = "text/plain";
$type['php'] = "text/plain";
$type['php3'] = "text/plain";
$type['php4'] = "text/plain";
$type['php5'] = "text/plain";
$type['phtml'] = "text/plain";

// Audio
$type['mp3'] = "audio/mp3";
$type['wma'] = "audio/x-windows-media-audio";
$type['wav'] = "audio/wav";
$type['ogg'] = "application/ogg";
$type['aac'] = "audio/aac";
$type['rm'] = "audio/x-real-audio";
$type['ra'] = "audio/x-real-audio";

// Video
$type['avi'] = "video/avi";
$type['mpg'] = "video/mpeg";
$type['mpeg'] = "video/mpeg";
$type['mov'] = "video/quicktime";
$type['qt'] = "video/quicktime";
$type['rv'] = "video/vnd.rn-realvideo";
$type['wmv'] = "video/x-ms-wmv";

// Documents
$type['sxw'] = "application/vnd.sun.xml.writer";
$type['sxc'] = "application/vnd.sun.xml.calc";
$type['sxi'] = "application/vnd.sun.xml.impress";
$type['htm'] = "text/html";
$type['html'] = "text/html";
$type['doc'] = "application/msword";
$type['xls'] = "application/vnd.ms-excel";
$type['ppt'] = "application/vnd.ms-powerpoint";

$type['default'] = "text/plain";
