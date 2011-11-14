<?

include(dirname(__FILE__).'/../roofl.php');


// The default sprite configuration
$settings = $ROOFL_Config['sprite']['__std'];

$sprite = $settings['image'];
$font = $settings['font'];
$font_size = $settings['size'];
$side_pad = $settings['side_pad'];
$font_alpha = $settings['alpha'];
$sprites = $settings['sprites'];

list($base_width, $base_height) = getimagesize($sprite);
$base_image = imagecreatefromstring( file_get_contents( $sprite ) );
$text = stripslashes(urldecode(isset($_GET['text'])?$_GET['text']:$settings['default']));
$text_size_arr = imagettfbbox($font_size, 0, $font, $text);
$text_width = $text_size_arr[2] - $text_size_arr[0];
$text_size_arr = imagettfbbox($font_size, 0, $font, 'ABC'); //ensures no downward letters like j
$text_height = $text_size_arr[1] - $text_size_arr[5];
$width = $text_width + $side_pad * 2;
$img = imagecreatetruecolor($width, $base_height);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);
//set up background
imagecopyresized( $img, $base_image, $base_width / 2, 0, $base_width / 2, 0, $width - $base_width, $base_height, 1, $base_height);
imagecopy($img, $base_image, 0,0,0,0,$base_width / 2, $base_height);
imagecopy($img, $base_image, $width - $base_width / 2,0,$base_width / 2,0,$base_width / 2 + 1, $base_height);

$sprite_height = $base_height / sizeof($sprites);
$v_offset = 0;
foreach ($sprites as $sprite) {
	$_v_offset = isset($sprite['v_offset'])?$sprite['v_offset']:0;
	if (isset($sprite['inset'])) {
		// Do stuff
		list($fr, $fg, $fb) = html2rgb($sprite['inset']);
		$inset_color = imagecolorallocatealpha($img, $fr, $fg, $fb, $font_alpha);
		imagettftext($img, $font_size, 0, $side_pad, ($text_height / 2) + ($sprite_height / 2)  + $v_offset + $_v_offset - 1, $inset_color, $font, $text);
	}
	list($fr, $fg, $fb) = html2rgb($sprite['color']);
	$font_color = imagecolorallocatealpha($img, $fr, $fg, $fb, $font_alpha);
	imagettftext($img, $font_size, 0, $side_pad, ($text_height / 2) + ($sprite_height / 2)  + $v_offset + $_v_offset, $font_color, $font, $text);


	// Increment the offset
	$v_offset += $sprite_height;
}

header('Cache-Control: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', strtotime("-1 Week")) . ' GMT');
header('Expires: '.gmdate('D, d M Y H:i:s', strtotime("+2 Week")) . ' GMT');
header ('Content-type: image/png');
imagepng($img);

function html2rgb($color){
	if ($color[0] == '#'){ $color = substr($color, 1); }

	if (strlen($color) == 6){
		list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
	}elseif (strlen($color) == 3){
		list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	}else{ return false; }
	$red = hexdec($r); $green = hexdec($g); $blue = hexdec($b);
	return array($red, $green, $blue);
}