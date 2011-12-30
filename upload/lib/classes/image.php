<?php

	/////////////////////////////////////////////////
	// MATHEMATICAL FUNCTIONS
	/////////////////////////////////////////////////

	function degreesToPoint($center_x, $center_y, $degree, $radius_x, $radius_y = 0)
	{
		return array(
			'x' => $center_x - (sin(-$degree * (pi() / 180)) * $radius_x),
			'y' => $center_y - (sin((90 + $degree) * (pi() / 180)) * ($radius_y ? $radius_y : $radius_x))
		);
	}

	function pointsToAngle($x1, $y1, $x2, $y2)
	{
		return ((atan2($y1 - $y2, $x1 - $x2) / pi() * 180) + 270) % 360;
	}

	function pointsToRadius($x1, $y1, $x2, $y2)
	{
		return sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));
	}

	/////////////////////////////////////////////////
	// DRAWING FUNCTIONS
	/////////////////////////////////////////////////

	function imagedrawshape($image, $x, $y, $points, $radius_outer, $radius_inner, $angle, $fill_color, $border_color)
	{
		$poly = array();
		for ($i = 0; $i < ($points * 2); $i++) {
			if ($i % 2 ? true : $radius_inner) {
				$point = degreesToPoint($x, $y, ((360 / ($points * 2)) * $i) + $angle, ($i % 2 ? $radius_outer : $radius_inner), ($i % 2 ? $radius_outer : $radius_inner));
				$poly[] = $point['x'];
				$poly[] = $point['y'];
			}
		} 
		if (count($poly) >= 6) {
			imagefilledpolygon($image, $poly, count($poly) / 2, $fill_color);
			imagepolygon($image, $poly, count($poly) / 2, $border_color);
		}
	}

	function imagegradientfillrect($dest_image, $from_color = 0, $to_color = 0, $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0, $orientation = 0, $colors = 255)
	{
		$col1  = array(($from_color >> 16) & 255, ($from_color >> 8) & 255, $from_color & 255);
		$col2  = array((($to_color >> 16) & 255) - $col1[0], (($to_color >> 8) & 255) - $col1[1], ($to_color & 255) - $col1[2]);
		$size  = min(($orientation ? ($y2 - $y1) : ($x2 - $x1)), $colors, 255);
		$image = imagecreatetruecolor(($orientation ? 1: $size), ($orientation ? $size : 1));

		for ($i = 0; $i < $size; $i++) {
			$color = imagecolorallocate($image, $col1[0] + ($col2[0] * ($i / $size)), $col1[1] + ($col2[1] * ($i / $size)), $col1[2] + ($col2[2] * ($i / $size)));
			if ($orientation) {imagesetpixel($image, 0, $i, $color);} else {imagesetpixel($image, $i, 0, $color);}			
			imagecolordeallocate($image, $color);
		}
		imagecopyresampled($dest_image, $image, $x1, $y1, 0, 0, ($x2 - $x1), ($y2 - $y1), ($orientation ? 1: $size), ($orientation ? $size : 1));
		imagedestroy($image);
	}

	function imagequadgradientfillrect($dest_image, $color1 = 0, $color2 = 0, $color3 = 0, $color4 = 0, $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0, $colors = 200)
	{
		$rgb1   = array(($color1 >> 16) & 255, ($color1 >> 8) & 255, $color1 & 255);
		$rgb2   = array(($color2 >> 16) & 255, ($color2 >> 8) & 255, $color2 & 255);
		$rgb3   = array((($color3 >> 16) & 255) - $rgb1[0], (($color3 >> 8) & 255) - $rgb1[1], ($color3 & 255) - $rgb1[2]);
		$rgb4   = array((($color4 >> 16) & 255) - $rgb2[0], (($color4 >> 8) & 255) - $rgb2[1], ($color4 & 255) - $rgb2[2]);
		$width  = min(($x2 - $x1), $colors, 255);
		$height = min(($y2 - $y1), $colors, 255);
		$image  = imagecreatetruecolor($width, $height);

		for ($y = 0; $y < $height; $y++) {
			$col1 = array($rgb1[0] + ($rgb3[0] * ($y / $height)), $rgb1[1] + ($rgb3[1] * ($y / $height)), $rgb1[2] + ($rgb3[2] * ($y / $height)));
			$col2 = array(($rgb2[0] + ($rgb4[0] * ($y / $height))) - $col1[0], ($rgb2[1] + ($rgb4[1] * ($y / $height))) - $col1[1], ($rgb2[2] + ($rgb4[2] * ($y / $height))) - $col1[2]);
			for ($x = 0; $x < $width; $x++) {
				$color = imagecolorallocate($image, $col1[0] + ($col2[0] * ($x / $width)), $col1[1] + ($col2[1] * ($x / $width)), $col1[2] + ($col2[2] * ($x / $width)));
				imagesetpixel($image, $x, $y, $color);
				imagecolordeallocate($image, $color);
			}

		}
		imagecopyresampled($dest_image, $image, $x1, $y1, 0, 0, ($x2 - $x1), ($y2 - $y1), $width, $height);
		imagedestroy($image);
	}

	function imagescaleresize(&$image, $width = 0, $height = 0, $lock_aspect_ratio = false, $canvas_padding = false, $canvas_crop = false, $color = 0)
	{
		$src_width  = imagesx($image);
		$src_height = imagesy($image);
		$width  = $dst_width  = $width  ? $width  : $src_width;
		$height = $dst_height = $height ? $height : $src_height;

		if ($canvas_padding && ($src_width <= $dst_width && $src_height <= $dst_height)) {
			$width  = $src_width;
			$height = $src_height;
		} elseif ($lock_aspect_ratio) {
			if (($src_width / $dst_width) > ($src_height / $dst_height)) {
				$width  = $dst_width;
				$height = $src_height * ($width / $src_width);
			} else {
				$height = $dst_height;
				$width  = $src_width * ($height / $src_height);
			}
		}
		if ($canvas_crop) {
			$dst_width  = $width;
			$dst_height = $height;
		}

		$dst_image = imageistruecolor($image) ? imagecreatetruecolor($dst_width, $dst_height) : imagecreate($dst_width, $dst_height);
		if ($dst_width != $width || $dst_height != $height) {
			$color = imagecolorallocate($dst_image, ($color >> 16) & 255, ($color >> 8) & 255, $color & 255);
			imagefilledrectangle($dst_image, 0, 0, $dst_width, $dst_height, $color);
			imagecolordeallocate($dst_image, $color);
		}
		imagecopyresampled($dst_image, $image, ($dst_width - $width) / 2, ($dst_height - $height) / 2, 0, 0, $width, $height, $src_width, $src_height);
		imagedestroy($image);
		$image = $dst_image;
	}

