/**
 * Drag and Drop file uploads.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/DragAndDrop
 */
define(['Dictionary', 'EventHandler', 'Language'], function (Dictionary, EventHandler, Language) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_dragOver: function() {},
			_drop: function() {},
			_dragLeave: function() {},
			_setup: function() {}
		};
		return Fake;
	}
	
	var _didInit = false;
	var _dragArea = new Dictionary();
	var _isDragging = false;
	var _isFile = false;
	var _timerLeave = null;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Redactor/DragAndDrop
	 */
	return {
		/**
		 * Initializes drag and drop support for provided editor instance.
		 * 
		 * @param       {$.Redactor}    editor          editor instance
		 */
		init: function (editor) {
			if (!_didInit) {
				this._setup();
			}
			
			_dragArea.set(editor.uuid, {
				editor: editor,
				element: null
			});
		},
		
		/**
		 * Handles items dragged into the browser window.
		 * 
		 * @param       {Event}         event           drag event
		 */
		_dragOver: function (event) {
			event.preventDefault();
			
			//noinspection JSUnresolvedVariable
			if (!event.dataTransfer || !event.dataTransfer.types) {
				return;
			}
			
			var isFirefox = false;
			//noinspection JSUnresolvedVariable
			for (var property in event.dataTransfer) {
				//noinspection JSUnresolvedVariable
				if (event.dataTransfer.hasOwnProperty(property) && property.match(/^moz/)) {
					isFirefox = true;
					break;
				}
			}
			
			// IE and WebKit set 'Files', Firefox sets 'application/x-moz-file' for files being dragged
			// and Safari just provides 'Files' along with a huge list of garbage
			_isFile = false;
			if (isFirefox) {
				// Firefox sets the 'Files' type even if the user is just dragging an on-page element
				//noinspection JSUnresolvedVariable
				if (event.dataTransfer.types[0] === 'application/x-moz-file') {
					_isFile = true;
				}
			}
			else {
				//noinspection JSUnresolvedVariable
				for (var i = 0; i < event.dataTransfer.types.length; i++) {
					//noinspection JSUnresolvedVariable
					if (event.dataTransfer.types[i] === 'Files') {
						_isFile = true;
						break;
					}
				}
			}
			
			if (!_isFile) {
				// user is just dragging around some garbage, ignore it
				return;
			}
			
			if (_isDragging) {
				// user is still dragging the file around
				return;
			}
			
			_isDragging = true;
			
			_dragArea.forEach((function (data, uuid) {
				var editor = data.editor.$editor[0];
				if (!editor.parentNode) {
					_dragArea.delete(uuid);
					return;
				}
				
				var element = data.element;
				if (element === null) {
					element = elCreate('div');
					element.className = 'redactorDropArea';
					elData(element, 'element-id', data.editor.$element[0].id);
					elData(element, 'drop-here', Language.get('wcf.attachment.dragAndDrop.dropHere'));
					elData(element, 'drop-now', Language.get('wcf.attachment.dragAndDrop.dropNow'));
					
					element.addEventListener('dragover', function () { element.classList.add('active'); });
					element.addEventListener('dragleave', function () { element.classList.remove('active'); });
					element.addEventListener('drop', this._drop.bind(this));
					
					data.element = element;
				}
				
				editor.parentNode.insertBefore(element, editor);
				element.style.setProperty('top', editor.offsetTop + 'px', '');
			}).bind(this));
		},
		
		/**
		 * Handles items dropped onto an editor's drop area
		 * 
		 * @param       {Event}         event           drop event
		 * @protected
		 */
		_drop: function (event) {
			if (!_isFile) {
				return;
			}
			
			//noinspection JSUnresolvedVariable
			if (!event.dataTransfer || !event.dataTransfer.files.length) {
				return;
			}
			
			event.preventDefault();
			
			//noinspection JSCheckFunctionSignatures
			var elementId = elData(event.currentTarget, 'element-id');
			
			//noinspection JSUnresolvedVariable
			for (var i = 0, length = event.dataTransfer.files.length; i < length; i++) {
				//noinspection JSUnresolvedVariable
				EventHandler.fire('com.woltlab.wcf.redactor2', 'dragAndDrop_' + elementId, {
					file: event.dataTransfer.files[i]
				});
			}
			
			// this will reset all drop areas
			this._dragLeave();
		},
		
		/**
		 * Invoked whenever the item is no longer dragged or was dropped.
		 * 
		 * @protected
		 */
		_dragLeave: function () {
			if (!_isDragging || !_isFile) {
				return;
			}
			
			if (_timerLeave !== null) {
				window.clearTimeout(_timerLeave);
			}
			
			_timerLeave = window.setTimeout(function () {
				if (!_isDragging) {
					_dragArea.forEach(function (data) {
						if (data.element && data.element.parentNode) {
							data.element.classList.remove('active');
							elRemove(data.element);
						}
					});
				}
				
				_timerLeave = null;
			}, 100);
			
			_isDragging = false;
		},
		
		/**
		 * Handles the global drop event.
		 * 
		 * @param       {Event}         event
		 * @protected
		 */
		_globalDrop: function (event) {
			if (event.target.closest('.redactor-layer') === null) {
				var eventData = { cancelDrop: true, event: event };
				_dragArea.forEach(function(data) {
					//noinspection JSUnresolvedVariable
					EventHandler.fire('com.woltlab.wcf.redactor2', 'dragAndDrop_globalDrop_' + data.editor.$element[0].id, eventData);
				});
				
				if (eventData.cancelDrop) {
					event.preventDefault();
				}
			}
			
			this._dragLeave(event);
		},
		
		/**
		 * Binds listeners to global events.
		 * 
		 * @protected
		 */
		_setup: function () {
			// discard garbage event
			window.addEventListener('dragend', function (event) { event.preventDefault(); });
			
			window.addEventListener('dragover', this._dragOver.bind(this));
			window.addEventListener('dragleave', this._dragLeave.bind(this));
			window.addEventListener('drop', this._globalDrop.bind(this));
			
			_didInit = true;
		}
	};
});
