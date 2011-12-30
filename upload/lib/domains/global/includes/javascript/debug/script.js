
	$().loadModule(
		{
			name    : 'Debugging',
			version : '1.0.1',
			author  : 'Trevor Lewis - Thraddash Software',

			Element : {

				/**
				 * Displays element and child structures in a way thats readable by humans
				 * @param mixed $element The parent element to be displayed
				 * @param bool $html If specified, the result is formatted for use in html
				 * @return string
				 */
				print : function($element, $html)
				{
					var
						$results = '',
						$styles  = {
							bracket   : $html ? '<span style="color: #0000ad;">' : '',
							nodeName  : $html ? '<span style="color: #3e73ae;">' : '',
							attrName  : $html ? '<span style="color: #2d8c58;">' : '',
							attrValue : $html ? '<span style="color: #000000;">' : '',
							attrPunc  : $html ? '<span style="color: #000000;">' : '',
							text      : $html ? '<span style="color: #000000;">' : '',
							end       : $html ? '</span>' : ''
						},
						$attributes = {
							id        : 'id',
							className : 'class',
							type      : 'type',
							value     : 'value',
							href      : 'href',
							src       : 'src',
							checked   : 'checked',
							selected  : 'selected'
						};

					(function($element, $padding)
					{
						var
							$child,
							$text,
							$attr = '',
							$i;

						switch ($element.nodeType) {
							case 1:
								for ($i = 0, $child = 0, $text = 0; $i < $element.childNodes.length; $i++) {
									       if ($element.childNodes[$i].nodeType === 1) {$child++;
									} else if ($element.childNodes[$i].nodeType === 3) {$text++;}
								}
								for ($i in $attributes) {
									if ($attributes.hasOwnProperty($i) && $element[$i]) {
										$attr += ' ' + ($styles.attrName + $attributes[$i] + $styles.end) + ($styles.attrPunc + '="' + $styles.end) + ($styles.attrValue + $element[$i] + $styles.end) + ($styles.attrPunc + '"' + $styles.end);
									}
								}
								$results += $padding + ($styles.bracket + ($html ? '&lt;' : '<') + $styles.end) + ($styles.nodeName + $element.nodeName.toLowerCase() + $styles.end) + $attr + ($styles.bracket + ($child + $text ? '' : ' /') + ($html ? '&gt;' : '>') + $styles.end) + ($child ? '\n' : '');
								for ($i = 0; $i < $element.childNodes.length; $i++) {arguments.callee($element.childNodes[$i], ($child ? $padding + '    ' : ''));}
								$results += ($child ? $padding : '') + ($child + $text ? (($styles.bracket + ($html ? '&lt;' : '<') + '/' + $styles.end) + ($styles.nodeName + $element.nodeName.toLowerCase() + $styles.end) + ($styles.bracket + ($html ? '&gt;' : '>') + $styles.end)) : '') + '\n';
								break;
							case 3:
								$text = $element.nodeValue.replace(/^\s+|\s+$/g, '');
								if ($text) {$results += $padding + ($styles.text + $text + $styles.end) + ($padding ? '\n' : '');}
								break;
						}
					}($element, ''));
					return $html ? ('<pre>' + $results + '</pre>') : $results;
				}

			},

			Data : {

				/**
				 * Displays information about a variable in a way thats readable by humans
				 * @param mixed $expression The expression to be printed
				 * @param bool $html If specified, the result is wrapped in <pre> tags
				 * @return string
				 */
				print : function($expression, $html)
				{
					var
						$results = '';

					(function($expression, $padding)
					{
						var
							$matches,
							$i;

						if ($expression === null || $expression === undefined) {
							$results += '[' + $expression + ']\n';
						} else if (typeof $expression === 'function') {
							$results += 'function(' + (($matches = $expression.toString().replace(/\s*/g, '').match(/function\(([^\)]+)/)) ? $matches[1] : '') + ') {[native code]}\n';
						} else if ($expression.nodeType === 1) {
							$results += '[object HTML' + $expression.nodeName.toLowerCase() + 'Element]\n';
						} else if (typeof $expression === 'object') {
							$results += ($expression.length === undefined ? 'Object' : 'Array') + '\n' + $padding + '(\n';
							for ($i in $expression) {
								if ($expression.hasOwnProperty($i)) {
									$results += $padding + '    [' + $i + '] => ';
									arguments.callee($expression[$i], $padding + '        ');
								}
							}
							$results += $padding + ')\n';
						} else {
							$results += $expression + '\n';
						}
					}($expression, ''));
					return $html ? ('<pre>' + $results + '</pre>') : $results;
				}
			}
		}
	);
