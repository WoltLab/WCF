/**
 * Handles the data to create and edit a poll in a form created via form builder.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Core", "../../Language", "../Sortable/List", "../../Event/Handler", "../../Date/Picker", "../../Component/Ckeditor/Event", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Core, Language, List_1, EventHandler, DatePicker, Event_1, Util_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    List_1 = tslib_1.__importDefault(List_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    DatePicker = tslib_1.__importStar(DatePicker);
    Util_1 = tslib_1.__importDefault(Util_1);
    class UiPollEditor {
        container;
        endTimeField;
        isChangeableNoField;
        isChangeableYesField;
        isPublicNoField;
        isPublicYesField;
        maxVotesField;
        optionCount;
        options;
        optionList;
        questionField;
        resultsRequireVoteNoField;
        resultsRequireVoteYesField;
        sortByVotesNoField;
        sortByVotesYesField;
        wysiwygId;
        constructor(containerId, pollOptions, wysiwygId, options) {
            const container = document.getElementById(containerId);
            if (container === null) {
                throw new Error("Unknown poll editor container with id '" + containerId + "'.");
            }
            this.container = container;
            this.wysiwygId = wysiwygId;
            if (wysiwygId !== "" && document.getElementById(wysiwygId) === null) {
                throw new Error("Unknown wysiwyg field with id '" + wysiwygId + "'.");
            }
            this.questionField = document.getElementById(this.wysiwygId + "pollQuestion");
            const optionList = this.container.querySelector(".sortableList");
            if (optionList === null) {
                throw new Error("Cannot find poll options list for container with id '" + containerId + "'.");
            }
            this.optionList = optionList;
            this.endTimeField = document.getElementById(this.wysiwygId + "pollEndTime");
            this.maxVotesField = document.getElementById(this.wysiwygId + "pollMaxVotes");
            this.isChangeableYesField = document.getElementById(this.wysiwygId + "pollIsChangeable");
            this.isChangeableNoField = document.getElementById(this.wysiwygId + "pollIsChangeable_no");
            this.isPublicYesField = document.getElementById(this.wysiwygId + "pollIsPublic");
            this.isPublicNoField = document.getElementById(this.wysiwygId + "PollIsPublic_no");
            this.resultsRequireVoteYesField = document.getElementById(this.wysiwygId + "pollResultsRequireVote");
            this.resultsRequireVoteNoField = document.getElementById(this.wysiwygId + "pollResultsRequireVote_no");
            this.sortByVotesYesField = document.getElementById(this.wysiwygId + "pollSortByVotes");
            this.sortByVotesNoField = document.getElementById(this.wysiwygId + "pollSortByVotes_no");
            this.optionCount = 0;
            this.options = Core.extend({
                isAjax: false,
                maxOptions: 20,
            }, options);
            this.createOptionList(pollOptions || []);
            new List_1.default({
                containerId: containerId,
                options: {
                    toleranceElement: "> div",
                },
            });
            if (this.options.isAjax) {
                const element = document.getElementById(this.wysiwygId);
                element.addEventListener("reset", () => {
                    this.reset();
                });
                (0, Event_1.listenToCkeditor)(element)
                    .collectMetaData((payload) => {
                    payload.metaData.poll = this.#getPollData();
                })
                    .reset(() => this.reset());
                ["handleError", "submit", "validate"].forEach((event) => {
                    EventHandler.add("com.woltlab.wcf.ckeditor5", event + "_" + this.wysiwygId, (...args) => this[event](...args));
                });
            }
            else {
                const form = this.container.closest("form");
                if (form === null) {
                    throw new Error("Cannot find form for container with id '" + containerId + "'.");
                }
                form.addEventListener("submit", (ev) => this.submit(ev));
            }
        }
        /**
         * Creates a poll option with the given data or an empty poll option of no data is given.
         */
        createOption(optionValue, optionId, insertAfter) {
            optionValue = optionValue || "";
            optionId = optionId || "0";
            const listItem = document.createElement("LI");
            listItem.classList.add("sortableNode");
            listItem.dataset.optionId = optionId;
            if (insertAfter) {
                insertAfter.insertAdjacentElement("afterend", listItem);
            }
            else {
                this.optionList.appendChild(listItem);
            }
            const pollOptionInput = document.createElement("div");
            pollOptionInput.classList.add("pollOptionInput");
            listItem.appendChild(pollOptionInput);
            const sortHandle = document.createElement("span");
            sortHandle.innerHTML = '<fa-icon name="up-down-left-right" solid></fa-icon>';
            sortHandle.classList.add("sortableNodeHandle");
            pollOptionInput.appendChild(sortHandle);
            // buttons
            const addButton = document.createElement("button");
            addButton.type = "button";
            addButton.innerHTML = '<fa-icon name="plus" solid></fa-icon>';
            addButton.classList.add("jsTooltip", "jsAddOption");
            addButton.title = Language.get("wcf.poll.button.addOption");
            addButton.addEventListener("click", () => this.createOption());
            pollOptionInput.appendChild(addButton);
            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.innerHTML = '<fa-icon name="xmark" solid></fa-icon>';
            deleteButton.classList.add("jsTooltip", "jsDeleteOption");
            deleteButton.title = Language.get("wcf.poll.button.removeOption");
            deleteButton.addEventListener("click", () => this.removeOption(deleteButton));
            pollOptionInput.appendChild(deleteButton);
            // input field
            const optionInput = document.createElement("input");
            optionInput.type = "text";
            optionInput.value = optionValue;
            optionInput.maxLength = 255;
            optionInput.addEventListener("keydown", (ev) => this.optionInputKeyDown(ev));
            optionInput.addEventListener("click", () => {
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
                this.optionList.querySelectorAll(".jsAddOption").forEach((icon) => {
                    icon.classList.remove("pointer");
                    icon.classList.add("disabled");
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
                case "pollEndTime":
                case "pollMaxVotes": {
                    let fieldName = data.returnValues.fieldName.replace("poll", "");
                    fieldName = fieldName.charAt(0).toLowerCase() + fieldName.slice(1);
                    Util_1.default.innerError(document.getElementById(this.wysiwygId + data.returnValues.fieldName), Language.get("wcf.poll." + fieldName + ".error." + data.returnValues.errorType), true);
                    data.cancel = true;
                    break;
                }
            }
        }
        /**
         * Adds another option field below the current option field after pressing Enter.
         */
        optionInputKeyDown(event) {
            if (event.key !== "Enter") {
                return;
            }
            const target = event.currentTarget;
            const addOption = target.parentElement.querySelector(".jsAddOption");
            Core.triggerEvent(addOption, "click");
            event.preventDefault();
        }
        /**
         * Removes a poll option after clicking on its deletion button.
         */
        removeOption(button) {
            button.closest("li").remove();
            this.optionCount--;
            if (this.optionList.childElementCount === 0) {
                this.createOption();
            }
            else {
                this.optionList.querySelectorAll(".jsAddOption").forEach((icon) => {
                    icon.classList.add("pointer");
                    icon.classList.remove("disabled");
                });
            }
        }
        /**
         * Resets all poll fields.
         */
        reset() {
            this.questionField.value = "";
            this.optionCount = 0;
            this.optionList.innerHTML = "";
            this.createOption();
            DatePicker.clear(this.endTimeField);
            this.maxVotesField.value = "1";
            this.isChangeableYesField.checked = false;
            if (this.isChangeableNoField)
                this.isChangeableNoField.checked = true;
            this.isPublicYesField.checked = false;
            if (this.isPublicNoField)
                this.isPublicNoField.checked = true;
            this.resultsRequireVoteYesField.checked = false;
            if (this.resultsRequireVoteNoField)
                this.resultsRequireVoteNoField.checked = true;
            this.sortByVotesYesField.checked = false;
            if (this.sortByVotesNoField)
                this.sortByVotesNoField.checked = true;
            EventHandler.fire("com.woltlab.wcf.poll.editor", "reset", {
                pollEditor: this,
            });
        }
        /**
         * Handles the poll data if the form is submitted.
         */
        submit(event) {
            if (this.options.isAjax) {
                EventHandler.fire("com.woltlab.wcf.poll.editor", "submit", {
                    event: event,
                    pollEditor: this,
                });
            }
            else {
                const form = this.container.closest("form");
                this.getOptions().forEach((option, i) => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = `${this.wysiwygId}pollOptions[${i}]`;
                    input.value = option;
                    form.appendChild(input);
                });
            }
        }
        #getPollData() {
            const data = {
                pollEndTime: DatePicker.getValue(this.endTimeField),
                pollMaxVotes: parseInt(this.maxVotesField.value) || 0,
                pollQuestion: this.questionField.value,
                pollOptions: [],
            };
            if (this.isChangeableYesField.checked) {
                data.pollIsChangeable = true;
            }
            if (this.resultsRequireVoteYesField.checked) {
                data.pollResultsRequireVote = true;
            }
            if (this.sortByVotesYesField.checked) {
                data.pollSortByVotes = true;
            }
            if (this.isPublicYesField?.checked) {
                data.pollIsPublic = true;
            }
            data.pollOptions = this.getOptions();
            return data;
        }
        /**
         * Validates the poll data.
         */
        validate(data) {
            if (this.questionField.value.trim() === "") {
                return;
            }
            let nonEmptyOptionCount = 0;
            Array.from(this.optionList.children).forEach((listItem) => {
                const optionInput = listItem.querySelector("input[type=text]");
                if (optionInput.value.trim() !== "") {
                    nonEmptyOptionCount++;
                }
            });
            if (nonEmptyOptionCount === 0) {
                data.api.throwError(this.container, Language.get("wcf.global.form.error.empty"));
                data.valid = false;
            }
            else {
                const maxVotes = ~~this.maxVotesField.value;
                if (maxVotes && maxVotes > nonEmptyOptionCount) {
                    data.api.throwError(this.maxVotesField.parentElement, Language.get("wcf.poll.maxVotes.error.invalid"));
                    data.valid = false;
                }
                else {
                    EventHandler.fire("com.woltlab.wcf.poll.editor", "validate", {
                        data: data,
                        pollEditor: this,
                    });
                }
            }
        }
        /**
         * Returns the data of the poll.
         */
        getData() {
            return {
                [this.questionField.id]: this.questionField.value,
                [this.wysiwygId + "Poll_options"]: this.getOptions(),
                [this.wysiwygId + "pollOptions"]: this.getOptions(),
                [this.endTimeField.id]: this.endTimeField.value,
                [this.maxVotesField.id]: this.maxVotesField.value,
                [this.isChangeableYesField.id]: !!this.isChangeableYesField.checked,
                [this.isPublicYesField.id]: !!this.isPublicYesField.checked,
                [this.resultsRequireVoteYesField.id]: !!this.resultsRequireVoteYesField.checked,
                [this.sortByVotesYesField.id]: !!this.sortByVotesYesField.checked,
            };
        }
        /**
         * Returns the selectable options in the poll.
         *
         * Format: `{optionID}_{option}` with `optionID = 0` if it is a new option.
         */
        getOptions() {
            const options = [];
            Array.from(this.optionList.children).forEach((listItem) => {
                const optionValue = listItem.querySelector("input[type=text]").value.trim();
                if (optionValue !== "") {
                    options.push(`${listItem.dataset.optionId}_${optionValue}`);
                }
            });
            return options;
        }
    }
    return UiPollEditor;
});
