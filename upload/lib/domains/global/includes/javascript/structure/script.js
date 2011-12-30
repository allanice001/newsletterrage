
	$().loadModule(
		{
			name    : 'Structure',
			version : '1.0.2',
			author  : 'Trevor Lewis - Thraddash Software',

			Data : {

				/**
				 * Converts and encodes Javascript memory into a string for storage or transmission
				 * @param mixed $data The Javascript variable to be converted into a string
				 * @return string
				 */
				pack : function($data)
				{
					var
						$string,
						$result = '',
						$i;

					$string = this.serialize($data);
					for ($i = 0; $i < $string.length; $i++) {
						$result += String.fromCharCode($string.charCodeAt($i) ^ ($i % 5));
					}
					return '<!--<DATA-START>' + escape($result) + '<DATA-END>-->';
				},

				/**
				 * Decodes then restores previously packed data back into Javascript memory
				 * @param string $data The packed data to be converted back into Javascript
				 * @return mixed
				 */
				unpack : function($data)
				{
					var
						$data_start = $data.indexOf('<DATA-START>') + 12,
						$data_end   = $data.indexOf('<DATA-END>'),
						$string     = '',
						$i;

					if ($data_start < $data_end) {
						$data = unescape($data.substring($data_start, $data_end));
						for ($i = 0; $i < $data.length; $i++) {
							$string += String.fromCharCode($data.charCodeAt($i) ^ ($i % 5));
						}
						return this.unserialize($string);
					}
					return '';
				},

				/**
				 * Generates a storable representation of a value
				 * @param mixed $data The value to be serialized
				 * @return string
				 */
				serialize : function($data)
				{
					var
						$result = '',
						$count  = 0,
						$key;

					if ($data === null) {
						return 'N;';
					} else {
						switch (typeof $data) {
							case 'boolean':
								return 'b:' + ($data ? 1 : 0) + ';';
							case 'number':
								return (/^\d+$/.test($data) ? 'i' : 'd') + ':' + $data + ';';
							case 'string':
								return 's:' + $data.length + ':"' + $data + '";';
							case 'object':
								for ($key in $data) {
									if ($data.hasOwnProperty($key)) {
										$result += arguments.callee((/^\d+$/).test($key) ? (1 * $key) : $key) + arguments.callee($data[$key]);
										$count++;
									}
								}
								return 'a:' + $count + ':{' + $result + '}';
						}
					}
				},

				/**
				 * Creates a Javascript value from a stored representation
				 * @param string $string The serialized string
				 * @return mixed
				 */
				unserialize : function($string)
				{
					var
						$position = 0;

					return (typeof $string === 'string' ? (function()
					{
						var
							$result = '',
							$type   = $string.substr($position, 1),
							$pos, $length, $i;

						$position += 2;

						       if ('N' === $type) {
							return null;
						} else if ('b' === $type) {
							$result    = '1' == $string.charAt($position);
							$position += 2;
						} else if ('i' === $type || 'd' === $type) {
							$pos      = $string.indexOf(';', $position);
							$result   = 1 * $string.substring($position, $pos);
							$position = $pos + 1;
						} else if ('s' === $type) {
							$pos      = $string.indexOf(':', $position);
							$length   = 1 * $string.substring($position, $pos);
							$result   = $string.substr($pos + 2, $length);
							$position = $pos + $length + 4;
						} else if ('a' === $type) {
							$pos      = $string.indexOf(':', $position);
							$length   = 1 * $string.substring($position, $pos);
							$position = $pos + 2;
							$result   = [];
							for ($i = 0; $i < $length; $i++) {
								$result[arguments.callee()] = arguments.callee();
							}
							$position++;
						}
						return $result;
					}()) : '');
				}
			}
		}
	);
