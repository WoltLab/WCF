$.Redactor.prototype.WoltLabMedia = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabMedia', '');
			$(button).addClass('jsMediaEditorButton');
			
			require(['WoltLab/WCF/Media/Manager/Editor'], function(MediaManagerEditor) {
				new MediaManagerEditor({
					editor: this
				});
			}.bind(this));
		},
	};
};
