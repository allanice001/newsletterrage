
	$().loadModule(
		{
			name    : 'Element Functions',
			version : '1.0.1',
			author  : 'Trevor Lewis - Thraddash Software',

			Element : {

				/**
				 * Creates an instance of the element object for the specified tag.
				 * @param mixed $parent The object to parent the new element.
				 * @param string $tag The type of element to instance eg. div, img
				 * @param mixed $properties An object specifying new or replacement properties for the element eg. {innerHTML : 'Content', onclick : function() {}}
				 * @param mixed $styles An object specifying new or replacement styles for the element
				 * @return mixed The new element object.
				 */
				addElement : function($parent, $tag, $properties, $styles)
				{
					var
						$item    = null,
						$element = null;

					if (($tag === 'input') && (typeof $properties === 'object') && ($properties.type === 'radio')) {
						try {
							$element = document.createElement('<input type="radio"' + ($properties.name ? ' name="' + $properties.name + '"' : '') + ' />');
						} catch(e) {
							$element = document.createElement($tag);
						}
					} else {
						$element = document.createElement($tag || 'div');
					}

					if (typeof $properties === 'object') {
						for ($item in $properties) {
							if ($properties.hasOwnProperty($item)) {
								$element[$item] = $properties[$item];
							}
						}
					}
					if (typeof $styles === 'object') {
						for ($item in $styles) {
							if ($styles.hasOwnProperty($item)) {
								$element.style[$item] = $styles[$item];
							}
						}
					}
					if ($parent) {
						$parent.appendChild($element);
					}
					return $($element);
				},

				/**
				 * Destroys a specified element object, recursing through all child elements and events.
				 * @param mixed $element The element object to destroy.
				 */
				removeElement : function($element)
				{
					for (var $j = $element.childNodes, $i = $j.length - 1; $i >= 0; $i--) {
						arguments.callee($j[$i]);
					}
					if (typeof $element.removeEvent === 'function') {
						$element.removeEvent();
					}
					if ($element.parentNode) {
						$element.parentNode.removeChild($element);
					}
				},

				/**
				 * Retrieves the dimensions of the bounding rectangle for the specified element. The dimensions are given in screen coordinates that are relative to the upper-left corner of the screen.
				 * @param mixed $element Identifies the element.
				 * @return mixed An object containing the upper-left and lower-right corners of the element in pixels.
				 */
				getRect : function($element)
				{
					var
						$rect = {
							left   : $element.offsetLeft,
							top    : $element.offsetTop,
							right  : $element.offsetWidth,
							bottom : $element.offsetHeight
						};

					while (($element = $element.offsetParent)) {
						$rect.left += $element.offsetLeft;
						$rect.top  += $element.offsetTop;
					}
					$rect.right  += $rect.left;
					$rect.bottom += $rect.top;
					return $rect;
				},

				/**
				 * Returns an object containing all submittable form element names and values
				 * @param mixed $element The element containing the form elements, usually a <form>
				 * @return mixed
				 */
				getSubmitValues : function($element)
				{
					var
						$fields = {};

					(function($element)
					{
						if ($element.name && !$element.disabled) {
							switch ($element.nodeName.toUpperCase()) {
								case 'INPUT':
									switch ($element.type.toUpperCase()) {
										case 'RADIO':
										case 'CHECKBOX':
											if ($element.checked) {
												$fields[$element.name] = $element.value;
											}
											break;
										default:
											$fields[$element.name] = $element.value;
											break;
									}
									break;
								case 'SELECT':
								case 'TEXTAREA':
									$fields[$element.name] = $element.value;
									break;
							}
						}
						for (var $i = 0; $i < $element.childNodes.length; $i++) {
							arguments.callee($element.childNodes[$i]);
						}
					}($element));

					return $fields;
				}
			}
		}
	);
