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

variable_set('scrop_style', 'default');
variable_set('scrop_std', $cropstd, 'json');
variable_set('scrop_conf', $cropconf, 'json');

$tempdir = FILES_UPLOAD_DIR . $cropconf['tempdir'];

if (is_writable($tempdir))
{
	mkdir($tempdir);
}
else
{
	$errmsg[] = 'Direktori upload file: ' . $tempdir . ' tidak bisa ditulisi';
}
