<?php
/** Vargen Captcha **
*
*  Author:  By Jesse G. Donat
*   Coauthor: Daniel Olfelt
*
*  Motto: Don't Be Evil
*
* 	April 24, 2009
*
**/

function generateValidationImage($rand) {
		$width = 150;
		$height = 40;
		$font = realpath('.') .'/fonts/BoinkStd.otf';
		$font2 = realpath('.') .'/fonts/SG04.ttf';
		$reduceReadability = 0;
		$image = imagecreatetruecolor($width, $height);
		imageantialias($image,true);
		$bgColor = imagecolorallocate ($image, 255, 255, 255);
		$textColor = imagecolorallocate ($image, 100, 100, 100);
		$typeColor = imagecolorallocate ($image, 50, 50, 50);
		$dotColor = imagecolorallocate($image, 200, 200, 200);
		$lineColor = imagecolorallocate($image, 180, 180, 180);


		imagefill($image,1,1,$bgColor);
		imageColorTransparent($image, $bgColor);

		// write the random text
		$textbox = imagettfbbox($fontSize, 0, $font, $rand);
		$left = 7;

		//imagearc ( $image , 60, 7, 105, 25, 25 , 155 , $lineColor );

		for($x = 0; $x<strlen($rand); $x++) {
			for($y=0; $y<$reduceReadability; $y++) {
				imagettftext($image, 14+rand(-12,0), rand(-20,20), $left+rand(-10,10), 25+rand(-10,10), $lineColor, $font, $rand[$x]);
			}
			imagettftext($image, 22, rand(-20,20), $left, 25, $textColor, $font, $rand[$x]);
			$left+=20;
		}
		$randomSlant = rand(40,60);
		$randomShift = rand(-4,4);
		imagettftext($image, 10, $randomSlant, $left, 35+$randomShift, $typeColor, $font2, "TYPE");
		imagettftext($image, 10, $randomSlant, $left+13, 35+$randomShift, $typeColor, $font2, "HERE");
		imagettftext($image, 10, 0, $left+31, 22+$randomShift, $typeColor, $font2, ">");
		imagettftext($image, 10, 0, $left+34, 22+$randomShift, $typeColor, $font2, ">");


		// send several headers to make sure the image is not cached
		// Date in the past

//		imagefilter($image, IMG_FILTER_EDGEDETECT);

		header("Expires: Mon, 23 Jul 1993 05:00:00 GMT");

		// always modified
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		// HTTP/1.1
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		// HTTP/1.0
		header("Pragma: no-cache");

		// send the content type header so the image is displayed properly
		header('Content-type: image/png');

		imagepng($image);
		imagedestroy($image);

		/**/
}

function generateString($length) {
  $string = '';
  $characters = "abcdefghjkmnpqrstuv23456789";

  for ($i=0;$i<$length;$i++) {
	$char = $characters[mt_rand(0, strlen($characters)-1)];
	$string .= $char;
  }

  return $string;
}


$length = 4;
$validation_code = strtolower(generateString($length));

$app_top = dirname(__FILE__).'/includes/application_top.php';
if (file_exists($app_top)) {
	include_once($app_top);
}

session_start();
$_SESSION['security_code'] = $validation_code;

generateValidationImage($validation_code);