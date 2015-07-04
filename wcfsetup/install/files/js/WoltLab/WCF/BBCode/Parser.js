define([], function() {
	"use strict";
	
	var BBCodeParser = {
		parse: function(message) {
			var stack = this._splitTags(message);
			this._buildLinearTree(stack);
			
			return stack;
		},
		
		_splitTags: function(message) {
			// TODO: `validTags` should be dynamic similar to the PHP implementation
			var validTags = __REDACTOR_BBCODES.join('|');
			var pattern = '(\\\[(?:/(?:' + validTags + ')|(?:' + validTags + ')'
				+ '(?:='
					+ '(?:\\\'[^\\\'\\\\]*(?:\\\\.[^\\\'\\\\]*)*\\\'|[^,\\\]]*)'
					+ '(?:,(?:\\\'[^\\\'\\\\]*(?:\\\\.[^\\\'\\\\]*)*\'|[^,\\\]]*))*'
				+ ')?)\\\])';
			
			var isBBCode = new RegExp('^' + pattern + '$', 'i');
			var part, parts = message.split(new RegExp(pattern, 'i')), stack = [], tag;
			for (var i = 0, length = parts.length; i < length; i++) {
				part = parts[i];
				
				if (part === '') {
					continue;
				}
				else if (part.match(isBBCode)) {
					tag = { name: '', closing: false, attributes: [], source: part };
					
					if (part[1] === '/') {
						tag.name = part.substring(2, part.length - 1);
						tag.closing = true;
					}
					else if (part.match(/^\[([a-z0-9]+)=?(.*)\]$/i)) {
						tag.name = RegExp.$1;
						
						if (RegExp.$2) {
							tag.attributes = this._parseAttributes(RegExp.$2);
						}
					}
					
					stack.push(tag);
				}
				else {
					stack.push(part);
				}
			}
			
			return stack;
		},
		
		_buildLinearTree: function(stack) {
			var item, openTags = [], reopenTags, sourceBBCode = '', tag;
			for (var i = 0; i < stack.length; i++) { // do not cache stack.length, its size is dynamic
				item = stack[i];
				
				if (typeof item === 'object') {
					if (sourceBBCode.length && (item.name !== sourceBBCode || !item.closing)) {
						stack[i] = item.source;
						continue;
					}
					
					if (item.closing) {
						if (this._hasOpenTag(openTags, item.name)) {
							reopenTags = this._closeUnclosedTags(stack, openTags, item.name);
							for (var j = 0, innerLength = reopenTags.length; j < innerLength; j++) {
								stack.splice(i, reopenTags[j]);
								i++;
							}
							
							openTags.pop().pair = i;
						}
						else {
							// tag was never opened, treat as plain text
							stack[i] = item.source;
						}
						
						if (sourceBBCode === item.name) {
							sourceBBCode = '';
						}
					}
					else {	
						openTags.push(item);
						
						if (__REDACTOR_SOURCE_BBCODES.indexOf(item.name) !== -1) {
							sourceBBCode = item.name;
						}
					}
				}
			}
			
			// close unclosed tags
			this._closeUnclosedTags(stack, openTags, '');
		},
		
		_closeUnclosedTags: function(stack, openTags, until) {
			var item, reopenTags = [], tag;
			
			for (var i = openTags.length - 1; i >= 0; i--) {
				item = openTags[i];
				
				if (item.name === until) {
					break;
				}
				
				tag = { name: item.name, closing: true, attributes: item.attributes.slice(), source: '[/' + item.name + ']' };
				item.pair = stack.length;
				
				stack.push(tag);
				
				openTags.pop();
				reopenTags.push({ name: item.name, closing: false, attributes: item.attributes.slice(), source: item.source });
			}
			
			return reopenTags.reverse();
		},
		
		_hasOpenTag: function(openTags, name) {
			for (var i = openTags.length - 1; i >= 0; i--) {
				if (openTags[i].name === name) {
					return true;
				}
			}
			
			return false;
		},
		
		_parseAttributes: function(attrString) {
			var tmp = attrString.split(/(?:^|,)('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'|[^,]*)/g);
			
			var attribute, attributes = [];
			for (var i = 0, length = tmp.length; i < length; i++) {
				attribute = tmp[i];
				
				if (attribute !== '') {
					if (attribute.charAt(0) === "'" && attribute.substr(-1) === "'") {
						attributes.push(attribute.substring(1, attribute.length - 1).trim());
					}
					else {
						attributes.push(attribute.trim());
					}
				}
			}
			
			return attributes;
		}
	};
	
	return BBCodeParser;
});
