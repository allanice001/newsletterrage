
	$().loadModule(
		{
			name    : 'Events',
			version : '1.0.2',
			author  : 'Trevor Lewis - Thraddash Software',

			Element : {

				/**
				 * Attaches a callback function to an element event
				 * @param mixed $element The element object to attach the event
				 * @param string $event_name The elements event name, eg. click, mousemove
				 * @param mixed $callback_function The function to be called when the event is triggered, eg. doSomething, function doSomething(e, p) {}
				 * @param mixed [...] All additional parameters will be passed to the callback functions
				 * @return int Event ID
				 * @example
				 *     addEvent(document, "click", function(e, p1, p2) { [code] }, "param1", "param2");
				 */
				addEvent : function($element, $event_name, $callback_function)
				{
					var
						$event_id = 1315423911,
						$param    = Array.prototype.slice.call(arguments, 3),
						$i, $j;

					for ($i = 0, $j = ($event_name + $callback_function.toString()); $i < $j.length; $i++) {$event_id ^= (($event_id << 5) + $j.charCodeAt($i) + ($event_id >> 2));}

					if (!$element.Events) {$element.Events = {};}
					if (!$element.Events[$event_id]) {

						$element.Events[$event_id] = {
							en : $event_name,
							fn : function($event)
							{
								$event = $event || window.event;
								if (!($event.type === 'mouseover' || $event.type === 'mouseout') ? true :
									(function(){
										var $r = $event.relatedTarget || ($event.type === 'mouseover' ? $event.fromElement : $event.toElement);
										if ($r) {while ($r !== $element && ($r = $r.parentNode)) {}} return $r !== $element;
									}())
								) {
									$callback_function.apply(
										$element,
										[
											{
												id      : $event_id,
												keyCode : $event.which || $event.keyCode,
												wheel   : $event.wheelDelta ? $event.wheelDelta / 120 : ($event.detail ? -$event.detail / 3 : 0),
												pageX   : $event.pageX || ($event.clientX + (document.body.scrollLeft || document.documentElement.scrollLeft) - document.documentElement.clientLeft),
												pageY   : $event.pageY || ($event.clientY + (document.body.scrollTop  || document.documentElement.scrollTop)  - document.documentElement.clientTop),
												clientX : $event.clientX,
												clientY : $event.clientY,
												cancelBubble  : function() {if ($event.stopPropagation) {$event.stopPropagation();} else {$event.cancelBubble = true;}},
												cancelDefault : function() {if ($event.preventDefault)  {$event.preventDefault();}  else {$event.returnValue  = false;}},
												cancelEvent   : function() {if (typeof $element.removeEvent === 'function') {$element.removeEvent(this.id);}}
											}
										].concat($param)
									);
								}
							}
						};

						if ($element.attachEvent) {
							$element.attachEvent('on' + $event_name, $element.Events[$event_id].fn);
						} else if ($element.addEventListener) {
							if ($event_name == 'mousewheel') {$element.addEventListener('DOMMouseScroll', $element.Events[$event_id].fn, false);}
							$element.addEventListener($event_name, $element.Events[$event_id].fn, false);
						}
					}
					return $event_id;
				},

				/**
				 * Detaches a callback function from an Element
				 * @param mixed $element The element object to detach the event
				 * @param mixed $event_name The elements event name OR the event id returned by addEvent. If omitted all events will be detached.
				 * @param mixed $callback_function The function to be removed from the element. If omitted all events by $event_name will be detached.
				 */
				removeEvent : function($element, $event_name, $callback_function)
				{
					var
						$event_id = 0,
						$i, $j;

					if ($element.Events) {
						if (!$event_name) {
							for ($event_id in $element.Events) {
								if ($element.Events.hasOwnProperty($event_id)) {
									arguments.callee($element, $event_id, null);
								}
							}

						} else if ($element.Events[$event_name]) {
							if ($element.detachEvent) {
								$element.detachEvent('on' + $element.Events[$event_name].en, $element.Events[$event_name].fn);
							} else if ($element.removeEventListener) {
								if ($element.Events[$event_name].en == 'mousewheel') {$element.removeEventListener('DOMMouseScroll', $element.Events[$event_name].fn, false);}
								$element.removeEventListener($element.Events[$event_name].en, $element.Events[$event_name].fn, false);
							}
							delete $element.Events[$event_name];

						} else if ($callback_function) {
							$event_id = 1315423911;
							for ($i = 0, $j = ($event_name + $callback_function.toString()); $i < $j.length; $i++) {$event_id ^= (($event_id << 5) + $j.charCodeAt($i) + ($event_id >> 2));}
							arguments.callee($element, $event_id, null);

						} else {
							for ($event_id in $element.Events) {
								if ($element.Events.hasOwnProperty($event_id) && ($element.Events[$event_id].en == $event_name)) {
									arguments.callee($element, $event_id, null);
								}
							}

						}
					}
				}
			}
		}
	);
