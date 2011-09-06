<?php
/*
 *      image.php
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

if (isset($_POST) AND isset($_POST['member']) AND is_array($_POST['member']))
{
	$member_id = $_POST['member'][0];
	$query = sprintf("SELECT member_image FROM member WHERE member_id = '%s'", mysql_escape_string($member_id));
	$data = array();
	$image = $dbs->query($query);
	$num_rows = $image->num_rows;
	if ($image->num_rows != 0 AND $array = $image->fetch_assoc() AND $array["member_image"] != NULL)
	{
		$member_image = $array["member_image"];
		copy(IMAGES_BASE_DIR . "persons/" . $member_image, FILES_UPLOAD_DIR . $s_conf->tempdir . DIRECTORY_SEPARATOR . $member_image);
		$data['error'] = 0;
		$data['id'] = $member_id;
		$data['src'] = $member_image;
	}
	else
	{
		$data['error'] = 1;
		$data['id'] = $member_id;
	}
	echo json_encode($data);
}
