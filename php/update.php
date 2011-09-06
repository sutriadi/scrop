<?php
/*
 *      update.php
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

if ( ! $can_read) {
      die(sprintf('<div class="errorBox">%s</div>', __('You dont have enough privileges to view this section')));
}

$conf = $_SESSION['plugins_conf'];
include('../../../func.php');

checkip();
checken('scrop');
checkref('plugin');

$s_conf = json_decode(variable_get('scrop_conf'));
$s_std = json_decode(variable_get('scrop_std'));

$tempdir = FILES_UPLOAD_DIR . $s_conf->tempdir . DIRECTORY_SEPARATOR;

$data = array(
	'error' => 1,
	'msg' => __('Data yang dikirimkan tidak valid!')
);

/*
Posted Array
(
    [file] => crop_1309449062.jpg
    [action] => save
    [id] => Array
        (
            [0] => Array
                (
                    [name] => members[]
                    [value] => M0003
                )

        )

)
*/

if (isset($_POST) AND isset($_POST['file']) AND isset($_POST['action']) AND isset($_POST['id']))
{
	$file = $_POST['file'];
	$act = $_POST['action'];
	$id = $_POST['id'][0]['value'];
	switch ($act)
	{
		case "delete":
			$data['msg'] = sprintf(__('Delete gambar sementara member: <strong>%s</strong> gagal!'), $id);
			if (unlink($tempdir . $file))
			{
				$data['error'] = 0;
				$data['msg'] = sprintf(__('Delete gambar sementara member: <strong>%s</strong> berhasil!'), $id);
			}
			break;
		case "save":
		default:
			$data['msg'] = sprintf(__('Update gambar member: <strong>%s</strong> gagal!'), $id);

			$from_file = FILES_UPLOAD_DIR . $s_conf->tempdir . "/" . $file;
			$to_file = IMAGES_BASE_DIR . "persons/" . $file;
			copy($from_file, $to_file);
			if (isset($_POST['orig']))
			{
				$orig = IMAGES_BASE_DIR . "persons/" . $_POST['orig'];
				if (file_exists($orig))
					unlink($orig);
			}

			$query = sprintf("UPDATE `member` SET `member_image` = '%s' WHERE `member_id` = '%s'",
				mysql_escape_string($file),
				mysql_escape_string($id)
			);
			$save = $dbs->query($query);
			if ($save)
			{
				$data['error'] = 0;
				$data['msg'] = sprintf(__('Update gambar member: <strong>%s</strong> berhasil!'), $id);
			}
	}
}
echo json_encode($data);
