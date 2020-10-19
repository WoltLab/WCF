/**
 * Handles the JavaScript part of the devtools project pip entry list.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Devtools/Project/Pip/Entry/List
 */
define([
	'Ajax',
	'Language',
	'Ui/Confirmation',
	'Ui/Notification'
], function (
	Ajax,
	Language,
	UiConfirmation,
	UiNotification
) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function DevtoolsProjectPipEntryList(tableId, projectId, pip, entryType, supportsDeleteInstruction) {
		this.init(tableId, projectId, pip, entryType, supportsDeleteInstruction);
	};
	DevtoolsProjectPipEntryList.prototype = {
		/**
		 * Initializes the devtools project pip entry list handler.
		 * 
		 * @param	{string}	tableId				id of the table containing the pip entries
		 * @param	{integer}	projectId			id of the project the listed pip entries belong to
		 * @param	{string}	pip				name of the pip the listed entries belong to
		 * @param	{string}	entryType			type of the listed entries
		 * @param	{boolean}	supportsDeleteInstruction	is `true` if the pip supports `<delete>`
		 */
		init: function(tableId, projectId, pip, entryType, supportsDeleteInstruction) {
			this._table = elById(tableId);
			if (this._table === null) {
				throw new Error("Unknown element with id '" + tableId + "'.");
			}
			if (this._table.tagName !== 'TABLE') {
				throw new Error("Element with id '" + tableId + "' is no table.");
			}
			
			this._projectId = projectId;
			this._pip = pip;
			this._entryType = entryType;
			this._supportsDeleteInstruction = supportsDeleteInstruction;
			
			elBySelAll('.jsDeleteButton', this._table, function(deleteButton) {
				deleteButton.addEventListener('click', this._confirmDeletePipEntry.bind(this));
			}.bind(this));
		},
		
		/**
		 * Returns the data used to setup the AJAX request object.
		 * 
		 * @return	{object}	setup data
		 */
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'deletePipEntry',
					className: 'wcf\\data\\devtools\\project\\DevtoolsProjectAction'
				}
			};
		},
		
		/**
		 * Handles successful AJAX request.
		 * 
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			UiNotification.show();
			
			elBySelAll('tbody > tr', this._table, function(pipEntry) {
				if (elData(pipEntry, 'identifier') === data.returnValues.identifier) {
					elRemove(pipEntry);
				}
			}.bind(this));
			
			// reload page if table is empty
			if (elBySelAll('tbody > tr', this._table).length === 0) {
				window.location.reload();
			}
		},
		
		/**
		 * Shows the confirmation dialog when deleting a pip entry.
		 * 
		 * @param	{Event}		event
		 */
		_confirmDeletePipEntry: function(event) {
			var pipEntry = event.currentTarget.closest('tr');
			
			UiConfirmation.show({
				confirm: this._deletePipEntry.bind(this),
				message: Language.get('wcf.acp.devtools.project.pip.entry.delete.confirmMessage'),
				template: this._supportsDeleteInstruction ? '' +
					'<dl>' +
					'	<dt></dt>' +
					'	<dd>' +
					'		<label><input type="checkbox" name="addDeleteInstruction" checked> ' + Language.get('wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction') + '</label>' + 
					'		<small>' + Language.get('wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction.description') + '</small>' +
					'	</dd>' +
					'</dl>' : '',
				parameters: {
					pipEntry: pipEntry
				}
			});
		},
		
		/**
		 * Sends the AJAX request to delete a pip entry.
		 * 
		 * @param	{object}	parameters	contains the deleted pip entry element
		 * @param	{HTMLElement}	content		confirmation dialog containing the `addDeleteInstruction` instruction
		 */
		_deletePipEntry: function(parameters, content) {
			var addDeleteInstruction = false;
			if (this._supportsDeleteInstruction) {
				addDeleteInstruction = ~~elBySel('input[name=addDeleteInstruction]', content).checked;
			}
			
			Ajax.api(this, {
				objectIDs: [this._projectId],
				parameters: {
					addDeleteInstruction: addDeleteInstruction,
					entryType: this._entryType,
					identifier: elData(parameters.pipEntry, 'identifier'),
					pip: this._pip
				}
			});
		}
	};
	
	return DevtoolsProjectPipEntryList;
});
