if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Handles drag&drop upload using the attachment system for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wupload = {
	_boundGlobalUploadEvents: false,
	_wUploadDropArea: { },
	_timer: null,
	_isDragging: false,
	
	/**
	 * Initializes the RedactorPlugins.wupload plugin.
	 */
	init: function() {
		var $namespace = '.redactor_' + this.$source.wcfIdentify();
		$(document).on('dragover' + $namespace, $.proxy(this._dragOver, this));
		$(document).on('dragleave' + $namespace, $.proxy(this._dragLeave, this));
		$(document).on('drop' + $namespace, $.proxy(function(event) {
			event.preventDefault();
			
			this._revertDropArea(undefined, this.$source.wcfIdentify());
		}, this));
		
		if (!this._boundGlobalUploadEvents) {
			this._boundGlobalUploadEvents = true;
			
			$(document).on('dragend', function(event) { event.preventDefault(); });
		}
	},
	
	/**
	 * Handles an actively dragged object.
	 * 
	 * @param	object		event
	 */
	_dragOver: function(event) {
		event = event.originalEvent;
		
		if (!event.dataTransfer || !event.dataTransfer.types) {
			return;
		}
		
		// IE and WebKit set 'Files', Firefox sets 'application/x-moz-file' for files being dragged
		if (event.dataTransfer.types[0] !== 'Files' && event.dataTransfer.types[0] !== 'application/x-moz-file') {
			return;
		}
		
		event.preventDefault();
		
		if (!this._isDragging) {
			var $containerID = this.$source.wcfIdentify();
			
			if (this._wUploadDropArea[$containerID] === undefined) {
				this._wUploadDropArea[$containerID] = $('<div class="redactorDropArea">' + WCF.Language.get('wcf.attachment.dragAndDrop.dropHere') + '</div>').hide().appendTo(document.body);
				this._wUploadDropArea[$containerID].on('dragover', $.proxy(this._hoverDropArea, this)).on('dragleave', $.proxy(this._revertDropArea, this)).on('drop', $.proxy(this._drop, this));
			}
			
			// adjust dimensions
			var $dimensions = (this.inWysiwygMode()) ? this.$editor.getDimensions('outer') : this.$source.getDimensions('outer');
			var $position = (this.inWysiwygMode()) ? this.$editor.getOffsets('offset') : this.$source.getOffsets('offset');
			
			this._wUploadDropArea[$containerID].css({
				height: $dimensions.height + 'px',
				left: $position.left + 'px',
				lineHeight: $dimensions.height + 'px',
				top: $position.top + 'px',
				width: $dimensions.width + 'px'
			}).show();
			
			this._isDragging = true;
		}
		
		event.preventDefault();
	},
	
	/**
	 * Visualizes the drop area being hovered.
	 * 
	 * @param	object		event
	 */
	_hoverDropArea: function(event) {
		this._wUploadDropArea[this.$source.wcfIdentify()].addClass('active').text(WCF.Language.get('wcf.attachment.dragAndDrop.dropNow'));
	},
	
	/**
	 * Reverts the drop area into the initial state.
	 * 
	 * @param	object		event
	 * @param	string		containerID
	 */
	_revertDropArea: function(event, containerID) {
		var $containerID = containerID || this.$source.wcfIdentify();
		this._wUploadDropArea[$containerID].removeClass('active').text(WCF.Language.get('wcf.attachment.dragAndDrop.dropHere'));
		
		if (containerID) {
			this._wUploadDropArea[$containerID].hide();
		}
	},
	
	/**
	 * Handles the object no longer being dragged.
	 * 
	 * This event can fires whenever an object is hovering over a different element, there is
	 * a delay of 100ms before the dragging will be checked again to prevent flicker.
	 */
	_dragLeave: function() {
		if (this._timer === null) {
			var self = this;
			this._timer = new WCF.PeriodicalExecuter(function(pe) {
				pe.stop();
				
				if (!self._isDragging) {
					self._wUploadDropArea[self.$source.wcfIdentify()].hide();
				}
			}, 100);
		}
		else {
			this._timer.resume();
		}
		
		this._isDragging = false;
	},
	
	/**
	 * Handles the drop of the dragged object.
	 * 
	 * @param	object		event
	 */
	_drop: function(event) {
		event = event.originalEvent || event;
		
		if (event.dataTransfer && event.dataTransfer.files.length == 1) {
			event.preventDefault();
			
			// reset overlay
			var $containerID = this.$source.wcfIdentify();
			this._revertDropArea(undefined, $containerID);
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'upload_' + $containerID, { file: event.dataTransfer.files[0] });
		}
	}
};
