/**
 * Data handler for the poll options.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Form/Builder/Field/Wysiwyg/Poll
 * @since       5.2
 */
define(['Core', '../Field'], function(Core, FormBuilderField) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function FormBuilderFieldPoll(fieldId) {
		this.init(fieldId);
	};
	Core.inherit(FormBuilderFieldPoll, FormBuilderField, {
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
		 */
		_getData: function() {
			return this._pollEditor.getData();
		},
		
		/**
		 * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_readField
		 */
		_readField: function() {
			// does nothing
		},
		
		/**
		 * 
		 * @param       {WoltLabSuite/Core/Ui/Poll/Editor}      pollEditor
		 */
		setPollEditor: function(pollEditor) {
			this._pollEditor = pollEditor;
		}
	});
	
	return FormBuilderFieldPoll;
});
