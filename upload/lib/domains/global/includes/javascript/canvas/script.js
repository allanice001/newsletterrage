
	$().loadModule(
		{
			name    : 'Canvas Functions',
			version : '1.0.0',
			author  : 'Trevor Lewis - Thraddash Software',

			ElementCANVAS : {

				/**
				 * Draws triangles, squares, stars or circles at a specified point and angle
				 * @param mixed $canvas The canvas element
				 * @param int $x The center horizontal coordinate
				 * @param int $y The center vertical coordinate
				 * @param int $points How many points on the shape
				 * @param int $radius_outer The radius of the outer points
				 * @param int $radius_inner The radius of the inner points, 0 to exclude inner points
				 * @param int $angle 0 to 360
				 * @param string $fill_color The color to fill the shape
				 * @param string $border_color The color to outline the shape
				 */
				drawShape : function($canvas, $x, $y, $points, $radius_outer, $radius_inner, $angle, $fill_color, $border_color)
				{
					var
						$context = $canvas.getContext('2d'),
						$point_x, $point_y,
						$i, $j;

					$context.save();
					$context.beginPath();
					for ($i = 0, $j = 0; $i < ($points * 2); $i++) {
						if ($i % 2 ? true : $radius_inner) {
							$point_x = Math.floor($x - (Math.sin(-(((360 / ($points * 2)) * $i) + $angle) * (Math.PI / 180)) * ($i % 2 ? $radius_outer : $radius_inner)));
							$point_y = Math.floor($y - (Math.sin((90 + (((360 / ($points * 2)) * $i) + $angle)) * (Math.PI / 180)) * ($i % 2 ? $radius_outer : $radius_inner)));
							if ($j++) {
								$context.lineTo($point_x, $point_y);
							} else {
								$context.moveTo($point_x, $point_y);
							}
						}						
					}
					$context.closePath();
					if ($fill_color) {
						$context.fillStyle = $fill_color;
						$context.fill();
					}
					if ($border_color) {
						$context.strokeStyle = $border_color;
						$context.stroke();
					}
					$context.restore();

					return $canvas;
				}
			}
		}
	);
