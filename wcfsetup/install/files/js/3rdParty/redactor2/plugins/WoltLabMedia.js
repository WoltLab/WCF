$.Redactor.prototype.WoltLabMedia = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabMedia', 'Media');
			$(button).addClass('jsMediaEditorButton');
			
			require(['WoltLab/WCF/Media/Manager/Editor'], function(MediaManagerEditor) {
				new MediaManagerEditor();
			});
		},
	};
};
