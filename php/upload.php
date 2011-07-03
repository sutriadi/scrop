<?php
/*
 *      upload.php
 *      
 *      Copyright 2011 Indra Sutriadi Pipii <indra@sutriadi.web.id>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

if (!defined('SENAYAN_BASE_DIR')) {
	require '../../../../../sysconfig.inc.php';
	require SENAYAN_BASE_DIR.'admin/default/session.inc.php';
}
require SENAYAN_BASE_DIR.'admin/default/session_check.inc.php';

$can_read = utility::havePrivilege('plugins', 'r');
$can_write = utility::havePrivilege('plugins', 'w');

if (!$can_read AND $can_write) {
      die('<div class="errorBox">You dont have enough privileges to view this section</div>');
}

$conf = $_SESSION['plugins_conf'];
include('../../func.php');

checkip();
checken();
checkref();

$s_conf = json_decode(variable_get('scrop_conf'));
$s_std = json_decode(variable_get('scrop_std'));

$picture = isset($_FILES["picture"]) ? $_FILES["picture"] : FALSE;
$tempdir = FILES_UPLOAD_DIR . $s_conf->tempdir;
$params = array('error' => TRUE);

if ($picture === FALSE || $picture["error"] == 4)
{
	$params['msg'] = "No file was uploaded!";
}
else
{
	switch ($picture["error"])
	{
		case 1:
		case 2:
			$params['msg'] = "Uploaded file exceeds the size limit!";
			break;
		case 3:
			$params['msg'] = "Uploaded file was corrupt!";
			break;
		case 6:
			$params['msg'] = "Missing a temporary folder!";
			break;
		case 7:
			$params['msg'] = "Failed to write file to disk!";
			break;
		case 8:
			$params['msg'] = "Some PHP extension stopped uploading file!";
			break;
		case 0:
			$ext = strtolower(end(explode(".",$picture["name"])));
			$allow = array('jpg', 'jpeg', 'gif', 'png');
			$tempname = "upload_".time().".".$ext;
			list($w, $h) = getimagesize($picture['tmp_name']);
			if ( ! in_array($ext, $allow))
				$params['msg'] = "Uploaded file was not allowed!";
			else if ( ! move_uploaded_file($picture['tmp_name'], $tempdir . "/" . $tempname))
				$params['msg'] = "Could not store the uploaded file to temporary directory!";
			else
			{
				$params['error'] = FALSE;
				$params['attr'] = array(
					'src' => $tempname,
					'w' => $w,
					'h' => $h,
				);
				$params['msg'] = "File uploaded successfully!";
			}	
			break;
		default:
			$params['msg'] = "Something cause uploading file failed!";
	}
}

echo json_encode($params);
