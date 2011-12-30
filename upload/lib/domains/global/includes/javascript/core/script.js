
	var $_FRAMEWORK = {
		Modules : {}
	};

	function $($expression)
	{
		var
			$element, $element_node_name,
			$module, $method, $matches,
			$function_wrapper,
			$framework = $_FRAMEWORK;

		if ($expression === null || $expression === undefined) {
			return {
				version    : '1.0.2',
				author     : 'Trevor Lewis - Thraddash Software',
				loadModule : function($module)
				{
					if ((typeof $module === 'object') && $module.name && !$framework.Modules[$module.name]) {
						$framework.Modules[$module.name] = $module;
					}
				}
			};

		} else if ($expression === '#') {
			document.write('<span id="' + ($element_node_name = 'TMP' + Math.floor(Math.random() * 100000)) + '"></span>');
			$element = document.getElementById($element_node_name).parentNode;
			$element.removeChild(document.getElementById($element_node_name));
			return arguments.callee($element);

		} else if (($element = ((typeof $expression === 'string') && ($matches = $expression.match(/^#(\S+)\s*(.*)$/))) ? document.getElementById($matches[1]) : false)) {
			return arguments.callee($element);

		} else if (($element = ((typeof $expression === 'object' && $expression.nodeType === 1) || $expression === document || $expression === window) ? $expression : false)) {
			if (!$element._elementExtended) {
				$element_node_name = 'Element' + String($element.nodeName ? $element.nodeName : 'WINDOW').replace(/[^\w]/g, '').toUpperCase();
				$function_wrapper  = function($function) {return function() {return $function.apply(this, [this].concat(Array.prototype.slice.call(arguments)));};};
				for ($module in $framework.Modules) {
					if ($framework.Modules.hasOwnProperty($module)) {
						if ($framework.Modules[$module][$element_node_name]) {
							for ($method in $framework.Modules[$module][$element_node_name]) {
								if ($framework.Modules[$module][$element_node_name].hasOwnProperty($method) && !$element[$method]) {
									$element[$method] = $function_wrapper($framework.Modules[$module][$element_node_name][$method]);
								}
							}
						}
						if ($framework.Modules[$module].Element) {
							for ($method in $framework.Modules[$module].Element) {
								if ($framework.Modules[$module].Element.hasOwnProperty($method) && !$element[$method]) {
									$element[$method] = $function_wrapper($framework.Modules[$module].Element[$method]);
								}
							}
						}
					}
				}
				$element._elementExtended = true;
			}
			return $element;

		} else {
			$element = {};
			$function_wrapper = function($module, $function, $data) {return function() {return $function.apply($module, [$data].concat(Array.prototype.slice.call(arguments)));};};
			for ($module in $framework.Modules) {
				if ($framework.Modules.hasOwnProperty($module) && $framework.Modules[$module].Data) {
					for ($method in $framework.Modules[$module].Data) {
						if ($framework.Modules[$module].Data.hasOwnProperty($method)) {
							$element[$method] = $function_wrapper($framework.Modules[$module].Data, $framework.Modules[$module].Data[$method], $expression);
						}
					}
				}
			}
			return $element;

		}
	}
