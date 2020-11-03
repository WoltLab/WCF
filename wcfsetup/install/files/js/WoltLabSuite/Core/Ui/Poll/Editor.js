/**
 * Handles the data to create and edit a poll in a form created via form builder.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Editor
 */
define(["require", "exports", "tslib", "../../Core", "../../Language", "../Sortable/List", "../../Event/Handler", "../../Date/Picker"], function (require, exports, tslib_1, Core, Language, List_1, EventHandler, DatePicker) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    List_1 = tslib_1.__importDefault(List_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    DatePicker = tslib_1.__importStar(DatePicker);
    class UiPollEditor {
        constructor(containerId, pollOptions, wysiwygId, options) {
            const container = document.getElementById(containerId);
            if (container === null) {
                throw new Error("Unknown poll editor container with id '" + containerId + "'.");
            }
            this.container = container;
            this.wysiwygId = wysiwygId;
            if (wysiwygId !== '' && document.getElementById(wysiwygId) === null) {
                throw new Error("Unknown wysiwyg field with id '" + wysiwygId + "'.");
            }
            this.questionField = document.getElementById(this.wysiwygId + 'Poll_question');
            const optionList = this.container.querySelector('.sortableList');
            if (optionList === null) {
                throw new Error("Cannot find poll options list for container with id '" + containerId + "'.");
            }
            this.optionList = optionList;
            this.endTimeField = document.getElementById(this.wysiwygId + 'Poll_endTime');
            this.maxVotesField = document.getElementById(this.wysiwygId + 'Poll_maxVotes');
            this.isChangeableYesField = document.getElementById(this.wysiwygId + 'Poll_isChangeable');
            this.isChangeableNoField = document.getElementById(this.wysiwygId + 'Poll_isChangeable_no');
            this.isPublicYesField = document.getElementById(this.wysiwygId + 'Poll_isPublic');
            this.isPublicNoField = document.getElementById(this.wysiwygId + 'Poll_isPublic_no');
            this.resultsRequireVoteYesField = document.getElementById(this.wysiwygId + 'Poll_resultsRequireVote');
            this.resultsRequireVoteNoField = document.getElementById(this.wysiwygId + 'Poll_resultsRequireVote_no');
            this.sortByVotesYesField = document.getElementById(this.wysiwygId + 'Poll_sortByVotes');
            this.sortByVotesNoField = document.getElementById(this.wysiwygId + 'Poll_sortByVotes_no');
            this.optionCount = 0;
            this.options = Core.extend({
                isAjax: false,
                maxOptions: 20,
            }, options);
            this.createOptionList(pollOptions || []);
            new List_1.default({
                containerId: containerId,
                options: {
                    toleranceElement: '> div',
                },
            });
            if (this.options.isAjax) {
                ['handleError', 'reset', 'submit', 'validate'].forEach((event) => {
                    EventHandler.add('com.woltlab.wcf.redactor2', event + '_' + this.wysiwygId, this[event].bind(this));
                });
            }
            else {
                const form = this.container.closest('form');
                if (form === null) {
                    throw new Error("Cannot find form for container with id '" + containerId + "'.");
                }
                form.addEventListener('submit', (ev) => this.submit(ev));
            }
        }
        /**
         * Creates a poll option with the given data or an empty poll option of no data is given.
         */
        createOption(optionValue, optionId, insertAfter) {
            optionValue = optionValue || '';
            optionId = optionId || '0';
            const listItem = document.createElement('LI');
            listItem.classList.add('sortableNode');
            listItem.dataset.optionId = optionId;
            if (insertAfter) {
                insertAfter.insertAdjacentElement('afterend', listItem);
            }
            else {
                this.optionList.appendChild(listItem);
            }
            const pollOptionInput = document.createElement('div');
            pollOptionInput.classList.add('pollOptionInput');
            listItem.appendChild(pollOptionInput);
            const sortHandle = document.createElement('span');
            sortHandle.classList.add('icon', 'icon16', 'fa-arrows', 'sortableNodeHandle');
            pollOptionInput.appendChild(sortHandle);
            // buttons
            const addButton = document.createElement('a');
            listItem.setAttribute('role', 'button');
            listItem.setAttribute('href', '#');
            addButton.classList.add('icon', 'icon16', 'fa-plus', 'jsTooltip', 'jsAddOption', 'pointer');
            addButton.setAttribute('title', Language.get('wcf.poll.button.addOption'));
            addButton.addEventListener('click', () => this.createOption());
            pollOptionInput.appendChild(addButton);
            const deleteButton = document.createElement('a');
            deleteButton.setAttribute('role', 'button');
            deleteButton.setAttribute('href', '#');
            deleteButton.classList.add('icon', 'icon16', 'fa-times', 'jsTooltip', 'jsDeleteOption', 'pointer');
            deleteButton.setAttribute('title', Language.get('wcf.poll.button.removeOption'));
            deleteButton.addEventListener('click', (ev) => this.removeOption(ev));
            pollOptionInput.appendChild(deleteButton);
            // input field
            const optionInput = document.createElement('input');
            optionInput.type = 'text';
            optionInput.value = optionValue;
            optionInput.maxLength = 255;
            optionInput.addEventListener('keydown', (ev) => this.optionInputKeyDown(ev));
            optionInput.addEventListener('click', () => {
                // work-around for some weird focus issue on iOS/Android
                if (document.activeElement !== optionInput) {
                    optionInput.focus();
                }
            });
            pollOptionInput.appendChild(optionInput);
            if (insertAfter !== null) {
                optionInput.focus();
            }
            this.optionCount++;
            if (this.optionCount === this.options.maxOptions) {
                this.optionList.querySelectorAll('.jsAddOption').forEach((icon) => {
                    icon.classList.remove('pointer');
                    icon.classList.add('disabled');
                });
            }
        }
        /**
         * Populates the option list with the current options.
         */
        createOptionList(pollOptions) {
            pollOptions.forEach((option) => {
                this.createOption(option.optionValue, option.optionID);
            });
            if (this.optionCount < this.options.maxOptions) {
                this.createOption();
            }
        }
        /**
         * Handles validation errors returned by Ajax request.
         */
        handleError(data) {
            switch (data.returnValues.fieldName) {
                case this.wysiwygId + 'Poll_endTime':
                case this.wysiwygId + 'Poll_maxVotes':
                    const fieldName = data.returnValues.fieldName.replace(this.wysiwygId + 'Poll_', '');
                    const small = document.createElement('small');
                    small.classList.add('innerError');
                    small.innerHTML = Language.get('wcf.poll.' + fieldName + '.error.' + data.returnValues.errorType);
                    const element = document.createElement(data.returnValues.fieldName);
                    element.nextSibling.insertAdjacentElement("afterbegin", small);
                    data.cancel = true;
                    break;
            }
        }
        /**
         * Adds another option field below the current option field after pressing Enter.
         */
        optionInputKeyDown(event) {
            if (event.key !== 'Enter') {
                return;
            }
            const target = event.currentTarget;
            const addOption = target.parentElement.querySelector('.jsAddOption');
            Core.triggerEvent(addOption, 'click');
            event.preventDefault();
        }
        /**
         * Removes a poll option after clicking on its deletion button.
         */
        removeOption(event) {
            event.preventDefault();
            const button = event.currentTarget;
            button.closest('li').remove();
            this.optionCount--;
            if (this.optionList.childElementCount === 0) {
                this.createOption();
            }
            else {
                this.optionList.querySelectorAll('.jsAddOption').forEach((icon) => {
                    icon.classList.add('pointer');
                    icon.classList.remove('disabled');
                });
            }
        }
        /**
         * Resets all poll fields.
         */
        reset() {
            this.questionField.value = '';
            this.optionCount = 0;
            this.optionList.innerHTML = '';
            this.createOption();
            DatePicker.clear(this.endTimeField);
            this.maxVotesField.value = '1';
            this.isChangeableYesField.checked = false;
            this.isChangeableNoField.checked = true;
            this.isPublicYesField.checked = false;
            this.isPublicNoField.checked = true;
            this.resultsRequireVoteYesField.checked = false;
            this.resultsRequireVoteNoField.checked = true;
            this.sortByVotesYesField.checked = false;
            this.sortByVotesNoField.checked = true;
            EventHandler.fire('com.woltlab.wcf.poll.editor', 'reset', {
                pollEditor: this,
            });
        }
        /**
         * Handles the poll data if the form is submitted.
         */
        submit(event) {
            if (this.options.isAjax) {
                EventHandler.fire('com.woltlab.wcf.poll.editor', 'submit', {
                    event: event,
                    pollEditor: this,
                });
            }
            else {
                const form = this.container.closest('form');
                const options = this.getOptions();
                this.getOptions().forEach((option, i) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = this.wysiwygId + 'Poll_options[' + i + ']';
                    input.value = option;
                    form.appendChild(input);
                });
            }
        }
        /**
         * Validates the poll data.
         */
        validate(data) {
            if (this.questionField.value.trim() === '') {
                return;
            }
            let nonEmptyOptionCount = 0;
            for (let i = 0, length = this.optionList.children.length; i < length; i++) {
                const optionInput = this.optionList.children[i].querySelector('input[type=text]');
                if (optionInput.value.trim() !== '') {
                    nonEmptyOptionCount++;
                }
            }
            if (nonEmptyOptionCount === 0) {
                data.api.throwError(this.container, Language.get('wcf.global.form.error.empty'));
                data.valid = false;
            }
            else {
                const maxVotes = ~~this.maxVotesField.value;
                if (maxVotes && maxVotes > nonEmptyOptionCount) {
                    data.api.throwError(this.maxVotesField.parentElement, Language.get('wcf.poll.maxVotes.error.invalid'));
                    data.valid = false;
                }
                else {
                    EventHandler.fire('com.woltlab.wcf.poll.editor', 'validate', {
                        data: data,
                        pollEditor: this
                    });
                }
            }
        }
        /**
         * Returns the data of the poll.
         */
        getData() {
            let data = {};
            data[this.questionField.id] = this.questionField.value;
            data[this.wysiwygId + 'Poll_options'] = this.getOptions();
            data[this.endTimeField.id] = this.endTimeField.value;
            data[this.maxVotesField.id] = this.maxVotesField.value;
            data[this.isChangeableYesField.id] = !!this.isChangeableYesField.checked;
            data[this.isPublicYesField.id] = !!this.isPublicYesField.checked;
            data[this.resultsRequireVoteYesField.id] = !!this.resultsRequireVoteYesField.checked;
            data[this.sortByVotesYesField.id] = !!this.sortByVotesYesField.checked;
            return data;
        }
        /**
         * Returns the selectable options in the poll.
         *
         * Format: `{optionID}_{option}` with `optionID = 0` if it is a new option.
         */
        getOptions() {
            let options = [];
            for (let i = 0, length = this.optionList.children.length; i < length; i++) {
                const listItem = this.optionList.children[i];
                const optionValue = listItem.querySelector('input[type=text]').value.trim();
                if (optionValue !== '') {
                    options.push(listItem.dataset.optionId + '_' + optionValue);
                }
            }
            return options;
        }
    }
    Core.enableLegacyInheritance(UiPollEditor);
    return UiPollEditor;
});
