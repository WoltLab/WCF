$.Redactor.prototype.WoltLabMedia = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabMedia', 'Media');
			$(button).attr('id', 'mediaManagerButton');
			
			require(['WoltLab/WCF/Media/Manager'], function(MediaManager) {
				new MediaManager();
			});
		},
	};
};
