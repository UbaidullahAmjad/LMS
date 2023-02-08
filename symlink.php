<?php

$targetFolder= $_SERVER['DOCUMENT_ROOT'].'/projects/LMS/storage/app/public';
$linkFolder=$_SERVER['DOCUMENT_ROOT'].'/projects/LMS/public/storage';

symlink($targetFolder , $linkFolder);
echo 'Success';

?>

