$.Redactor.prototype.WoltLabIndent = function() {
	"use strict";
	
	return {
		init: function () {
			this.indent.normalize = (function() {
				this.core.editor().find('li').each($.proxy(function (i, s) {
					var $el = $(s);
					
					// remove style
					var filter = '';
					if (this.opts.keepStyleAttr.length !== 0) {
						filter = ',' + this.opts.keepStyleAttr.join(',');
					}
					
					// WoltLab modification: exclude <span> from style purification
					$el.find(this.opts.inlineTags.join(',')).not('img' + filter).not('span').removeAttr('style');
					
					var $parent = $el.parent();
					if ($parent.length !== 0 && $parent[0].tagName === 'LI') {
						$parent.after($el);
						return;
					}
					
					var $next = $el.next();
					if ($next.length !== 0 && ($next[0].tagName === 'UL' || $next[0].tagName === 'OL')) {
						$el.append($next);
					}
					
				}, this));
			}).bind(this);
		}
	};
};
