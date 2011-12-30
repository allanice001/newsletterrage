
	$().loadModule(
		{
			name    : 'Styles',
			version : '1.0.0',
			author  : 'Trevor Lewis - Thraddash Software',

			Element : {

				/**
				 * Sets or retrieves styling information from a specified element
				 * @param mixed $element The element object
				 * @param string $style The name of the style as referred to in the style collection eg. backgroundColor
				 * @param mixed $value The value to set the specified style. If omitted the current style value is returned.
				 * @return mixed The current style value if no $value is specified
				 */
				css : function($element, $style, $value)
				{
					var
						$matches;

					if ($style === 'float') {
						$style = ($element.style.cssFloat === undefined) ? 'styleFloat' : 'cssFloat';
					}

					if ($value === undefined || $value === null) {

						switch ($style) {
							case 'left': case 'top': case 'width': case 'height':
								$value = $element['offset' + ($style.charAt(0).toUpperCase() + $style.substr(1))];
								break;
							default:
								if ($element.style[$style]) {
									$value = $element.style[$style];
								} else if (window.getComputedStyle) {
									$value = window.getComputedStyle($element, null)[$style];
								} else if ($element.currentStyle[$style]) {
									$value = $element.currentStyle[$style];
								}
								break;
						}

						if ($value === undefined) {
							if ($style === 'opacity') {
								$value = 1;
							} else {
								$value = '';
							}

						} else if (typeof $value === 'string') {

							if (/^\d+\.?\d*$/.test($value)) {
								$value *= 1;

							} else if (($matches = $value.match(/^([\d\.]+)(\D+)$/))) {
								switch ($matches[2]) {
									case 'px': $value = $matches[1] * 1; break;
									case 'em': $value = Math.floor($matches[1] * ($style !== 'fontSize' ? arguments.callee($element, 'fontSize') : 1)); break;
									case 'pt': $value = Math.round($matches[1] * 1.33); break;
								}

							} else if (/[Cc]olor$/.test($style)) {
								if (($matches = $value.match(/^rgb\((\d+)\D+(\d+)\D+(\d+)/))) {
									$value = (($matches[1] << 16) + ($matches[2] << 8) + ($matches[3] * 1)).toString(16);
									$value = '#' + (new Array(7 - $value.length).join('0')) + $value;
								} else if (($matches = $value.match(/^#(\w{1})(\w{1})(\w{1})$/))) {
									$value = '#' + ($matches[1] + $matches[1]) + ($matches[2] + $matches[2]) + ($matches[3] + $matches[3]);
								} else {
									switch ($value.toLowerCase()) {
										case 'aqua':    $value = '#00ffff'; break;
										case 'black':   $value = '#000000'; break;
										case 'blue':    $value = '#0000ff'; break;
										case 'fuchsia': $value = '#ff00ff'; break;
										case 'gray':    $value = '#808080'; break;
										case 'green':   $value = '#008000'; break;
										case 'lime':    $value = '#00ff00'; break;
										case 'maroon':  $value = '#800000'; break;
										case 'navy':    $value = '#000080'; break;
										case 'olive':   $value = '#808000'; break;
										case 'purple':  $value = '#800080'; break;
										case 'red':     $value = '#ff0000'; break;
										case 'silver':  $value = '#c0c0c0'; break;
										case 'teal':    $value = '#008080'; break;
										case 'white':   $value = '#ffffff'; break;
										case 'yellow':  $value = '#ffff00'; break;
									}
								}
							}
						}
						return $value;

					} else {

						$element.style[$style] = $value;
						if ($style === 'opacity') {
							$element.style.filter = 'alpha(opacity=' + ($value * 100) + ')';
							$element.style.zoom   = 1;
						}
						return $element;

					}
				}
			}
		}
	);
