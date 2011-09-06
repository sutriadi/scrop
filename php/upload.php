<?php

define('INDEX_AUTH', '1');

if (!defined('SENAYAN_BASE_DIR')) {
	require '../../../../../../sysconfig.inc.php';
	require SENAYAN_BASE_DIR.'admin/default/session.inc.php';
}
require SENAYAN_BASE_DIR.'admin/default/session_check.inc.php';

$can_read = utility::havePrivilege('plugins', 'r');
$can_read = utility::havePrivilege('plugins', 'w');

if (!$can_read) {
      die(sprintf('<div class="errorBox">%s</div>', __('You dont have enough privileges to view this section')));
}

$conf = $_SESSION['plugins_conf'];
include('../../../func.php');

checkip();
checken('scrop');
checkref('plugin');

$s_conf = json_decode(variable_get('scrop_conf'));
$s_std = json_decode(variable_get('scrop_std'));

$picture = isset($_FILES["picture"]) ? $_FILES["picture"] : FALSE;
$tempdir = FILES_UPLOAD_DIR . $s_conf->tempdir;
$params = array('error' => TRUE);

if ($picture === FALSE || $picture["error"] == 4)
{
	$params['msg'] = __('No file was uploaded!');
}
else
{
	switch ($picture["error"])
	{
		case 1:
		case 2:
			$params['msg'] = __('Uploaded file exceeds the size limit!');
			break;
		case 3:
			$params['msg'] = __('Uploaded file was corrupt!');
			break;
		case 6:
			$params['msg'] = __('Missing a temporary folder!');
			break;
		case 7:
			$params['msg'] = __('Failed to write file to disk!');
			break;
		case 8:
			$params['msg'] = __('Some PHP extension stopped uploading file!');
			break;
		case 0:
			$ext = strtolower(end(explode(".",$picture["name"])));
			$allow = array('jpg', 'jpeg', 'gif', 'png');
			$tempname = "upload_".time().".".$ext;
			list($w, $h) = getimagesize($picture['tmp_name']);
			if ( ! in_array($ext, $allow))
				$params['msg'] = __('Uploaded file was not allowed!');
			else if ( ! move_uploaded_file($picture['tmp_name'], $tempdir . "/" . $tempname))
				$params['msg'] = __('Could not store the uploaded file to temporary directory!');
			else
			{
				$params['error'] = FALSE;
				$params['attr'] = array(
					'src' => $tempname,
					'w' => $w,
					'h' => $h,
				);
				$params['msg'] = __('File uploaded successfully!');
			}	
			break;
		default:
			$params['msg'] = __('Something cause uploading file failed!');
	}
}

echo json_encode($params);
