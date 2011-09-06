<?php
/*
 *      template.php
 *      
 *      Copyright 2011 Indra Sutriadi Pipii <indra.sutriadi@gmail.com>
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

if ( ! defined('SENAYAN_BASE_DIR')) { exit(); }
if ( ! $can_read)
	die(sprintf('<div class="errorBox">%s</div>', __('You dont have enough privileges to view this section')));

$s_conf = json_decode(variable_get('scrop_conf'));
$s_std = json_decode(variable_get('scrop_std'));

// mengambil daftar nama kolom tabel member, dan urutan kolom
$base_cols_name = base_cols_name('scrop');
$fcols = cols_get('scrop');

$dtables = table_render('scrop');
extract($dtables);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $name;?> <?php echo $version;?></title>
	<style type="text/css" title="currentStyle">
		@import "../../library/dataTables/css/demo_page.css";
		@import "../../library/dataTables/css/demo_table_jui.css";
		@import "../../<?php echo css_get();?>";
		@import "./css/s.css";
		@import "./css/custom.css";
	</style>
	<script type="text/javascript">
		<?php if (isset($php_js)) echo $php_js;?>
		var dirtemp='../../../../../<?php echo FILES_DIR . "/" . $s_conf->tempdir . "/";?>';
	</script>
	<script type="text/javascript" language="javascript" src="../../library/js/jquery.min.js"></script>
	<script type="text/javascript" language="javascript" src="../../library/ui/js/jquery-ui.custom.min.js"></script>
	<script type="text/javascript" language="javascript" src="../../library/dataTables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="./js/jquery.jqupload.min.js"></script>
	<script type="text/javascript" language="javascript" src="./js/jquery-custom-file-input.js"></script>
	<script type="text/javascript" charset="utf-8" language="javascript" src="./js/s.js"></script>
	<script type="text/javascript" charset="utf-8" language="javascript" src="./js/custom.js"></script>

</head>
<body id="dt_example">
	<div id="container">
		<h1><?php echo $name;?> <?php echo $version;?></h1>
		<div id="demo">
			<form id="formulir" name="formulir" target="" action="" method="POST">
				<div style="text-align:left; padding-bottom: 1em; float: left;" class="ui-widget">
					<button type="button" id="to_options" title="Alt+Shift+O" accesskey="O">
						<?php echo __('<u>O</u>ptions');?>
					</button>
				</div>
				<div style="text-align:right; padding-bottom:1em;" class="ui-widget">
					<button type="button" id="crop" name="crop" title="Alt+Shift+C" accesskey="C" class="ui-button ui-button-text ui-state-default ui-corner-all">
						<?php echo __('Do <u>C</u>rop');?>
					</button>
					<button type="button" id="upload" name="upload" title="Alt+Shift+U" accesskey="U" class="ui-button ui-button-text ui-state-default ui-corner-all">
						<?php echo __('Do <u>U</u>pload');?>
					</button>
				</div>
				<div style="margin: 5px 0px;" width="100%">
					<button type="button" onclick="alluncheck(this);" id="btn2" accesskey="E" title="Alt+Shift+E" class="ui-button ui-state-default ui-corner-all"><?php echo __('R<u>e</u>set');?></button>
				</div>
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="members">
					<?php echo $thead;?>
					<?php echo $tbody;?>
					<?php echo $tfoot;?>
				</table>
				<div style="margin: 5px 0px;" width="100%">
					<button type="button" onclick="alluncheck(this);" id="btn5" accesskey="E" title="Alt+Shift+E" class="ui-button ui-state-default ui-corner-all"><?php echo __('R<u>e</u>set');?></button>
				</div>
			</form>
		</div>
		<div class="spacer"></div>
		<div style="text-align:left; padding-bottom:1em; float: left;" class="ui-widget">
			<button type="button" id="reload" accesskey="R" title="Alt+Shift+R" class="ui-button ui-state-default ui-corner-all">
				<?php echo __('<u>R</u>eload');?>
			</button>
		</div>
		<div style="text-align:right; padding-bottom:1em;" class="ui-widget">
			<button type="button" id="tutup" accesskey="X" title="Alt+Shift+X" class="ui-button ui-state-default ui-corner-all">
				<?php echo __('E<u>x</u>it');?>
			</button>
		</div>
		<address style="text-align: center;">
			Copyright &copy; 1431-1432 H / 2010-2011 M by Indra Sutriadi Pipii.<br />
			Build with jQuery-UI + jQuery-CropZoom + dataTables plugin.
		</address>
	</div>
	<div id="s_dialog" title="Information">
		<p id="validateTips"></p>
	</div>
	<div id="s_result" title="Gambar Pratinjau">
		<div class="fullwidth">
			<img id="s_result_image" class="center" title="" src="">
		</div>
	</div>
	<div id="s_upload" title="Upload">
		<form id="s_upload_form" name="s_upload_form" method="POST" action="./php/upload.php" enctype="multipart/form-data" target="s_upload_frame">
			<p>
				<input type="file" id="s_file" name="picture" />
			</p>
			<p>
				<?php echo __('File');?>: <span id="s_file_name"><?php echo __('No file');?></span>
			</p>
			<p>
				<input type="button" id="s_choose" value="<?php echo __('Browse...');?>" />
				<input type="submit" value="<?php echo __('Upload');?>" />
			</p>
		</form>
		<iframe id="s_upload_frame" name="s_upload_frame" src="" border="0" width="0" height="0" style="display: none;"></iframe>
	</div>
	<div id="s_crop" title="Crop">
		<iframe id="s_crop_frame" src="" border="0" style="border:none;" width="100%" height="100%"></iframe>
	</div>

<?php
	$options_units = '';
	$set_units = (isset($s_std->units) AND is_array((array) $s_std->units)) ? TRUE : FALSE;
	$units = $set_units === TRUE ? $s_std->units : array();
	foreach ($units as $unit)
	{
		$selected = (isset($s_conf->unit) AND $unit == $s_conf->unit) ? "selected" : "";
		$options_units .= "<option $selected value=\"$unit\">$unit</option>";
	}
	$set_unit = ($set_units === TRUE AND isset($s_conf->unit)) ? $s_conf->units->$s_conf->unit : '';

	$checked_ratio = (isset($s_conf->ratio) AND $s_conf->ratio == true) ? "checked" : "";
	
	$options_sources = '';
	$sources = (isset($s_std->sources) AND is_array((array) $s_std->sources)) ? $s_std->sources : array();
	foreach ($sources as $source)
	{
		$selected = (isset($s_conf->source) AND $source == $s_conf->source) ? "selected" : "";
		$options_sources .= "<option $selected value=\"$source\">$source</option>";
	}
?>

	<div id="s_options" title="Options">
		<form name="s_options_form">
			<fieldset>
				<p>
					<label for="source" class="lmid"><?php echo __('Source');?>:</label>
					<select name="source" disabled>
						<?php echo $options_sources; ?>
					</select>
				</p>
				<p>
					<label for="tempdir" class="lmid"><?php echo __('Temp Folder');?>:</label>
					<input id="tempdir" name="tempdir" size="20" type="text" class="text ui-corner-all" value="<?php echo isset($s_conf->tempdir) ? $s_conf->tempdir : '';?>" />
				</p>
				<div id="s_options_accordion">
					<div>
						<h3><a href="#"><?php echo __('Size');?></a></h3>
						<div>
							<p>
								<label for="unit" class="lmid"><?php echo __('Unit');?>:</label>
								<select id="unit" name="unit" onchange="satuan(this);">
									<?php echo $options_units; ?>
								</select>
							</p>
							<p>
								<label for="width" class="lmid"><?php echo __('Width');?>:</label>
								<input id="width" name="width" maxlength="4" size="4" type="text" class="text ui-corner-all" value="<?php echo isset($s_conf->width) ? $s_conf->width : '200';?>" />
								<span class="unit"><?php echo $set_unit;?></span>
							</p>
							<p>
								<label for="height" class="lmid"><?php echo __('Height');?>:</label>
								<input id="height" name="height" maxlength="4" size="4" type="text" class="text ui-corner-all" value="<?php echo isset($s_conf->height) ? $s_conf->height : '300';?>" />
								<span class="unit"><?php echo $set_unit;?></span>
							</p>
							<p>
								<label for="ratio" class="llong"><?php echo __('Keep Ratio?');?></label>
								<input id="ratio" type="checkbox" name="ratio" <?php echo $checked_ratio;?> /> <span><?php echo __('Yes!');?></span>
							</p>
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

</body>
</html>
