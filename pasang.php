<?php
/*
 *      pasang.php
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

if (!defined('MODULES_WEB_ROOT_DIR')) {
	exit();
}

/*
konfigurasi tampilan kartu member
*/
$cropstd = array(
	'units' => array(0 => 'pixel', 1 => 'cm'), // unit
	'sources' => array(0 => 'member', 1 => 'biblio'), // picture source	
);

$cropconf = array(
	'width' => '200', // width in default unit
	'height' => '300', // width in default unit
	'tempdir' => 'temp', // temporary directory
	'ratio' => TRUE,
);

variable_set('scrop_std', json_encode($cropstd, JSON_FORCE_OBJECT));
variable_set('scrop_conf', json_encode($cropconf, JSON_FORCE_OBJECT));

$dtables = array(
	'table' => 'scrop',
	'type' => 'member',
	'title' => 'SCrop',
	'desc' => __('DataTables for plugin SCrop.'),
	'first_col' => 'radio',
	'base_cols' => '["member_id","member_name","member_type_id","member_email","inst_name"]',
	'end_cols' => '',
	'php_code' => 0,
	'add_code' => '',
	'windowed' => 1,
	'sort' => '{"member_id":"0","member_name":"1","member_type_name":"2","member_email":"5","inst_name":"4"}'
);

dtable_set($dtables);

$tempdir = FILES_UPLOAD_DIR . $cropconf['tempdir'];

if (is_writable(FILES_UPLOAD_DIR))
{
	mkdir(FILES_UPLOAD_DIR . $cropconf['tempdir']);
}
else
{
	$errmsg[] = __('Direktori upload file tidak bisa ditulisi');
}
