/**
 * Handles the JavaScript part of the label form field.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Controller/Label
 * @since	5.2
 */
define(['Core', 'Dom/Util', 'Language', 'Ui/SimpleDropdown'], function(Core, DomUtil, Language, UiSimpleDropdown) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldLabel(fielId, labelId, options) {
		this.init(fielId, labelId, options);
	};
	FormBuilderFieldLabel.prototype = {
		/**
		 * Initializes the label form field.
		 * 
		 * @param	{string}	fieldId		id of the relevant form builder field
		 * @param	{integer}	labelId		id of the currently selected label
		 * @param	{object}	options		additional label options
		 */
		init: function(fieldId, labelId, options) {
			this._formFieldContainer = elById(fieldId + 'Container');
			this._labelChooser = elByClass('labelChooser', this._formFieldContainer)[0];
			this._options = Core.extend({
				forceSelection: false,
				showWithoutSelection: false
			}, options);
			
			this._input = elCreate('input');
			this._input.type = 'hidden';
			this._input.id = fieldId;
			this._input.name = fieldId;
			this._input.value = ~~labelId;
			this._formFieldContainer.appendChild(this._input);
			
			var labelChooserId = DomUtil.identify(this._labelChooser);
			
			// init dropdown
			var dropdownMenu = UiSimpleDropdown.getDropdownMenu(labelChooserId);
			if (dropdownMenu === null) {
				UiSimpleDropdown.init(elByClass('dropdownToggle', this._labelChooser)[0]);
				dropdownMenu = UiSimpleDropdown.getDropdownMenu(labelChooserId);
			}
			
			var additionalOptionList = null;
			if (this._options.showWithoutSelection || !this._options.forceSelection) {
				additionalOptionList = elCreate('ul');
				dropdownMenu.appendChild(additionalOptionList);
				
				var dropdownDivider = elCreate('li');
				dropdownDivider.className = 'dropdownDivider';
				additionalOptionList.appendChild(dropdownDivider);
			}
			
			if (this._options.showWithoutSelection) {
				var listItem = elCreate('li');
				elData(listItem, 'label-id', -1);
				this._blockScroll(listItem);
				additionalOptionList.appendChild(listItem);
				
				var span = elCreate('span');
				listItem.appendChild(span);
				
				var label = elCreate('span');
				label.className = 'badge label';
				label.innerHTML = Language.get('wcf.label.withoutSelection');
				span.appendChild(label);
			}
			
			if (!this._options.forceSelection) {
				var listItem = elCreate('li');
				elData(listItem, 'label-id', 0);
				this._blockScroll(listItem);
				additionalOptionList.appendChild(listItem);
				
				var span = elCreate('span');
				listItem.appendChild(span);
				
				var label = elCreate('span');
				label.className = 'badge label';
				label.innerHTML = Language.get('wcf.label.none');
				span.appendChild(label);
			}
			
			elBySelAll('li:not(.dropdownDivider)', dropdownMenu, function(listItem) {
				listItem.addEventListener('click', this._click.bind(this));
				
				if (labelId) {
					if (~~elData(listItem, 'label-id') === labelId) {
						this._selectLabel(listItem);
					}
				}
			}.bind(this));
		},
		
		/**
		 * Blocks page scrolling for the given element.
		 * 
		 * @param	{HTMLElement}		element
		 */
		_blockScroll: function(element) {
			element.addEventListener(
				'wheel',
				function(event) {
					event.preventDefault();
				},
				{
					passive: false
				}
			);
		},
		
		/**
		 * Select a label after clicking on it.
		 * 
		 * @param	{Event}		event	click event in label selection dropdown
		 */
		_click: function(event) {
			event.preventDefault();
			
			this._selectLabel(event.currentTarget, false);
		},
		
		/**
		 * Selects the given label.
		 * 
		 * @param	{HTMLElement}	label
		 */
		_selectLabel: function(label) {
			// save label
			var labelId = elData(label, 'label-id');
			if (!labelId) {
				labelId = 0;
			}
			
			// replace button with currently selected label
			var displayLabel = elBySel('span > span', label);
			var button = elBySel('.dropdownToggle > span', this._labelChooser);
			button.className = displayLabel.className;
			button.textContent = displayLabel.textContent;
			
			this._input.value = labelId;
		}
	};
	
	return FormBuilderFieldLabel;
});
