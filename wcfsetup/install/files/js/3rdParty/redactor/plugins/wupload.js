if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Handles drag&drop upload using the attachment system for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wupload = function() {
	"use strict";
	
	return {
		_boundGlobalUploadEvents: false,
		_dropArea: { },
		_timer: null,
		_isDragging: false,
		_isFile: false,
		
		/**
		 * Initializes the RedactorPlugins.wupload plugin.
		 */
		init: function() {
			var $namespace = '.redactor_' + this.$textarea.wcfIdentify();
			$(document).on('dragover' + $namespace, $.proxy(this.wupload._dragOver, this));
			$(document).on('dragleave' + $namespace, $.proxy(this.wupload._dragLeave, this));
			$(document).on('drop' + $namespace, (function(event) {
				event.preventDefault();
				
				this.wupload._revertDropArea(undefined, this.$textarea.wcfIdentify());
			}).bind(this));
			
			if (!this.wupload._boundGlobalUploadEvents) {
				this.wupload._boundGlobalUploadEvents = true;
				
				$(document).on('dragend', function(event) { event.preventDefault(); });
			}
			
			WCF.System.Event.addListener('com.woltlab.wcf.attachment', 'autoInsert_' + this.$textarea.wcfIdentify(), $.proxy(this.wupload.insertPastedImageAttachment, this));
		},
		
		/**
		 * Handles an actively dragged object.
		 * 
		 * @param	object		event
		 */
		_dragOver: function(event) {
			event = event.originalEvent;
			
			if (!this.$editor.is(':visible')) {
				return;
			}
			
			if (!event.dataTransfer || !event.dataTransfer.types) {
				return;
			}
			
			var $isFirefox = false;
			for (var $property in event.dataTransfer) {
				if (/^moz/.test($property)) {
					$isFirefox = true;
					break;
				}
			}
			
			// IE and WebKit set 'Files', Firefox sets 'application/x-moz-file' for files being dragged
			// and Safari just provides 'Files' along with a huge list of other stuff
			this.wupload._isFile = false;
			if ($isFirefox) {
				// Firefox sets the 'Files' type even if the user is just dragging an on-page element
				if (event.dataTransfer.types[0] === 'application/x-moz-file') {
					this.wupload._isFile = true;
				}
			}
			else {
				for (var $i = 0; $i < event.dataTransfer.types.length; $i++) {
					if (event.dataTransfer.types[$i] === 'Files') {
						this.wupload._isFile = true;
						break;
					}
				}
			}
			
			if (!this.wupload._isFile) {
				return;
			}
			
			this.wupload._isFile = true;
			event.preventDefault();
			
			if (!this.wupload._isDragging) {
				var $containerID = this.$textarea.wcfIdentify();
				
				if (this.wupload._dropArea[$containerID] === undefined) {
					this.wupload._dropArea[$containerID] = $('<div class="redactorDropArea">' + WCF.Language.get('wcf.attachment.dragAndDrop.dropHere') + '</div>').hide().appendTo(document.body);
					this.wupload._dropArea[$containerID].on('dragover', $.proxy(this.wupload._hoverDropArea, this)).on('dragleave', $.proxy(this.wupload._revertDropArea, this)).on('drop', $.proxy(this.wupload._drop, this));
				}
				
				// adjust dimensions
				var $dimensions = (this.wutil.inWysiwygMode()) ? this.$editor.getDimensions('outer') : this.$textarea.getDimensions('outer');
				var $position = (this.wutil.inWysiwygMode()) ? this.$editor.getOffsets('offset') : this.$textarea.getOffsets('offset');
				
				this.wupload._dropArea[$containerID].css({
					height: $dimensions.height + 'px',
					left: $position.left + 'px',
					lineHeight: $dimensions.height + 'px',
					top: $position.top + 'px',
					width: $dimensions.width + 'px'
				}).show();
				
				this.wupload._isDragging = true;
			}
			
			event.preventDefault();
		},
		
		/**
		 * Visualizes the drop area being hovered.
		 * 
		 * @param	object		event
		 */
		_hoverDropArea: function(event) {
			this.wupload._dropArea[this.$textarea.wcfIdentify()].addClass('active').text(WCF.Language.get('wcf.attachment.dragAndDrop.dropNow'));
		},
		
		/**
		 * Reverts the drop area into the initial state.
		 * 
		 * @param	object		event
		 * @param	string		containerID
		 */
		_revertDropArea: function(event, containerID) {
			if (!this.wupload._isFile) {
				return;
			}
			
			var $containerID = containerID || this.$textarea.wcfIdentify();
			this.wupload._dropArea[$containerID].removeClass('active').text(WCF.Language.get('wcf.attachment.dragAndDrop.dropHere'));
			
			if (containerID) {
				this.wupload._dropArea[$containerID].hide();
			}
		},
		
		/**
		 * Handles the object no longer being dragged.
		 * 
		 * This event can fires whenever an object is hovering over a different element, there is
		 * a delay of 100ms before the dragging will be checked again to prevent flicker.
		 */
		_dragLeave: function() {
			if (!this.wupload._isDragging || !this.wupload._isFile) {
				return;
			}
			
			if (this.wupload._timer === null) {
				this.wupload._timer = new WCF.PeriodicalExecuter((function(pe) {
					pe.stop();
					
					if (!this.wupload._isDragging) {
						this.wupload._dropArea[this.$textarea.wcfIdentify()].hide();
					}
				}).bind(this), 100);
			}
			else {
				this.wupload._timer.resume();
			}
			
			this.wupload._isDragging = false;
		},
		
		/**
		 * Handles the drop of the dragged object.
		 * 
		 * @param	object		event
		 */
		_drop: function(event) {
			if (!this.wupload._isFile) {
				return;
			}
			
			event = event.originalEvent || event;
			
			if (event.dataTransfer && event.dataTransfer.files.length) {
				event.preventDefault();
				
				// reset overlay
				var $containerID = this.$textarea.wcfIdentify();
				this.wupload._revertDropArea(undefined, $containerID);
				
				for (var $i = 0; $i < event.dataTransfer.files.length; $i++) {
					WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'upload_' + $containerID, { file: event.dataTransfer.files[$i] });
				}
			}
		},
		
		/**
		 * Overwrites $.Redactor.pasteClipboardUploadMozilla() to upload files as attachments.
		 * 
		 * @see		$.Redactor.pasteClipboardUploadMozilla()
		 */
		pasteClipboardUploadMozilla: function() {
			this.$editor.find('img[data-mozilla-paste-image]').each($.proxy(function(index, image) {
				var $image = $(image);
				var $src = $image.prop('src').split(',');
				var $contentType = $src[0].split(';')[0].split(':')[1];
				var $data = $src[1]; // raw base64
				
				var $eventData = {
					blob: WCF.base64toBlob($data, $contentType),
					uploadID: null
				};
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'upload_' + this.$textarea.wcfIdentify(), $eventData);
				
				// drop image
				$image.replaceWith('<span class="redactor-pastedImageFromClipboard-' + $eventData.uploadID + '" />');
			}, this));
		},
		
		/**
		 * Inserts the attachment at the placeholder location.
		 * 
		 * @param	object		data
		 */
		insertPastedImageAttachment: function(data) {
			var $placeholder = this.$editor.find('span.redactor-pastedImageFromClipboard-' + data.uploadID);
			$placeholder.before(data.attachment);
			$placeholder.remove();
		}
	};
};
