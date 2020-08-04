/**
 * Delete files which are uploaded via AJAX.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/File/Delete
 * @since	5.2
 */
define(['Ajax', 'Core', 'Dom/ChangeListener', 'Language', 'Dom/Util', 'Dom/Traverse', 'Dictionary'], function(Ajax, Core, DomChangeListener, Language, DomUtil, DomTraverse, Dictionary) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function Delete(buttonContainerId, targetId, isSingleImagePreview, uploadHandler) {
		this._isSingleImagePreview = isSingleImagePreview;
		this._uploadHandler = uploadHandler;
		
		this._buttonContainer = elById(buttonContainerId);
		if (this._buttonContainer === null) {
			throw new Error("Element id '" + buttonContainerId + "' is unknown.");
		}
		
		this._target = elById(targetId);
		if (targetId === null) {
			throw new Error("Element id '" + targetId + "' is unknown.");
		}
		this._containers = new Dictionary();
		
		this._internalId = elData(this._target, 'internal-id');
		
		if (!this._internalId) {
			throw new Error("InternalId is unknown.");
		}
		
		this.rebuild();
	}
	
	Delete.prototype = {
		/**
		 * Creates the upload button.
		 */
		_createButtons: function() {
			var element, elements = elBySelAll('li.uploadedFile', this._target), elementData, triggerChange = false, uniqueFileId;
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				uniqueFileId = elData(element, 'unique-file-id');
				if (this._containers.has(uniqueFileId)) {
					continue;
				}
				
				elementData = {
					uniqueFileId: uniqueFileId,
					element: element
				};
				
				this._containers.set(uniqueFileId, elementData);
				this._initDeleteButton(element, elementData);
				
				triggerChange = true;
			}
			
			if (triggerChange) {
				DomChangeListener.trigger();
			}
		},
		
		/**
		 * Init the delete button for a specific element.
		 * 
		 * @param       {HTMLElement}   element
		 * @param       {string}        elementData
		 */
		_initDeleteButton: function(element, elementData) {
			var buttonGroup = elBySel('.buttonGroup', element);
			
			if (buttonGroup === null) {
				throw new Error("Button group in '" + targetId + "' is unknown.");
			}
			
			var li = elCreate('li');
			var span = elCreate('span');
			span.classList = "button jsDeleteButton small";
			span.textContent = Language.get('wcf.global.button.delete');
			li.appendChild(span);
			buttonGroup.appendChild(li);
			
			li.addEventListener(WCF_CLICK_EVENT, this._delete.bind(this, elementData.uniqueFileId));
		},
		
		/**
		 * Delete a specific file with the given uniqueFileId.
		 * 
		 * @param       {string}        uniqueFileId
		 */
		_delete: function(uniqueFileId) {
			Ajax.api(this, {
				uniqueFileId: uniqueFileId,
				internalId: this._internalId
			});
		},
		
		/**
		 * Rebuilds the delete buttons for unknown files. 
		 */
		rebuild: function() {
			if (this._isSingleImagePreview) {
				var img = elBySel('img', this._target);
				
				if (img !== null) {
					var uniqueFileId = elData(img, 'unique-file-id');
					
					if (!this._containers.has(uniqueFileId)) {
						var elementData = {
							uniqueFileId: uniqueFileId,
							element: img
						};
						
						this._containers.set(uniqueFileId, elementData);
						
						this._deleteButton = elCreate('p');
						this._deleteButton.className = 'button deleteButton';
						
						var span = elCreate('span');
						span.textContent = Language.get('wcf.global.button.delete');
						this._deleteButton.appendChild(span);
						
						this._buttonContainer.appendChild(this._deleteButton);
						
						this._deleteButton.addEventListener(WCF_CLICK_EVENT, this._delete.bind(this, elementData.uniqueFileId));
					}
				}
			}
			else {
				this._createButtons();
			}
		},
		
		_ajaxSuccess: function(data) {
			elRemove(this._containers.get(data.uniqueFileId).element);
			
			if (this._isSingleImagePreview) {
				elRemove(this._deleteButton);
				this._deleteButton = null;
			}
			
			this._uploadHandler.checkMaxFiles();
			Core.triggerEvent(this._target, 'change');
		},
		
		_ajaxSetup: function () {
			return {
				url: 'index.php?ajax-file-delete/&t=' + SECURITY_TOKEN
			};
		}
	};
	
	return Delete;
});
