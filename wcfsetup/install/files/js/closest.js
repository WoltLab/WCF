/*
 * Polyfill for `Element.prototype.matches()` and `Element.prototype.closest()`
 * Copyright (c) 2015 Jonathan Neal - https://github.com/jonathantneal/closest
 * License: CC0 1.0 Universal (https://creativecommons.org/publicdomain/zero/1.0/)
 */
(function(ELEMENT) {
	ELEMENT.matches = ELEMENT.matches || ELEMENT.mozMatchesSelector || ELEMENT.msMatchesSelector || ELEMENT.oMatchesSelector || ELEMENT.webkitMatchesSelector;
	
	ELEMENT.closest = ELEMENT.closest || function closest(selector) {
			var element = this;
			
			while (element) {
				if (element.matches(selector)) {
					break;
				}
				
				element = element.parentElement;
			}
			
			return element;
		};
}(Element.prototype));
