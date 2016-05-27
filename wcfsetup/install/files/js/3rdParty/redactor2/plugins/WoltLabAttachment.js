$.Redactor.prototype.WoltLabAttachment = function() {
	"use strict";
	
	return {
		init: function() {
			require(['EventHandler'], (function(EventHandler) {
				EventHandler.add('com.woltlab.wcf.redactor2', 'insertAttachment_' + this.$element[0].id, this.WoltLabAttachment._insert.bind(this))
			}).bind(this));
		},
		
		_insert: function(data) {
			var attachmentId = data.attachmentId;
			
			if (data.url) {
				this.insert.html('<img src="' + data.url + '" class="woltlabAttachment" data-attachment-id="' + attachmentId + '">');
			}
			else {
				// non-image attachment
				this.insert.text('[attach=' + attachmentId + '][/attach]');
			}
		}
	};
};