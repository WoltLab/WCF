$.Redactor.prototype.WoltLabMedia = function() {
	"use strict";
	
	return {
		init: function() {
			var button = this.button.add('woltlabMedia', '');
			$(button).addClass('jsMediaEditorButton');
			
			var metacodeUuid = WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'metacode_wsm_' + this.$element[0].id, (function(data) {
				if (data.attributes.length === 1) {
					// ignore non-image files
					return;
				}
				
				var float = '';
				if (data.attributes === 3) {
					if (data.attributes[2] === 'left') {
						float = ' messageFloatObjectLeft';
					}
					else if (data.attributes[2] === 'right') {
						float = ' messageFloatObjectRight';
					}
				}
				
				var image = elCreate('img');
				image.className = 'woltlabSuiteMedia' + float;
				//noinspection JSUnresolvedVariable
				image.src = this.opts.woltlab.mediaUrl.replace('-123456789', data.attributes[0]).replace('thumbnail=void', 'thumbnail=' + data.attributes[1]);
				elData(image, 'media-id', data.attributes[0]);
				elData(image, 'media-size', data.attributes[1]);
				
				var metacode = data.metacode;
				metacode.parentNode.insertBefore(image, metacode);
				elRemove(metacode);
				
				data.cancel = true;
			}).bind(this));
			
			WCF.System.Event.addListener('com.woltlab.wcf.redactor2', 'destroy_' + this.$element[0].id, (function () {
				WCF.System.Event.removeListener('com.woltlab.wcf.redactor2', 'metacode_wsm_' + this.$element[0].id, metacodeUuid);
			}).bind(this));
			
			require(['WoltLabSuite/Core/Media/Manager/Editor'], function(MediaManagerEditor) {
				new MediaManagerEditor({
					editor: this
				});
			}.bind(this));
		},
	};
};
