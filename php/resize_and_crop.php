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

$tempdir = FILES_UPLOAD_DIR . $s_conf->tempdir . DIRECTORY_SEPARATOR;

/* Functions */
function returnCorrectFunction($ext){
    $function = "";
    switch($ext){
        case "png":
            $function = "imagecreatefrompng"; 
            break;
        case "jpeg":
            $function = "imagecreatefromjpeg"; 
            break;
        case "jpg":
            $function = "imagecreatefromjpeg";  
            break;
        case "gif":
            $function = "imagecreatefromgif"; 
            break;
    }
    return $function;
}

function parseImage($ext,$img,$file = null){
    switch($ext){
        case "png":
            imagepng($img,($file != null ? $file : '')); 
            break;
        case "jpeg":
            imagejpeg($img,($file ? $file : ''),90); 
            break;
        case "jpg":
            imagejpeg($img,($file ? $file : ''),90);
            break;
        case "gif":
            imagegif($img,($file ? $file : ''));
            break;
    }
}

function setTransparency($imgSrc,$imgDest,$ext){

	if($ext == "png" || $ext == "gif"){
		$trnprt_indx = imagecolortransparent($imgSrc);
		// If we have a specific transparent color
		if ($trnprt_indx >= 0) {
			// Get the original image's transparent color's RGB values
			$trnprt_color    = imagecolorsforindex($imgSrc, $trnprt_indx);
			// Allocate the same color in the new image resource
			$trnprt_indx    = imagecolorallocate($imgDest, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
			// Completely fill the background of the new image with allocated color.
			imagefill($imgDest, 0, 0, $trnprt_indx);
			// Set the background color for new image to transparent
			imagecolortransparent($imgDest, $trnprt_indx);
		} 
		// Always make a transparent background color for PNGs that don't have one allocated already
		elseif ($ext == "png") {
		   // Turn off transparency blending (temporarily)
		   imagealphablending($imgDest, true);
		   // Create a new transparent color for image
		   $color = imagecolorallocatealpha($imgDest, 0, 0, 0, 127);
		   // Completely fill the background of the new image with allocated color.
		   imagefill($imgDest, 0, 0, $color);
		   // Restore transparency blending
		   imagesavealpha($imgDest, true);
		}
	}
}

if (isset($_POST['imageSource']))
{
	$imageSource = '../' . $_POST['imageSource'];
}
else
{
	$imageSource = '../pictures/default.jpg';
}
list($width, $height) = getimagesize($imageSource);
$pWidth = isset($_POST["imageW"]) ? $_POST["imageW"] : '80';
$pHeight =  isset($_POST["imageH"]) ? $_POST["imageH"] : '120';
$ext = end(explode(".",$imageSource));
$function = returnCorrectFunction($ext);  
$image = $function($imageSource);
$width = imagesx($image);
$height = imagesy($image);

// Resample
$image_p = imagecreatetruecolor($pWidth, $pHeight);
setTransparency($image,$image_p,$ext);
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $pWidth, $pHeight, $width, $height);
imagedestroy($image); 
$widthR = imagesx($image_p);
$hegihtR = imagesy($image_p);

if(isset($_POST["imageRotate"]))
{
    $angle = 360 - $_POST["imageRotate"];
    $image_p = imagerotate($image_p,$angle,0);
    $pWidth = imagesx($image_p);
    $pHeight = imagesy($image_p);
}

if ($pWidth > $_POST["viewPortW"])
{
    $src_x = abs(abs($_POST["imageX"]) - abs(($_POST["imageW"] - $pWidth) / 2));
    $dst_x = 0;
}
else
{
    $src_x = 0;
    $dst_x = $_POST["imageX"] + (($_POST["imageW"] - $pWidth) / 2); 
}

if ($pHeight > $_POST["viewPortH"])
{
    $src_y = abs($_POST["imageY"] - abs(($_POST["imageH"] - $pHeight) / 2));
    $dst_y = 0;
}
else
{
    $src_y = 0;
    $dst_y = $_POST["imageY"] + (($_POST["imageH"] - $pHeight) / 2); 
}
$viewport = imagecreatetruecolor($_POST["viewPortW"],$_POST["viewPortH"]);
setTransparency($image_p,$viewport,$ext); 
imagecopy($viewport, $image_p, $dst_x, $dst_y, $src_x, $src_y, $pWidth, $pHeight);
imagedestroy($image_p);


$selector = imagecreatetruecolor($_POST["selectorW"],$_POST["selectorH"]);
setTransparency($viewport,$selector,$ext);
imagecopy($selector, $viewport, 0, 0, $_POST["selectorX"], $_POST["selectorY"],$_POST["viewPortW"],$_POST["viewPortH"]);

$dir = $tempdir;
$file = "crop_".time().".".$ext;
parseImage($ext,$selector,$dir . $file);
imagedestroy($viewport);

//Return value
list($w, $h) = getimagesize($dir . $file);
echo json_encode(array('src' => $file,
	'w' => $w,
	'h' => $h,
));
