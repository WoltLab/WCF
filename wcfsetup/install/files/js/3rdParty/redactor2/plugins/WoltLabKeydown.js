$.Redactor.prototype.WoltLabKeydown = function() {
	"use strict";
	
	var _tags = [];
	
	return {
		init: function () {
			this.keydown.onArrowDown = (function() {
				var tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					if (tags[i]) {
						this.keydown.insertAfterLastElement(tags[i]);
						return false;
					}
				}
			}).bind(this);
			
			this.keydown.onArrowUp = (function() {
				var tags = this.WoltLabKeydown._getBlocks();
				
				for (var i = 0; i < tags.length; i++) {
					if (tags[i]) {
						this.keydown.insertBeforeFirstElement(tags[i]);
						return false;
					}
				}
			}).bind(this);
		},
		
		register: function (tag) {
			if (_tags.indexOf(tag) === -1) {
				_tags.push(tag);
			}
		},
		
		_getBlocks: function () {
			var tags = [this.keydown.blockquote, this.keydown.pre, this.keydown.figcaption];
			
			for (var i = 0, length = _tags.length; i < length; i++) {
				tags.push(this.utils.isTag(this.keydown.current, _tags[i]))
			}
			
			return tags;
		}
	}
};
