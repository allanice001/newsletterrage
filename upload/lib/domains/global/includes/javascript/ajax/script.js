
	$().loadModule(
		{
			name    : 'AJAX',
			version : '1.0.0',
			author  : 'Trevor Lewis - Thraddash Software',

			Data : {

				/**
				 * Performs an asynchronous request to a specified URL, on response the callback functions are called passing the optional parameters
				 * @param string $url The local URL hosting the desired information
				 * @param mixed $options Options include: {post : {}, onComplete : function(){}, onSuccess : function(){}, onFailure : function(){}}
				 * @param mixed [...] All additional parameters will be passed to the callback functions
				 */
				request : function($url, $options)
				{
					var
						$http  = null,
						$post  = '',
						$param = Array.prototype.slice.call(arguments, 2),
						$i;

					if (window.XMLHttpRequest) {
						$http = new XMLHttpRequest();
					} else if (window.ActiveXObject) {
						try {
							$http = new ActiveXObject('Msxml2.XMLHTTP');
						} catch(e) {
							$http = new ActiveXObject('Microsoft.XMLHTTP');
						}
					}

					if ($http) {
						$options = (typeof $options === 'object') ? $options : {};
						$http.onreadystatechange = function()
						{
							if (/4|^complete$/.test($http.readyState)) {
							    if (typeof $options.onComplete === 'function') {$options.onComplete.apply($http, [$http.responseText].concat($param));}
								if (typeof $options['on' + (($http.status == 200) ? 'Success' : 'Failure')] === 'function') {$options['on' + (($http.status == 200) ? 'Success' : 'Failure')].apply($http, [$http.responseText].concat($param));}
							}
						};
						if ($options.post) {
							if (typeof $options.post === 'object') {
								for ($i in $options.post) {
									if ($options.post.hasOwnProperty($i)) {
										$post += ($post.length ? '&' : '') + $i + '=' + escape($options.post[$i]);
									}
								}
							} else {
								$post = $options.post;
							}
							$http.open('POST', $url, true);
							$http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							$http.send($post);
						} else {
							$http.open('GET', $url, true);
							$http.send(null);
						}
					}
				}
			}
		}
	);
