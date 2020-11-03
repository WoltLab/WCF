"use strict";
/**
 * Handles the data to create and edit a poll in a form created via form builder.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Editor
 */
var Core = require("../../Core");
var Language = require("../../Language");
var List_1 = require("../Sortable/List");
var EventHandler = require("../../Event/Handler");
var DatePicker = require("../../Date/Picker");
var UiPollEditor = /** @class */ (function () {
    function UiPollEditor(containerId, pollOptions, wysiwygId, options) {
        var _this = this;
        var container = document.getElementById(containerId);
        if (container === null) {
            throw new Error("Unknown poll editor container with id '" + containerId + "'.");
        }
        this.container = container;
        this.wysiwygId = wysiwygId;
        if (wysiwygId !== "" && document.getElementById(wysiwygId) === null) {
            throw new Error("Unknown wysiwyg field with id '" + wysiwygId + "'.");
        }
        this.questionField = document.getElementById(this.wysiwygId + "Poll_question");
        var optionList = this.container.querySelector(".sortableList");
        if (optionList === null) {
            throw new Error("Cannot find poll options list for container with id '" + containerId + "'.");
        }
        this.optionList = optionList;
        this.endTimeField = document.getElementById(this.wysiwygId + "Poll_endTime");
        this.maxVotesField = document.getElementById(this.wysiwygId + "Poll_maxVotes");
        this.isChangeableYesField = document.getElementById(this.wysiwygId + "Poll_isChangeable");
        this.isChangeableNoField = document.getElementById(this.wysiwygId + "Poll_isChangeable_no");
        this.isPublicYesField = document.getElementById(this.wysiwygId + "Poll_isPublic");
        this.isPublicNoField = document.getElementById(this.wysiwygId + "Poll_isPublic_no");
        this.resultsRequireVoteYesField = document.getElementById(this.wysiwygId + "Poll_resultsRequireVote");
        this.resultsRequireVoteNoField = document.getElementById(this.wysiwygId + "Poll_resultsRequireVote_no");
        this.sortByVotesYesField = document.getElementById(this.wysiwygId + "Poll_sortByVotes");
        this.sortByVotesNoField = document.getElementById(this.wysiwygId + "Poll_sortByVotes_no");
        this.optionCount = 0;
        this.options = Core.extend({
            isAjax: false,
            maxOptions: 20
        }, options);
        this.createOptionList(pollOptions || []);
        new List_1["default"]({
            containerId: containerId,
            options: {
                toleranceElement: "> div"
            }
        });
        if (this.options.isAjax) {
            ["handleError", "reset", "submit", "validate"].forEach(function (event) {
                EventHandler.add("com.woltlab.wcf.redactor2", event + "_" + _this.wysiwygId, function () {
                    var args = [];
                    for (var _i = 0; _i < arguments.length; _i++) {
                        args[_i] = arguments[_i];
                    }
                    return _this[event].apply(_this, args);
                });
            });
        }
        else {
            var form = this.container.closest("form");
            if (form === null) {
                throw new Error("Cannot find form for container with id '" + containerId + "'.");
            }
            form.addEventListener("submit", function (ev) { return _this.submit(ev); });
        }
    }
    /**
     * Creates a poll option with the given data or an empty poll option of no data is given.
     */
    UiPollEditor.prototype.createOption = function (optionValue, optionId, insertAfter) {
        var _this = this;
        optionValue = optionValue || "";
        optionId = optionId || "0";
        var listItem = document.createElement("LI");
        listItem.classList.add("sortableNode");
        listItem.dataset.optionId = optionId;
        if (insertAfter) {
            insertAfter.insertAdjacentElement("afterend", listItem);
        }
        else {
            this.optionList.appendChild(listItem);
        }
        var pollOptionInput = document.createElement("div");
        pollOptionInput.classList.add("pollOptionInput");
        listItem.appendChild(pollOptionInput);
        var sortHandle = document.createElement("span");
        sortHandle.classList.add("icon", "icon16", "fa-arrows", "sortableNodeHandle");
        pollOptionInput.appendChild(sortHandle);
        // buttons
        var addButton = document.createElement("a");
        listItem.setAttribute("role", "button");
        listItem.setAttribute("href", "#");
        addButton.classList.add("icon", "icon16", "fa-plus", "jsTooltip", "jsAddOption", "pointer");
        addButton.setAttribute("title", Language.get("wcf.poll.button.addOption"));
        addButton.addEventListener("click", function () { return _this.createOption(); });
        pollOptionInput.appendChild(addButton);
        var deleteButton = document.createElement("a");
        deleteButton.setAttribute("role", "button");
        deleteButton.setAttribute("href", "#");
        deleteButton.classList.add("icon", "icon16", "fa-times", "jsTooltip", "jsDeleteOption", "pointer");
        deleteButton.setAttribute("title", Language.get("wcf.poll.button.removeOption"));
        deleteButton.addEventListener("click", function (ev) { return _this.removeOption(ev); });
        pollOptionInput.appendChild(deleteButton);
        // input field
        var optionInput = document.createElement("input");
        optionInput.type = "text";
        optionInput.value = optionValue;
        optionInput.maxLength = 255;
        optionInput.addEventListener("keydown", function (ev) { return _this.optionInputKeyDown(ev); });
        optionInput.addEventListener("click", function () {
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
            this.optionList.querySelectorAll(".jsAddOption").forEach(function (icon) {
                icon.classList.remove("pointer");
                icon.classList.add("disabled");
            });
        }
    };
    /**
     * Populates the option list with the current options.
     */
    UiPollEditor.prototype.createOptionList = function (pollOptions) {
        var _this = this;
        pollOptions.forEach(function (option) {
            _this.createOption(option.optionValue, option.optionID);
        });
        if (this.optionCount < this.options.maxOptions) {
            this.createOption();
        }
    };
    /**
     * Handles validation errors returned by Ajax request.
     */
    UiPollEditor.prototype.handleError = function (data) {
        switch (data.returnValues.fieldName) {
            case this.wysiwygId + "Poll_endTime":
            case this.wysiwygId + "Poll_maxVotes": {
                var fieldName = data.returnValues.fieldName.replace(this.wysiwygId + "Poll_", "");
                var small = document.createElement("small");
                small.classList.add("innerError");
                small.innerHTML = Language.get("wcf.poll." + fieldName + ".error." + data.returnValues.errorType);
                var field = document.getElementById(data.returnValues.fieldName);
                field.nextSibling.insertAdjacentElement("afterbegin", small);
                data.cancel = true;
                break;
            }
        }
    };
    /**
     * Adds another option field below the current option field after pressing Enter.
     */
    UiPollEditor.prototype.optionInputKeyDown = function (event) {
        if (event.key !== "Enter") {
            return;
        }
        var target = event.currentTarget;
        var addOption = target.parentElement.querySelector(".jsAddOption");
        Core.triggerEvent(addOption, "click");
        event.preventDefault();
    };
    /**
     * Removes a poll option after clicking on its deletion button.
     */
    UiPollEditor.prototype.removeOption = function (event) {
        event.preventDefault();
        var button = event.currentTarget;
        button.closest("li").remove();
        this.optionCount--;
        if (this.optionList.childElementCount === 0) {
            this.createOption();
        }
        else {
            this.optionList.querySelectorAll(".jsAddOption").forEach(function (icon) {
                icon.classList.add("pointer");
                icon.classList.remove("disabled");
            });
        }
    };
    /**
     * Resets all poll fields.
     */
    UiPollEditor.prototype.reset = function () {
        this.questionField.value = "";
        this.optionCount = 0;
        this.optionList.innerHTML = "";
        this.createOption();
        DatePicker.clear(this.endTimeField);
        this.maxVotesField.value = "1";
        this.isChangeableYesField.checked = false;
        this.isChangeableNoField.checked = true;
        this.isPublicYesField.checked = false;
        this.isPublicNoField.checked = true;
        this.resultsRequireVoteYesField.checked = false;
        this.resultsRequireVoteNoField.checked = true;
        this.sortByVotesYesField.checked = false;
        this.sortByVotesNoField.checked = true;
        EventHandler.fire("com.woltlab.wcf.poll.editor", "reset", {
            pollEditor: this
        });
    };
    /**
     * Handles the poll data if the form is submitted.
     */
    UiPollEditor.prototype.submit = function (event) {
        var _this = this;
        if (this.options.isAjax) {
            EventHandler.fire("com.woltlab.wcf.poll.editor", "submit", {
                event: event,
                pollEditor: this
            });
        }
        else {
            var form_1 = this.container.closest("form");
            this.getOptions().forEach(function (option, i) {
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = _this.wysiwygId + " + 'Poll_options[" + i + "}]";
                input.value = option;
                form_1.appendChild(input);
            });
        }
    };
    /**
     * Validates the poll data.
     */
    UiPollEditor.prototype.validate = function (data) {
        if (this.questionField.value.trim() === "") {
            return;
        }
        var nonEmptyOptionCount = 0;
        Array.from(this.optionList.children).forEach(function (listItem) {
            var optionInput = listItem.querySelector("input[type=text]");
            if (optionInput.value.trim() !== "") {
                nonEmptyOptionCount++;
            }
        });
        if (nonEmptyOptionCount === 0) {
            data.api.throwError(this.container, Language.get("wcf.global.form.error.empty"));
            data.valid = false;
        }
        else {
            var maxVotes = ~~this.maxVotesField.value;
            if (maxVotes && maxVotes > nonEmptyOptionCount) {
                data.api.throwError(this.maxVotesField.parentElement, Language.get("wcf.poll.maxVotes.error.invalid"));
                data.valid = false;
            }
            else {
                EventHandler.fire("com.woltlab.wcf.poll.editor", "validate", {
                    data: data,
                    pollEditor: this
                });
            }
        }
    };
    /**
     * Returns the data of the poll.
     */
    UiPollEditor.prototype.getData = function () {
        var _a;
        return _a = {},
            _a[this.questionField.id] = this.questionField.value,
            _a[this.wysiwygId + "Poll_options"] = this.getOptions(),
            _a[this.endTimeField.id] = this.endTimeField.value,
            _a[this.maxVotesField.id] = this.maxVotesField.value,
            _a[this.isChangeableYesField.id] = !!this.isChangeableYesField.checked,
            _a[this.isPublicYesField.id] = !!this.isPublicYesField.checked,
            _a[this.resultsRequireVoteYesField.id] = !!this.resultsRequireVoteYesField.checked,
            _a[this.sortByVotesYesField.id] = !!this.sortByVotesYesField.checked,
            _a;
    };
    /**
     * Returns the selectable options in the poll.
     *
     * Format: `{optionID}_{option}` with `optionID = 0` if it is a new option.
     */
    UiPollEditor.prototype.getOptions = function () {
        var options = [];
        Array.from(this.optionList.children).forEach(function (listItem) {
            var optionValue = listItem.querySelector("input[type=text]").value.trim();
            if (optionValue !== "") {
                options.push(listItem.dataset.optionId + "_" + optionValue);
            }
        });
        return options;
    };
    return UiPollEditor;
}());
Core.enableLegacyInheritance(UiPollEditor);
module.exports = UiPollEditor;
