/**
 * Handles the data to create and edit a poll in a form created via form builder.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

import * as Core from "../../Core";
import * as Language from "../../Language";
import UiSortableList from "../Sortable/List";
import * as EventHandler from "../../Event/Handler";
import * as DatePicker from "../../Date/Picker";
import { DatabaseObjectActionResponse } from "../../Ajax/Data";
import { listenToCkeditor } from "../../Component/Ckeditor/Event";

interface UiPollEditorOptions {
  isAjax: boolean;
  maxOptions: number;
}

interface PollOption {
  optionID: string;
  optionValue: string;
}

interface AjaxReturnValue {
  errorType: string;
  fieldName: string;
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: AjaxReturnValue;
}

interface ValidationApi {
  throwError: (container: HTMLElement, message: string) => void;
}

interface ValidationData {
  api: ValidationApi;
  valid: boolean;
}

type PollData = {
  pollEndTime: string;
  pollIsChangeable?: boolean;
  pollIsPublic?: boolean;
  pollMaxVotes: number;
  pollOptions: string[];
  pollQuestion: string;
  pollResultsRequireVote?: true;
  pollSortByVotes?: boolean;
};

class UiPollEditor {
  private readonly container: HTMLElement;
  private readonly endTimeField: HTMLInputElement;
  private readonly isChangeableYesField: HTMLInputElement;
  private readonly isPublicField: HTMLInputElement;
  private readonly maxVotesField: HTMLInputElement;
  private optionCount: number;
  private readonly options: UiPollEditorOptions;
  private readonly optionList: HTMLOListElement;
  private readonly questionField: HTMLInputElement;
  private readonly resultsRequireVoteNoField: HTMLInputElement | null;
  private readonly resultsRequireVoteYesField: HTMLInputElement;
  private readonly sortByVotesField: HTMLInputElement;
  private readonly wysiwygId: string;

  constructor(containerId: string, pollOptions: PollOption[], wysiwygId: string, options: UiPollEditorOptions) {
    const container = document.getElementById(containerId);
    if (container === null) {
      throw new Error("Unknown poll editor container with id '" + containerId + "'.");
    }
    this.container = container;

    this.wysiwygId = wysiwygId;
    if (wysiwygId !== "" && document.getElementById(wysiwygId) === null) {
      throw new Error("Unknown wysiwyg field with id '" + wysiwygId + "'.");
    }

    this.questionField = document.getElementById(this.wysiwygId + "Poll_question") as HTMLInputElement;

    const optionList = this.container.querySelector(".sortableList");
    if (optionList === null) {
      throw new Error("Cannot find poll options list for container with id '" + containerId + "'.");
    }
    this.optionList = optionList as HTMLOListElement;

    this.endTimeField = document.getElementById(this.wysiwygId + "Poll_endTime") as HTMLInputElement;
    this.maxVotesField = document.getElementById(this.wysiwygId + "Poll_maxVotes") as HTMLInputElement;
    this.isChangeableYesField = document.getElementById(this.wysiwygId + "Poll_isChangeable") as HTMLInputElement;
    this.isPublicField = document.getElementById(this.wysiwygId + "Poll_isPublic") as HTMLInputElement;
    this.resultsRequireVoteYesField = document.getElementById(
      this.wysiwygId + "Poll_resultsRequireVote",
    ) as HTMLInputElement;
    this.resultsRequireVoteNoField = document.getElementById(
      this.wysiwygId + "Poll_resultsRequireVote_no",
    ) as HTMLInputElement;
    this.sortByVotesField = document.getElementById(this.wysiwygId + "Poll_sortByVotes") as HTMLInputElement;

    this.optionCount = 0;

    this.options = Core.extend(
      {
        isAjax: false,
        maxOptions: 20,
      },
      options,
    ) as UiPollEditorOptions;

    this.createOptionList(pollOptions || []);

    new UiSortableList({
      containerId: containerId,
      options: {
        toleranceElement: "> div",
      },
    });

    if (this.options.isAjax) {
      const element = document.getElementById(this.wysiwygId)!;
      element.addEventListener("reset", () => {
        this.reset();
      });

      listenToCkeditor(element)
        .collectMetaData((payload) => {
          payload.metaData.poll = this.#getPollData();
        })
        .reset(() => this.reset());

      ["handleError", "submit", "validate"].forEach((event) => {
        EventHandler.add("com.woltlab.wcf.ckeditor5", event + "_" + this.wysiwygId, (...args: unknown[]) =>
          this[event](...args),
        );
      });
    } else {
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
  private createOption(optionValue?: string, optionId?: string, insertAfter?: HTMLElement): void {
    optionValue = optionValue || "";
    optionId = optionId || "0";

    const listItem = document.createElement("LI") as HTMLLIElement;
    listItem.classList.add("sortableNode");
    listItem.dataset.optionId = optionId;

    if (insertAfter) {
      insertAfter.insertAdjacentElement("afterend", listItem);
    } else {
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
      this.optionList.querySelectorAll(".jsAddOption").forEach((icon: HTMLSpanElement) => {
        icon.classList.remove("pointer");
        icon.classList.add("disabled");
      });
    }
  }

  /**
   * Populates the option list with the current options.
   */
  private createOptionList(pollOptions: PollOption[]): void {
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
  private handleError(data: AjaxResponse): void {
    switch (data.returnValues.fieldName) {
      case this.wysiwygId + "pollEndTime":
      case this.wysiwygId + "pollMaxVotes": {
        const fieldName = data.returnValues.fieldName.replace(this.wysiwygId + "poll", "");

        const small = document.createElement("small");
        small.classList.add("innerError");
        small.innerHTML = Language.get("wcf.poll." + fieldName + ".error." + data.returnValues.errorType);

        const field = document.getElementById(data.returnValues.fieldName)!;
        (field.nextSibling! as HTMLElement).insertAdjacentElement("afterbegin", small);

        data.cancel = true;
        break;
      }
    }
  }

  /**
   * Adds another option field below the current option field after pressing Enter.
   */
  private optionInputKeyDown(event: KeyboardEvent): void {
    if (event.key !== "Enter") {
      return;
    }

    const target = event.currentTarget as HTMLInputElement;
    const addOption = target.parentElement!.querySelector(".jsAddOption") as HTMLSpanElement;
    Core.triggerEvent(addOption, "click");

    event.preventDefault();
  }

  /**
   * Removes a poll option after clicking on its deletion button.
   */
  private removeOption(button: HTMLButtonElement): void {
    button.closest("li")!.remove();

    this.optionCount--;

    if (this.optionList.childElementCount === 0) {
      this.createOption();
    } else {
      this.optionList.querySelectorAll(".jsAddOption").forEach((icon) => {
        icon.classList.add("pointer");
        icon.classList.remove("disabled");
      });
    }
  }

  /**
   * Resets all poll fields.
   */
  private reset(): void {
    this.questionField.value = "";

    this.optionCount = 0;
    this.optionList.innerHTML = "";
    this.createOption();

    DatePicker.clear(this.endTimeField);

    this.maxVotesField.value = "1";
    this.isChangeableYesField.checked = false;
    this.isPublicField.checked = false;
    this.resultsRequireVoteYesField.checked = false;
    if (this.resultsRequireVoteNoField) this.resultsRequireVoteNoField.checked = true;
    this.sortByVotesField.checked = false;

    EventHandler.fire("com.woltlab.wcf.poll.editor", "reset", {
      pollEditor: this,
    });
  }

  /**
   * Handles the poll data if the form is submitted.
   */
  private submit(event: Event): void {
    if (this.options.isAjax) {
      EventHandler.fire("com.woltlab.wcf.poll.editor", "submit", {
        event: event,
        pollEditor: this,
      });
    } else {
      const form = this.container.closest("form")!;

      this.getOptions().forEach((option, i) => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = `${this.wysiwygId}pollOptions[${i}]`;
        input.value = option;
        form.appendChild(input);
      });
    }
  }

  #getPollData(): PollData {
    const data: PollData = {
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

    if (this.sortByVotesField.checked) {
      data.pollSortByVotes = true;
    }

    if (this.isPublicField?.checked) {
      data.pollIsPublic = true;
    }

    data.pollOptions = this.getOptions();

    return data;
  }

  /**
   * Validates the poll data.
   */
  private validate(data: ValidationData): void {
    if (this.questionField.value.trim() === "") {
      return;
    }

    let nonEmptyOptionCount = 0;
    Array.from(this.optionList.children).forEach((listItem: HTMLLIElement) => {
      const optionInput = listItem.querySelector("input[type=text]") as HTMLInputElement;
      if (optionInput.value.trim() !== "") {
        nonEmptyOptionCount++;
      }
    });

    if (nonEmptyOptionCount === 0) {
      data.api.throwError(this.container, Language.get("wcf.global.form.error.empty"));
      data.valid = false;
    } else {
      const maxVotes = ~~this.maxVotesField.value;

      if (maxVotes && maxVotes > nonEmptyOptionCount) {
        data.api.throwError(this.maxVotesField.parentElement!, Language.get("wcf.poll.maxVotes.error.invalid"));
        data.valid = false;
      } else {
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
  public getData(): object {
    return {
      [this.questionField.id]: this.questionField.value,
      [this.wysiwygId + "Poll_options"]: this.getOptions(),
      [this.wysiwygId + "pollOptions"]: this.getOptions(),
      [this.endTimeField.id]: this.endTimeField.value,
      [this.maxVotesField.id]: this.maxVotesField.value,
      [this.isChangeableYesField.id]: !!this.isChangeableYesField.checked,
      [this.isPublicField.id]: !!this.isPublicField.checked,
      [this.resultsRequireVoteYesField.id]: !!this.resultsRequireVoteYesField.checked,
      [this.sortByVotesField.id]: !!this.sortByVotesField.checked,
    };
  }

  /**
   * Returns the selectable options in the poll.
   *
   * Format: `{optionID}_{option}` with `optionID = 0` if it is a new option.
   */
  public getOptions(): string[] {
    const options: string[] = [];
    Array.from(this.optionList.children).forEach((listItem: HTMLLIElement) => {
      const optionValue = (listItem.querySelector("input[type=text]") as HTMLInputElement).value.trim();

      if (optionValue !== "") {
        options.push(`${listItem.dataset.optionId!}_${optionValue}`);
      }
    });

    return options;
  }
}

export = UiPollEditor;
