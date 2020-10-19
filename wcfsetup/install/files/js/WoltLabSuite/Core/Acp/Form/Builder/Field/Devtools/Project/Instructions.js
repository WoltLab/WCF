/**
 * Manages the instructions entered in a devtools project instructions form field.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/Instructions
 * @since	5.2
 */
define([
    'Dom/ChangeListener',
    'Dom/Traverse',
    'Dom/Util',
    'EventKey',
    'Language',
    'Ui/Confirmation',
    'Ui/Dialog',
    'WoltLabSuite/Core/Ui/Sortable/List'
], function (DomChangeListener, DomTraverse, DomUtil, EventKey, Language, UiConfirmation, UiDialog, UiSortableList) {
    "use strict";
    var _applicationPips = ['acpTemplate', 'file', 'script', 'template'];
    /**
     * @constructor
     */
    function Instructions(formFieldId, instructionsTemplate, instructionsEditDialogTemplate, instructionEditDialogTemplate, pipDefaultFilenames, existingInstructions) {
        this.init(formFieldId, instructionsTemplate, instructionsEditDialogTemplate, instructionEditDialogTemplate, pipDefaultFilenames, existingInstructions || []);
    }
    ;
    Instructions.prototype = {
        /**
         * Initializes the instructions handler.
         *
         * @param	{string}	formFieldId			id of the associated form field
         * @param	{Template}	instructionsTemplate		template used for a new set of instructions
         * @param	{Template}	instructionsEditDialogTemplate	template used for instructions edit dialogs
         * @param	{Template}	instructionEditDialogTemplate	template used for instruction edit dialogs
         * @param	{object}	pipDefaultFilenames		maps pip names to their default filenames
         * @param	{object[]}	existingInstructions		data of existing instructions
         */
        init: function (formFieldId, instructionsTemplate, instructionsEditDialogTemplate, instructionEditDialogTemplate, pipDefaultFilenames, existingInstructions) {
            this._formFieldId = formFieldId;
            this._instructionsTemplate = instructionsTemplate;
            this._instructionsEditDialogTemplate = instructionsEditDialogTemplate;
            this._instructionEditDialogTemplate = instructionEditDialogTemplate;
            this._instructionsCounter = 0;
            this._pipDefaultFilenames = pipDefaultFilenames;
            this._instructionCounter = 0;
            this._instructionsList = elById(this._formFieldId + '_instructionsList');
            if (this._instructionsList === null) {
                throw new Error("Cannot find package list for packages field with id '" + this._formFieldId + "'.");
            }
            this._instructionsType = elById(this._formFieldId + '_instructionsType');
            if (this._instructionsType === null) {
                throw new Error("Cannot find instruction type form field for instructions field with id '" + this._formFieldId + "'.");
            }
            this._instructionsType.addEventListener('change', this._toggleFromVersionFormField.bind(this));
            this._fromVersion = elById(this._formFieldId + '_fromVersion');
            if (this._fromVersion === null) {
                throw new Error("Cannot find from version form field for instructions field with id '" + this._formFieldId + "'.");
            }
            this._fromVersion.addEventListener('keypress', this._instructionsKeyPress.bind(this));
            this._addButton = elById(this._formFieldId + '_addButton');
            if (this._addButton === null) {
                throw new Error("Cannot find add button for instructions field with id '" + this._formFieldId + "'.");
            }
            this._addButton.addEventListener('click', this._addInstructions.bind(this));
            this._form = this._instructionsList.closest('form');
            if (this._form === null) {
                throw new Error("Cannot find form element for instructions field with id '" + this._formFieldId + "'.");
            }
            this._form.addEventListener('submit', this._submit.bind(this));
            var hasInstallInstructions = false;
            for (var index in existingInstructions) {
                var instructions = existingInstructions[index];
                if (instructions.type === 'install') {
                    hasInstallInstructions = true;
                    break;
                }
            }
            // ensure that there are always installation instructions
            if (!hasInstallInstructions) {
                this._addInstructionsByData({
                    fromVersion: '',
                    type: 'install'
                });
            }
            existingInstructions.forEach(this._addInstructionsByData.bind(this));
            DomChangeListener.trigger();
        },
        /**
         * Adds an instruction to a set of instructions as a consequence
         * of the given event. If the instruction data is invalid, an
         * error message is shown and no instruction is added.
         *
         * @param	{Event}		event	event that triggered trying to add the instruction
         */
        _addInstruction: function (event) {
            event.preventDefault();
            event.stopPropagation();
            var instructionsId = elData(event.currentTarget.closest('li.section'), 'instructions-id');
            // note: data will be validated/filtered by the server
            var pipField = elById(this._formFieldId + '_instructions' + instructionsId + '_pip');
            // ignore pressing button if no PIP has been selected
            if (!pipField.value) {
                return;
            }
            var valueField = elById(this._formFieldId + '_instructions' + instructionsId + '_value');
            var runStandaloneField = elById(this._formFieldId + '_instructions' + instructionsId + '_runStandalone');
            var applicationField = elById(this._formFieldId + '_instructions' + instructionsId + '_application');
            this._addInstructionByData(instructionsId, {
                application: _applicationPips.indexOf(pipField.value) !== -1 ? applicationField.value : '',
                pip: pipField.value,
                runStandalone: ~~runStandaloneField.checked,
                value: valueField.value
            });
            // empty fields
            pipField.value = '';
            valueField.value = '';
            runStandaloneField.checked = false;
            applicationField.value = '';
            elById(this._formFieldId + '_instructions' + instructionsId + '_valueDescription').innerHTML = Language.get('wcf.acp.devtools.project.instruction.value.description');
            this._toggleApplicationFormField(instructionsId);
            DomChangeListener.trigger();
        },
        /**
         * Adds an instruction to the set of instructions with the given id.
         *
         * @param	{int}		instructionsId
         * @param	{object}	instructionData
         */
        _addInstructionByData: function (instructionsId, instructionData) {
            var instructionId = ++this._instructionCounter;
            var instructionList = elById(this._formFieldId + '_instructions' + instructionsId + '_instructionList');
            var listItem = elCreate('li');
            listItem.className = 'sortableNode';
            listItem.id = this._formFieldId + '_instruction' + instructionId;
            elData(listItem, 'instruction-id', instructionId);
            elData(listItem, 'application', instructionData.application);
            elData(listItem, 'pip', instructionData.pip);
            elData(listItem, 'runStandalone', instructionData.runStandalone);
            elData(listItem, 'value', instructionData.value);
            var content = '' +
                '<div class="sortableNodeLabel">' +
                '	<div class="jsDevtoolsProjectInstruction">' +
                '		' + Language.get('wcf.acp.devtools.project.instruction.instruction', instructionData);
            if (instructionData.errors) {
                for (var index in instructionData.errors) {
                    content += '<small class="innerError">' + instructionData.errors[index] + '</small>';
                }
            }
            content += '' +
                '	</div>' +
                '	<span class="statusDisplay sortableButtonContainer">' +
                '		<span class="icon icon16 fa-pencil pointer jsTooltip" id="' + this._formFieldId + '_instruction' + instructionId + '_editButton" title="' + Language.get('wcf.global.button.edit') + '"></span>' +
                '		<span class="icon icon16 fa-times pointer jsTooltip" id="' + this._formFieldId + '_instruction' + instructionId + '_deleteButton" title="' + Language.get('wcf.global.button.delete') + '"></span>' +
                '	</span>' +
                '</div>';
            listItem.innerHTML = content;
            instructionList.appendChild(listItem);
            elById(this._formFieldId + '_instruction' + instructionId + '_deleteButton').addEventListener('click', this._removeInstruction.bind(this));
            elById(this._formFieldId + '_instruction' + instructionId + '_editButton').addEventListener('click', this._editInstruction.bind(this));
        },
        /**
         * Adds a set of instructions as a consequenc of the given event.
         * If the instructions data is invalid, an error message is shown
         * and no instruction set is added.
         *
         * @param	{Event}		event	event that triggered trying to add the instructions
         */
        _addInstructions: function (event) {
            event.preventDefault();
            event.stopPropagation();
            // validate data
            if (!this._validateInstructionsType() || (this._instructionsType.value === 'update' && !this._validateFromVersion(this._fromVersion))) {
                return;
            }
            this._addInstructionsByData({
                fromVersion: this._instructionsType.value === 'update' ? this._fromVersion.value : '',
                type: this._instructionsType.value
            });
            // empty fields
            this._instructionsType.value = '';
            this._fromVersion.value = '';
            this._toggleFromVersionFormField();
            DomChangeListener.trigger();
        },
        /**
         * Adds a set of instructions.
         *
         * @param	{object}	instructionData
         */
        _addInstructionsByData: function (instructionsData) {
            var instructionsId = ++this._instructionsCounter;
            var listItem = elCreate('li');
            listItem.className = 'section';
            listItem.innerHTML = this._instructionsTemplate.fetch({
                instructionsId: instructionsId,
                sectionTitle: Language.get('wcf.acp.devtools.project.instructions.type.' + instructionsData.type + '.title', {
                    fromVersion: instructionsData.fromVersion
                }),
                type: instructionsData.type
            });
            listItem.id = this._formFieldId + '_instructions' + instructionsId;
            elData(listItem, 'instructions-id', instructionsId);
            elData(listItem, 'type', instructionsData.type);
            elData(listItem, 'fromVersion', instructionsData.fromVersion);
            elById(this._formFieldId + '_instructions' + instructionsId + '_valueDescription');
            this._instructionsList.appendChild(listItem);
            var instructionListContainer = elById(this._formFieldId + '_instructions' + instructionsId + '_instructionListContainer');
            if (Array.isArray(instructionsData.errors)) {
                instructionsData.errors.forEach(function (errorMessage) {
                    var small = elCreate('small');
                    small.className = 'innerError';
                    small.innerHTML = errorMessage;
                    instructionListContainer.parentNode.insertBefore(small, instructionListContainer);
                });
            }
            new UiSortableList({
                containerId: instructionListContainer.id,
                isSimpleSorting: true,
                options: {
                    toleranceElement: '> div'
                }
            });
            var deleteButton = elById(this._formFieldId + '_instructions' + instructionsId + '_deleteButton');
            if (instructionsData.type === 'update') {
                elById(this._formFieldId + '_instructions' + instructionsId + '_deleteButton').addEventListener('click', this._removeInstructions.bind(this));
                elById(this._formFieldId + '_instructions' + instructionsId + '_editButton').addEventListener('click', this._editInstructions.bind(this));
            }
            elById(this._formFieldId + '_instructions' + instructionsId + '_pip').addEventListener('change', this._changeInstructionPip.bind(this));
            elById(this._formFieldId + '_instructions' + instructionsId + '_value').addEventListener('keypress', this._instructionKeyPress.bind(this));
            elById(this._formFieldId + '_instructions' + instructionsId + '_addButton').addEventListener('click', this._addInstruction.bind(this));
            if (instructionsData.instructions) {
                for (var index in instructionsData.instructions) {
                    this._addInstructionByData(instructionsId, instructionsData.instructions[index]);
                }
            }
        },
        /**
         * Is called if the selected package installation plugin of an
         * instruction is changed.
         *
         * @param	{Event}		event		change event
         */
        _changeInstructionPip: function (event) {
            var pip = event.currentTarget.value;
            var instructionsId = elData(event.currentTarget.closest('li.section'), 'instructions-id');
            var description = elById(this._formFieldId + '_instructions' + instructionsId + '_valueDescription');
            // update value description
            if (this._pipDefaultFilenames[pip] !== '') {
                description.innerHTML = Language.get('wcf.acp.devtools.project.instruction.value.description.defaultFilename', {
                    defaultFilename: this._pipDefaultFilenames[pip]
                });
            }
            else {
                description.innerHTML = Language.get('wcf.acp.devtools.project.instruction.value.description');
            }
            var valueDlClassList = elById(this._formFieldId + '_instructions' + instructionsId + '_value').closest('dl').classList;
            var applicationDl = elById(this._formFieldId + '_instructions' + instructionsId + '_application').closest('dl');
            // toggle application selector
            this._toggleApplicationFormField(instructionsId);
        },
        /**
         * Opens a dialog to edit an existing instruction.
         *
         * @param	{Event}		event	edit button click event
         */
        _editInstruction: function (event) {
            var listItem = event.currentTarget.closest('li');
            var instructionId = elData(listItem, 'instruction-id');
            var application = elData(listItem, 'application');
            var pip = elData(listItem, 'pip');
            var runStandalone = elDataBool(listItem, 'runStandalone');
            var value = elData(listItem, 'value');
            var dialogContent = this._instructionEditDialogTemplate.fetch({
                runStandalone: runStandalone,
                value: value
            });
            var dialogId = 'instructionEditDialog' + instructionId;
            if (!UiDialog.getDialog(dialogId)) {
                UiDialog.openStatic(dialogId, dialogContent, {
                    onSetup: function (content) {
                        var applicationSelect = elBySel('select[name=application]', content);
                        var pipSelect = elBySel('select[name=pip]', content);
                        var runStandaloneInput = elBySel('input[name=runStandalone]', content);
                        var valueInput = elBySel('input[name=value]', content);
                        // set values of `select` elements
                        applicationSelect.value = application;
                        pipSelect.value = pip;
                        var submit = function () {
                            var listItem = elById(this._formFieldId + '_instruction' + instructionId);
                            elData(listItem, 'application', _applicationPips.indexOf(pipSelect.value) !== -1 ? applicationSelect.value : '');
                            elData(listItem, 'pip', pipSelect.value);
                            elData(listItem, 'runStandalone', ~~runStandaloneInput.checked);
                            elData(listItem, 'value', valueInput.value);
                            // note: data will be validated/filtered by the server
                            elByClass('jsDevtoolsProjectInstruction', listItem)[0].innerHTML = Language.get('wcf.acp.devtools.project.instruction.instruction', {
                                application: elData(listItem, 'application'),
                                pip: elData(listItem, 'pip'),
                                runStandalone: elDataBool(listItem, 'runStandalone'),
                                value: elData(listItem, 'value'),
                            });
                            DomChangeListener.trigger();
                            UiDialog.close(dialogId);
                        }.bind(this);
                        valueInput.addEventListener('keypress', function (event) {
                            if (EventKey.Enter(event)) {
                                submit();
                            }
                        });
                        elBySel('button[data-type=submit]', content).addEventListener('click', submit);
                        var pipChange = function () {
                            var pip = pipSelect.value;
                            if (_applicationPips.indexOf(pip) !== -1) {
                                elShow(applicationSelect.closest('dl'));
                            }
                            else {
                                elHide(applicationSelect.closest('dl'));
                            }
                            var description = DomTraverse.nextByTag(valueInput, 'SMALL');
                            if (this._pipDefaultFilenames[pip] !== '') {
                                description.innerHTML = Language.get('wcf.acp.devtools.project.instruction.value.description.defaultFilename', {
                                    defaultFilename: this._pipDefaultFilenames[pip]
                                });
                            }
                            else {
                                description.innerHTML = Language.get('wcf.acp.devtools.project.instruction.value.description');
                            }
                        }.bind(this);
                        pipSelect.addEventListener('change', pipChange);
                        pipChange();
                    }.bind(this),
                    title: Language.get('wcf.acp.devtools.project.instruction.edit')
                });
            }
            else {
                UiDialog.openStatic(dialogId);
            }
        },
        /**
         * Opens a dialog to edit an existing set of instructions.
         *
         * @param	{Event}		event	edit button click event
         */
        _editInstructions: function (event) {
            var listItem = event.currentTarget.closest('li');
            var instructionsId = elData(listItem, 'instructions-id');
            var fromVersion = elData(listItem, 'fromVersion');
            var dialogContent = this._instructionsEditDialogTemplate.fetch({
                fromVersion: fromVersion
            });
            var dialogId = 'instructionsEditDialog' + instructionsId;
            if (!UiDialog.getDialog(dialogId)) {
                UiDialog.openStatic(dialogId, dialogContent, {
                    onSetup: function (content) {
                        var fromVersion = elBySel('input[name=fromVersion]', content);
                        var submit = function () {
                            if (!this._validateFromVersion(fromVersion)) {
                                return;
                            }
                            var instructions = elById(this._formFieldId + '_instructions' + instructionsId);
                            elData(instructions, 'fromVersion', fromVersion.value);
                            elByClass('jsInstructionsTitle', instructions)[0].textContent = Language.get('wcf.acp.devtools.project.instructions.type.update.title', {
                                fromVersion: fromVersion.value
                            });
                            DomChangeListener.trigger();
                            UiDialog.close(dialogId);
                        }.bind(this);
                        fromVersion.addEventListener('keypress', function (event) {
                            if (EventKey.Enter(event)) {
                                submit();
                            }
                        });
                        elBySel('button[data-type=submit]', content).addEventListener('click', submit);
                    }.bind(this),
                    title: Language.get('wcf.acp.devtools.project.instructions.edit')
                });
            }
            else {
                UiDialog.openStatic(dialogId);
            }
        },
        /**
         * Returns the error element for the given form field element.
         * If `createIfNonExistent` is not given or `false`, `null` is returned
         * if there is no error element, otherwise an empty error element
         * is created and returned.
         *
         * @param	{?boolean}	createIfNonExistent
         * @return	{?HTMLElement}
         */
        _getErrorElement: function (element, createIfNoNExistent) {
            var error = DomTraverse.nextByClass(element, 'innerError');
            if (error === null && createIfNoNExistent) {
                error = elCreate('small');
                error.className = 'innerError';
                DomUtil.insertAfter(error, element);
            }
            return error;
        },
        /**
         * Returns the error element for the from version form field.
         * If `createIfNonExistent` is not given or `false`, `null` is returned
         * if there is no error element, otherwise an empty error element
         * is created and returned.
         *
         * @param	{?boolean}	createIfNonExistent
         * @return	{?HTMLElement}
         */
        _getFromVersionErrorElement: function (inputField, createIfNonExistent) {
            return this._getErrorElement(inputField, createIfNonExistent);
        },
        /**
         * Returns the error element for the instruction type form field.
         * If `createIfNonExistent` is not given or `false`, `null` is returned
         * if there is no error element, otherwise an empty error element
         * is created and returned.
         *
         * @param	{?boolean}	createIfNonExistent
         * @return	{?HTMLElement}
         */
        _getInstructionsTypeErrorElement: function (createIfNonExistent) {
            return this._getErrorElement(this._instructionsType, createIfNonExistent);
        },
        /**
         * Adds an instruction after pressing ENTER in a relevant text
         * field.
         *
         * @param	{Event}		event
         */
        _instructionKeyPress: function (event) {
            if (EventKey.Enter(event)) {
                this._addInstruction(event);
            }
        },
        /**
         * Adds a set of instruction after pressing ENTER in a relevant
         * text field.
         *
         * @param	{Event}		event
         */
        _instructionsKeyPress: function (event) {
            if (EventKey.Enter(event)) {
                this._addInstructions(event);
            }
        },
        /**
         * Removes an instruction by clicking on its delete button.
         *
         * @param	{Event}		event		delete button click event
         */
        _removeInstruction: function (event) {
            var instruction = event.currentTarget.closest('li');
            UiConfirmation.show({
                confirm: function () {
                    elRemove(instruction);
                },
                message: Language.get('wcf.acp.devtools.project.instruction.delete.confirmMessages')
            });
        },
        /**
         * Removes a set of instructions by clicking on its delete button.
         *
         * @param	{Event}		event		delete button click event
         */
        _removeInstructions: function (event) {
            var instructions = event.currentTarget.closest('li');
            UiConfirmation.show({
                confirm: function () {
                    elRemove(instructions);
                },
                message: Language.get('wcf.acp.devtools.project.instructions.delete.confirmMessages')
            });
        },
        /**
         * Adds all necessary (hidden) form fields to the form when
         * submitting the form.
         */
        _submit: function (event) {
            DomTraverse.childrenByTag(this._instructionsList, 'LI').forEach(function (instructions, instructionsIndex) {
                var namePrefix = this._formFieldId + '[' + instructionsIndex + ']';
                var instructionsType = elCreate('input');
                elAttr(instructionsType, 'type', 'hidden');
                elAttr(instructionsType, 'name', namePrefix + '[type]');
                instructionsType.value = elData(instructions, 'type');
                this._form.appendChild(instructionsType);
                if (instructionsType.value === 'update') {
                    var fromVersion = elCreate('input');
                    elAttr(fromVersion, 'type', 'hidden');
                    elAttr(fromVersion, 'name', this._formFieldId + '[' + instructionsIndex + '][fromVersion]');
                    fromVersion.value = elData(instructions, 'fromVersion');
                    this._form.appendChild(fromVersion);
                }
                DomTraverse.childrenByTag(elById(instructions.id + '_instructionList'), 'LI').forEach(function (instruction, instructionIndex) {
                    var namePrefix = this._formFieldId + '[' + instructionsIndex + '][instructions][' + instructionIndex + ']';
                    ['pip', 'value', 'runStandalone'].forEach((function (property) {
                        var element = elCreate('input');
                        elAttr(element, 'type', 'hidden');
                        elAttr(element, 'name', namePrefix + '[' + property + ']');
                        element.value = elData(instruction, property);
                        this._form.appendChild(element);
                    }).bind(this));
                    if (_applicationPips.indexOf(elData(instruction, 'pip')) !== -1) {
                        var application = elCreate('input');
                        elAttr(application, 'type', 'hidden');
                        elAttr(application, 'name', namePrefix + '[application]');
                        application.value = elData(instruction, 'application');
                        this._form.appendChild(application);
                    }
                }.bind(this));
            }.bind(this));
        },
        /**
         * Toggles the visibility of the application form field based on
         * the selected pip for the instructions with the given id.
         *
         * @param	{int}	instructionsId		id of the relevant instruction set
         */
        _toggleApplicationFormField: function (instructionsId) {
            var pip = elById(this._formFieldId + '_instructions' + instructionsId + '_pip').value;
            var valueDlClassList = elById(this._formFieldId + '_instructions' + instructionsId + '_value').closest('dl').classList;
            var applicationDl = elById(this._formFieldId + '_instructions' + instructionsId + '_application').closest('dl');
            if (_applicationPips.indexOf(pip) !== -1) {
                valueDlClassList.remove('col-md-9');
                valueDlClassList.add('col-md-7');
                elShow(applicationDl);
            }
            else {
                valueDlClassList.remove('col-md-7');
                valueDlClassList.add('col-md-9');
                elHide(applicationDl);
            }
        },
        /**
         * Toggles the visibility of the `fromVersion` form field based on
         * the selected instructions type.
         */
        _toggleFromVersionFormField: function () {
            var instructionsTypeList = this._instructionsType.closest('dl').classList;
            var fromVersionDl = this._fromVersion.closest('dl');
            if (this._instructionsType.value === 'update') {
                instructionsTypeList.remove('col-md-10');
                instructionsTypeList.add('col-md-5');
                elShow(fromVersionDl);
            }
            else {
                instructionsTypeList.remove('col-md-5');
                instructionsTypeList.add('col-md-10');
                elHide(fromVersionDl);
            }
        },
        /**
         * Returns `true` if the currently entered update "from version"
         * is valid. Otherwise `false` is returned and an error message
         * is shown.
         *
         * @return	{boolean}
         */
        _validateFromVersion: function (inputField) {
            var version = inputField.value;
            if (version === '') {
                this._getFromVersionErrorElement(inputField, true).textContent = Language.get('wcf.global.form.error.empty');
                return false;
            }
            if (version.length > 50) {
                this._getFromVersionErrorElement(inputField, true).textContent = Language.get('wcf.acp.devtools.project.packageVersion.error.maximumLength');
                return false;
            }
            // wildcard versions are checked on the server side
            if (version.indexOf('*') === -1) {
                // see `wcf\data\package\Package::isValidVersion()`
                if (!version.match(/^([0-9]+)\.([0-9]+)\.([0-9]+)(\ (a|alpha|b|beta|d|dev|rc|pl)\ ([0-9]+))?$/i)) {
                    this._getFromVersionErrorElement(inputField, true).textContent = Language.get('wcf.acp.devtools.project.packageVersion.error.format');
                    return false;
                }
            }
            else if (!version.replace('*', '0').match(/^([0-9]+)\.([0-9]+)\.([0-9]+)(\ (a|alpha|b|beta|d|dev|rc|pl)\ ([0-9]+))?$/i)) {
                this._getFromVersionErrorElement(inputField, true).textContent = Language.get('wcf.acp.devtools.project.packageVersion.error.format');
                return false;
            }
            // remove outdated errors
            var error = this._getFromVersionErrorElement(inputField);
            if (error !== null) {
                elRemove(error);
            }
            return true;
        },
        /**
         * Returns `true` if the entered update instructions type is valid.
         * Otherwise `false` is returned and an error message is shown.
         *
         * @return	{boolean}
         */
        _validateInstructionsType: function () {
            if (this._instructionsType.value !== 'install' && this._instructionsType.value !== 'update') {
                if (this._instructionsType.value === '') {
                    this._getInstructionsTypeErrorElement(true).textContent = Language.get('wcf.global.form.error.empty');
                }
                else {
                    this._getInstructionsTypeErrorElement(true).textContent = Language.get('wcf.global.form.error.noValidSelection');
                }
                return false;
            }
            // there may only be one set of installation instructions 
            if (this._instructionsType.value === 'install') {
                var hasInstall = false;
                [].forEach.call(this._instructionsList.children, function (instructions) {
                    if (elData(instructions, 'type') === 'install') {
                        hasInstall = true;
                    }
                });
                if (hasInstall) {
                    this._getInstructionsTypeErrorElement(true).textContent = Language.get('wcf.acp.devtools.project.instructions.type.update.error.duplicate');
                    return false;
                }
            }
            // remove outdated errors
            var error = this._getInstructionsTypeErrorElement();
            if (error !== null) {
                elRemove(error);
            }
            return true;
        }
    };
    return Instructions;
});
