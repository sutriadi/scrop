<?php
/*
 *      frame.php
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

$level = '../../../../../';
require $level . 'sysconfig.inc.php';

require SENAYAN_BASE_DIR.'admin/default/session.inc.php';
require SENAYAN_BASE_DIR.'admin/default/session_check.inc.php';

$can_read = utility::havePrivilege('plugins', 'r');

if (!$can_read)
	die(sprintf('<div class="errorBox">%s</div>', __('You dont have enough privileges to view this section')));

$conf = $_SESSION['plugins_conf'];
require('../../func.php');

checkip();
checken('scrop');
checkref('plugin');

$s_conf = json_decode(variable_get('scrop_conf'));
$s_std = json_decode(variable_get('scrop_std'));

$faktor = 37.795275591;

$def_w = 400;
$def_h = 300;
$def_zoom = "true";
$def_zoom_start = 0;
$def_zoom_min = 50;
$def_zoom_max = 200;
$def_zoom_step = 5;
$def_rotate = "true";
$def_rotate_step = 5;
$def_ratio = (isset($s_conf->ratio) AND $s_conf->ratio == TRUE) ? "true" : "false";
$def_sel_w = (isset($s_conf->unit) AND $s_conf->unit == "pixel") ? $s_conf->width : $faktor * $s_conf->width;
$def_sel_h = (isset($s_conf->unit) AND $s_conf->unit == "pixel") ? $s_conf->height : $faktor * $s_conf->height;
if (isset($_GET['src']))
	$picture = $level . FILES_DIR . "/" . $s_conf->tempdir . "/" . $_GET['src'];
else
	$picture = './pictures/default.jpg';
list($width, $height) = getimagesize($picture);
if ($height > $def_h || $width > $def_w)
{
	$diff_h = $height / $def_h;
	$diff_w = $width / $def_w;
	$diff = ($diff_h > $diff_w) ? $diff_h : $diff_w;
/*
	ukuran tinggi gambar 4000
	ukuran tinggi kanvas 400
	kebesaran 10x
	supaya fit maka diturunkan sebanyak 
*/
	unset($diff_h);
	unset($diff_w);
	unset($diff);
}
if ($def_sel_h > $def_h || $def_sel_w > $def_w)
{
	$diff_h = $def_sel_h / $def_h;
	$diff_w = $def_sel_w / $def_w;
	$diff = ($diff_h > $diff_w) ? $diff_h : $diff_w;
	$def_sel_h = $def_sel_h / $diff;
	$def_sel_w = $def_sel_w / $diff;
	unset($diff_h);
	unset($diff_w);
	unset($diff);
}

?>
<html>
<head>
	<style type="text/css" title="currentStyle">
		@import "../../<?php echo css_get();?>";
		@import "./css/jquery.cropzoom.css";

		#control {
			display: block;
			width: 500px;
			margin: 5px 0;
			padding: 5px;
		}
		#zoom, #rot {
			margin:auto;
			height:25px;
		}
		#movement {
			width: 100px;
			height: 100px;
			float: left;
			margin: auto;
		}
        #buttons {
			display: block;
			float: none;
			padding: 5px;
			margin: 5px 0;
		}	</style>
	<script type="text/javascript" language="javascript" src="../../library/js/jquery.min.js"></script>
	<script type="text/javascript" language="javascript" src="../../library/ui/js/jquery-ui.custom.min.js"></script>
	<script type="text/javascript" src="./js/jquery.cropzoom.js"></script>
	<script type="text/javascript">
		var cropzoom_def = {
			width: <?php echo $def_w;?>,
			height: <?php echo $def_h;?>,
			bgColor: '#CACACA',
			enableRotation: <?php echo $def_rotate;?>,
			enableZoom: <?php echo $def_zoom;?>,
			zoomSteps: <?php echo $def_zoom_step;?>,
			rotationSteps: <?php echo $def_rotate_step;?>,
			expose: {
				slidersOrientation: 'horizontal',
				zoomElement: '#zoom',
				rotationElement: '#rot',
				elementMovement:'#movement'
			},
			selector: {
				centered: true,
				borderColor:'#FFF',
				borderColorHover:'#C1C1C1',
				aspectRatio: <?php echo $def_ratio;?>,
				startWithOverlay: true,
				hideOverlayOnDragAndResize: false,
				showPositionsOnDrag: false,
				w: <?php echo $def_sel_w;?>,
				h: <?php echo $def_sel_h;?>,
			},
			image: {
				source: '<?php echo $picture;?>',
				width: <?php echo $width;?>,
				height: <?php echo $height;?>,
				minZoom: <?php echo $def_zoom_min;?>,
				maxZoom: <?php echo $def_zoom_max;?>,
				startZoom: <?php echo $def_zoom_start;?>,
				useStartZoomAsMinZoom: true,
				snapToContainer: true
			}
		};

		$(document).ready(function(){
			var cropzoom2 = $('#crop_container2').cropzoom(cropzoom_def);
			$('button[class="button"]').button();
			
			$('#restore').click(function() {
				cropzoom2.restore();
			});
			
			$('#crop').click(function() {
				cropzoom2.send('./php/resize_and_crop.php','POST', {}, function(data){
					window.parent.set_image(data);
				}, "json");
			});
		});

	</script>
</head>
<body>

	<div id="crop_container2"></div>
	<div id="control">
		<div id="movement"></div>
		<div id="zoom"></div>
		<div id="rot"></div>
	</div>
	<div class="cleared"></div>
	<div id="buttons">
		<button class="button" id="restore"><?php echo __('Restore');?></button>
		<button class="button" id="crop"><?php echo __('Crop');?></button>
	</div>

</body>
</html>
