<?php
/*
 *      setup.php
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

if (isset($_POST) && count($_POST) > 0)
{
	$s_conf = $_POST;
	$s_conf['ratio'] = isset($s_conf['ratio']) ? true : false;
	
	variable_set('scrop_conf', $s_conf, 'json');
	echo json_encode(array('track' => 'sukses'));
}
else
	echo json_encode(array('track' => 'gagal'));
