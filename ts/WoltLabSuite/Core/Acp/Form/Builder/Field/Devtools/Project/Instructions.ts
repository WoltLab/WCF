/**
 * Manages the instructions entered in a devtools project instructions form field.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/Instructions
 * @since 5.2
 */

import * as Core from "../../../../../../Core";
import Template from "../../../../../../Template";
import * as Language from "../../../../../../Language";
import * as DomTraverse from "../../../../../../Dom/Traverse";
import DomChangeListener from "../../../../../../Dom/Change/Listener";
import DomUtil from "../../../../../../Dom/Util";
import UiSortableList from "../../../../../../Ui/Sortable/List";
import UiDialog from "../../../../../../Ui/Dialog";
import * as UiConfirmation from "../../../../../../Ui/Confirmation";

interface Instruction {
  application: string;
  errors?: string[];
  pip: string;
  runStandalone: number;
  value: string;
}

interface InstructionsData {
  errors?: string[];
  fromVersion?: string;
  instructions?: Instruction[];
  type: InstructionsType;
}

type InstructionsType = "install" | "update";
type InstructionsId = number | string;
type PipFilenameMap = { [k: string]: string };

class Instructions {
  protected readonly addButton: HTMLAnchorElement;
  protected readonly form: HTMLFormElement;
  protected readonly formFieldId: string;
  protected readonly fromVersion: HTMLInputElement;
  protected instructionCounter = 0;
  protected instructionsCounter = 0;
  protected readonly instructionsEditDialogTemplate: Template;
  protected readonly instructionsList: HTMLUListElement;
  protected readonly instructionsType: HTMLSelectElement;
  protected readonly instructionsTemplate: Template;
  protected readonly instructionEditDialogTemplate: Template;
  protected readonly pipDefaultFilenames: PipFilenameMap;

  protected static readonly applicationPips = ["acpTemplate", "file", "script", "template"];

  // see `wcf\data\package\Package::isValidPackageName()`
  protected static readonly packageIdentifierRegExp = new RegExp(/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/);

  // see `wcf\data\package\Package::isValidVersion()`
  protected static readonly versionRegExp = new RegExp(
    /^([0-9]+).([0-9]+)\.([0-9]+)( (a|alpha|b|beta|d|dev|rc|pl) ([0-9]+))?$/i,
  );

  constructor(
    formFieldId: string,
    instructionsTemplate: Template,
    instructionsEditDialogTemplate: Template,
    instructionEditDialogTemplate: Template,
    pipDefaultFilenames: PipFilenameMap,
    existingInstructions: InstructionsData[],
  ) {
    this.formFieldId = formFieldId;
    this.instructionsTemplate = instructionsTemplate;
    this.instructionsEditDialogTemplate = instructionsEditDialogTemplate;
    this.instructionEditDialogTemplate = instructionEditDialogTemplate;
    this.pipDefaultFilenames = pipDefaultFilenames;

    this.instructionsList = document.getElementById(`${this.formFieldId}_instructionsList`) as HTMLUListElement;
    if (this.instructionsList === null) {
      throw new Error(`Cannot find package list for packages field with id '${this.formFieldId}'.`);
    }

    this.instructionsType = document.getElementById(`${this.formFieldId}_instructionsType`) as HTMLSelectElement;
    if (this.instructionsType === null) {
      throw new Error(`Cannot find instruction type form field for instructions field with id '${this.formFieldId}'.`);
    }
    this.instructionsType.addEventListener("change", () => this.toggleFromVersionFormField());

    this.fromVersion = document.getElementById(`${this.formFieldId}_fromVersion`) as HTMLInputElement;
    if (this.fromVersion === null) {
      throw new Error(`Cannot find from version form field for instructions field with id '${this.formFieldId}'.`);
    }
    this.fromVersion.addEventListener("keypress", (ev) => this.instructionsKeyPress(ev));

    this.addButton = document.getElementById(`${this.formFieldId}_addButton`) as HTMLAnchorElement;
    if (this.addButton === null) {
      throw new Error(`Cannot find add button form field for instructions field with id '${this.formFieldId}'.`);
    }
    this.addButton.addEventListener("click", (ev) => this.addInstructions(ev));

    this.form = this.instructionsList.closest("form")!;
    if (this.form === null) {
      throw new Error(`Cannot find form element for instructions field with id '${this.formFieldId}'.`);
    }
    this.form.addEventListener("submit", () => this.submit());

    const hasInstallInstructions = existingInstructions.some((instructions) => instructions.type === "install");

    // ensure that there are always installation instructions
    if (!hasInstallInstructions) {
      this.addInstructionsByData({
        fromVersion: "",
        type: "install",
      });
    }

    existingInstructions.forEach((instructions) => this.addInstructionsByData(instructions));

    DomChangeListener.trigger();
  }

  /**
   * Adds an instruction to a set of instructions as a consequence of the given event.
   * If the instruction data is invalid, an error message is shown and no instruction is added.
   */
  protected addInstruction(event: Event): void {
    event.preventDefault();
    event.stopPropagation();

    const instructionsId = ((event.currentTarget as HTMLElement).closest("li.section") as HTMLElement).dataset
      .instructionsId!;

    // note: data will be validated/filtered by the server

    const pipField = document.getElementById(
      `${this.formFieldId}_instructions${instructionsId}_pip`,
    ) as HTMLInputElement;

    // ignore pressing button if no PIP has been selected
    if (!pipField.value) {
      return;
    }

    const valueField = document.getElementById(
      `${this.formFieldId}_instructions${instructionsId}_value`,
    ) as HTMLInputElement;
    const runStandaloneField = document.getElementById(
      `${this.formFieldId}_instructions${instructionsId}_runStandalone`,
    ) as HTMLInputElement;
    const applicationField = document.getElementById(
      `${this.formFieldId}_instructions${instructionsId}_application`,
    ) as HTMLSelectElement;

    this.addInstructionByData(instructionsId, {
      application: Instructions.applicationPips.indexOf(pipField.value) !== -1 ? applicationField.value : "",
      pip: pipField.value,
      runStandalone: ~~runStandaloneField.checked,
      value: valueField.value,
    });

    // empty fields
    pipField.value = "";
    valueField.value = "";
    runStandaloneField.checked = false;
    applicationField.value = "";
    document.getElementById(`${this.formFieldId}_instructions${instructionsId}_valueDescription`)!.innerHTML =
      Language.get("wcf.acp.devtools.project.instruction.value.description");
    this.toggleApplicationFormField(instructionsId);

    DomChangeListener.trigger();
  }

  /**
   * Adds an instruction to the set of instructions with the given id.
   */
  protected addInstructionByData(instructionsId: InstructionsId, instructionData: Instruction): void {
    const instructionId = ++this.instructionCounter;

    const instructionList = document.getElementById(
      `${this.formFieldId}_instructions${instructionsId}_instructionList`,
    )!;

    const listItem = document.createElement("li");
    listItem.className = "sortableNode";
    listItem.id = `${this.formFieldId}_instruction${instructionId}`;
    listItem.dataset.instructionId = instructionId.toString();
    listItem.dataset.application = instructionData.application;
    listItem.dataset.pip = instructionData.pip;
    listItem.dataset.runStandalone = instructionData.runStandalone ? "1" : "0";
    listItem.dataset.value = instructionData.value;

    let content = `
      <div class="sortableNodeLabel">
        <div class="jsDevtoolsProjectInstruction">
          ${Language.get("wcf.acp.devtools.project.instruction.instruction", instructionData)}
    `;

    if (instructionData.errors) {
      instructionData.errors.forEach((error) => {
        content += `<small class="innerError">${error}</small>`;
      });
    }

    content += `
        </div>
        <span class="statusDisplay sortableButtonContainer">
          <span class="icon icon16 fa-pencil pointer jsTooltip" id="${
            this.formFieldId
          }_instruction${instructionId}_editButton" title="${Language.get("wcf.global.button.edit")}"></span>
          <span class="icon icon16 fa-times pointer jsTooltip" id="${
            this.formFieldId
          }_instruction${instructionId}_deleteButton" title="${Language.get("wcf.global.button.delete")}"></span>
        </span>
      </div>
    `;

    listItem.innerHTML = content;

    instructionList.appendChild(listItem);

    document
      .getElementById(`${this.formFieldId}_instruction${instructionId}_deleteButton`)!
      .addEventListener("click", (ev) => this.removeInstruction(ev));
    document
      .getElementById(`${this.formFieldId}_instruction${instructionId}_editButton`)!
      .addEventListener("click", (ev) => this.editInstruction(ev));
  }

  /**
   * Adds a set of instructions.
   *
   * If the instructions data is invalid, an error message is shown and no instruction set is added.
   */
  protected addInstructions(event: Event): void {
    event.preventDefault();
    event.stopPropagation();

    // validate data
    if (
      !this.validateInstructionsType() ||
      (this.instructionsType.value === "update" && !this.validateFromVersion(this.fromVersion))
    ) {
      return;
    }

    this.addInstructionsByData({
      fromVersion: this.instructionsType.value === "update" ? this.fromVersion.value : "",
      type: this.instructionsType.value as InstructionsType,
    });

    // empty fields
    this.instructionsType.value = "";
    this.fromVersion.value = "";

    this.toggleFromVersionFormField();

    DomChangeListener.trigger();
  }

  /**
   * Adds a set of instructions.
   */
  protected addInstructionsByData(instructionsData: InstructionsData): void {
    const instructionsId = ++this.instructionsCounter;

    const listItem = document.createElement("li");
    listItem.className = "section";
    listItem.innerHTML = this.instructionsTemplate.fetch({
      instructionsId: instructionsId,
      sectionTitle: Language.get(`wcf.acp.devtools.project.instructions.type.${instructionsData.type}.title`, {
        fromVersion: instructionsData.fromVersion,
      }),
      type: instructionsData.type,
    });
    listItem.id = `${this.formFieldId}_instructions${instructionsId}`;
    listItem.dataset.instructionsId = instructionsId.toString();
    listItem.dataset.type = instructionsData.type;
    listItem.dataset.fromVersion = instructionsData.fromVersion;

    this.instructionsList.appendChild(listItem);

    const instructionListContainer = document.getElementById(
      `${this.formFieldId}_instructions${instructionsId}_instructionListContainer`,
    )!;
    if (Array.isArray(instructionsData.errors)) {
      instructionsData.errors.forEach((errorMessage) => {
        DomUtil.innerError(instructionListContainer, errorMessage, true);
      });
    }

    new UiSortableList({
      containerId: instructionListContainer.id,
      isSimpleSorting: true,
      options: {
        toleranceElement: "> div",
      },
    });

    if (instructionsData.type === "update") {
      document
        .getElementById(`${this.formFieldId}_instructions${instructionsId}_deleteButton`)!
        .addEventListener("click", (ev) => this.removeInstructions(ev));
      document
        .getElementById(`${this.formFieldId}_instructions${instructionsId}_editButton`)!
        .addEventListener("click", (ev) => this.editInstructions(ev));
    }

    document
      .getElementById(`${this.formFieldId}_instructions${instructionsId}_pip`)!
      .addEventListener("change", (ev) => this.changeInstructionPip(ev));

    document
      .getElementById(`${this.formFieldId}_instructions${instructionsId}_value`)!
      .addEventListener("keypress", (ev) => this.instructionKeyPress(ev));

    document
      .getElementById(`${this.formFieldId}_instructions${instructionsId}_addButton`)!
      .addEventListener("click", (ev) => this.addInstruction(ev));

    if (instructionsData.instructions) {
      instructionsData.instructions.forEach((instruction) => {
        this.addInstructionByData(instructionsId, instruction);
      });
    }
  }

  /**
   * Is called if the selected package installation plugin of an instruction is changed.
   */
  protected changeInstructionPip(event: Event): void {
    const target = event.currentTarget as HTMLInputElement;

    const pip = target.value;
    const instructionsId = (target.closest("li.section") as HTMLElement).dataset.instructionsId!;
    const description = document.getElementById(`${this.formFieldId}_instructions${instructionsId}_valueDescription`)!;

    // update value description
    if (this.pipDefaultFilenames[pip] !== "") {
      description.innerHTML = Language.get("wcf.acp.devtools.project.instruction.value.description.defaultFilename", {
        defaultFilename: this.pipDefaultFilenames[pip],
      });
    } else {
      description.innerHTML = Language.get("wcf.acp.devtools.project.instruction.value.description");
    }

    // toggle application selector
    this.toggleApplicationFormField(instructionsId);
  }

  /**
   * Opens a dialog to edit an existing instruction.
   */
  protected editInstruction(event: Event): void {
    const listItem = (event.currentTarget as HTMLElement).closest("li")!;

    const instructionId = listItem.dataset.instructionId!;
    const application = listItem.dataset.application!;
    const pip = listItem.dataset.pip!;
    const runStandalone = Core.stringToBool(listItem.dataset.runStandalone!);
    const value = listItem.dataset.value!;

    const dialogContent = this.instructionEditDialogTemplate.fetch({
      runStandalone: runStandalone,
      value: value,
    });

    const dialogId = "instructionEditDialog" + instructionId;
    if (!UiDialog.getDialog(dialogId)) {
      UiDialog.openStatic(dialogId, dialogContent, {
        onSetup: (content) => {
          const applicationSelect = content.querySelector("select[name=application]") as HTMLSelectElement;
          const pipSelect = content.querySelector("select[name=pip]") as HTMLInputElement;
          const runStandaloneInput = content.querySelector("input[name=runStandalone]") as HTMLInputElement;
          const valueInput = content.querySelector("input[name=value]") as HTMLInputElement;

          // set values of `select` elements
          applicationSelect.value = application;
          pipSelect.value = pip;

          const submit = () => {
            const listItem = document.getElementById(`${this.formFieldId}_instruction${instructionId}`)!;
            listItem.dataset.application =
              Instructions.applicationPips.indexOf(pipSelect.value) !== -1 ? applicationSelect.value : "";
            listItem.dataset.pip = pipSelect.value;
            listItem.dataset.runStandalone = runStandaloneInput.checked ? "1" : "0";
            listItem.dataset.value = valueInput.value;

            // note: data will be validated/filtered by the server

            listItem.querySelector(".jsDevtoolsProjectInstruction")!.innerHTML = Language.get(
              "wcf.acp.devtools.project.instruction.instruction",
              {
                application: listItem.dataset.application,
                pip: listItem.dataset.pip,
                runStandalone: Core.stringToBool(listItem.dataset.runStandalone),
                value: listItem.dataset.value,
              },
            );

            DomChangeListener.trigger();

            UiDialog.close(dialogId);
          };

          valueInput.addEventListener("keypress", (event) => {
            if (event.key === "Enter") {
              submit();
            }
          });

          content.querySelector("button[data-type=submit]")!.addEventListener("click", submit);

          const pipChange = () => {
            const pip = pipSelect.value;

            if (Instructions.applicationPips.indexOf(pip) !== -1) {
              DomUtil.show(applicationSelect.closest("dl")!);
            } else {
              DomUtil.hide(applicationSelect.closest("dl")!);
            }

            const description = DomTraverse.nextByTag(valueInput, "SMALL")!;
            if (this.pipDefaultFilenames[pip] !== "") {
              description.innerHTML = Language.get(
                "wcf.acp.devtools.project.instruction.value.description.defaultFilename",
                {
                  defaultFilename: this.pipDefaultFilenames[pip],
                },
              );
            } else {
              description.innerHTML = Language.get("wcf.acp.devtools.project.instruction.value.description");
            }
          };

          pipSelect.addEventListener("change", pipChange);
          pipChange();
        },
        title: Language.get("wcf.acp.devtools.project.instruction.edit"),
      });
    } else {
      UiDialog.openStatic(dialogId, null);
    }
  }

  /**
   * Opens a dialog to edit an existing set of instructions.
   */
  protected editInstructions(event: Event): void {
    const listItem = (event.currentTarget as HTMLElement).closest("li")!;

    const instructionsId = listItem.dataset.instructionsId!;
    const fromVersion = listItem.dataset.fromVersion;

    const dialogContent = this.instructionsEditDialogTemplate.fetch({
      fromVersion: fromVersion,
    });

    const dialogId = "instructionsEditDialog" + instructionsId;
    if (!UiDialog.getDialog(dialogId)) {
      UiDialog.openStatic(dialogId, dialogContent, {
        onSetup: (content) => {
          const fromVersion = content.querySelector("input[name=fromVersion]") as HTMLInputElement;

          const submit = () => {
            if (!this.validateFromVersion(fromVersion)) {
              return;
            }

            const instructions = document.getElementById(`${this.formFieldId}_instructions${instructionsId}`)!;
            instructions.dataset.fromVersion = fromVersion.value;

            instructions.querySelector(".jsInstructionsTitle")!.innerHTML = Language.get(
              "wcf.acp.devtools.project.instructions.type.update.title",
              {
                fromVersion: fromVersion.value,
              },
            );

            DomChangeListener.trigger();

            UiDialog.close(dialogId);
          };

          fromVersion.addEventListener("keypress", (event) => {
            if (event.key === "Enter") {
              submit();
            }
          });

          content.querySelector("button[data-type=submit]")!.addEventListener("click", submit);
        },
        title: Language.get("wcf.acp.devtools.project.instructions.edit"),
      });
    } else {
      UiDialog.openStatic(dialogId, null);
    }
  }

  /**
   * Adds an instruction after pressing ENTER in a relevant text field.
   */
  protected instructionKeyPress(event: KeyboardEvent): void {
    if (event.key === "Enter") {
      this.addInstruction(event);
    }
  }

  /**
   * Adds a set of instruction after pressing ENTER in a relevant text field.
   */
  protected instructionsKeyPress(event: KeyboardEvent): void {
    if (event.key === "Enter") {
      this.addInstructions(event);
    }
  }

  /**
   * Removes an instruction by clicking on its delete button.
   */
  protected removeInstruction(event: Event): void {
    const instruction = (event.currentTarget as HTMLElement).closest("li")!;

    UiConfirmation.show({
      confirm: () => {
        instruction.remove();
      },
      message: Language.get("wcf.acp.devtools.project.instruction.delete.confirmMessages"),
    });
  }

  /**
   * Removes a set of instructions by clicking on its delete button.
   *
   * @param	{Event}		event		delete button click event
   */
  protected removeInstructions(event: Event): void {
    const instructions = (event.currentTarget as HTMLElement).closest("li")!;

    UiConfirmation.show({
      confirm: () => {
        instructions.remove();
      },
      message: Language.get("wcf.acp.devtools.project.instructions.delete.confirmMessages"),
    });
  }

  /**
   * Adds all necessary (hidden) form fields to the form when submitting the form.
   */
  protected submit(): void {
    DomTraverse.childrenByTag(this.instructionsList, "LI").forEach((instructions, instructionsIndex) => {
      const namePrefix = `${this.formFieldId}[${instructionsIndex}]`;

      const instructionsType = document.createElement("input");
      instructionsType.type = "hidden";
      instructionsType.name = `${namePrefix}[type]`;
      instructionsType.value = instructions.dataset.type!;
      this.form.appendChild(instructionsType);

      if (instructionsType.value === "update") {
        const fromVersion = document.createElement("input");
        fromVersion.type = "hidden";
        fromVersion.name = `${this.formFieldId}[${instructionsIndex}][fromVersion]`;
        fromVersion.value = instructions.dataset.fromVersion!;
        this.form.appendChild(fromVersion);
      }

      DomTraverse.childrenByTag(document.getElementById(`${instructions.id}_instructionList`)!, "LI").forEach(
        (instruction, instructionIndex) => {
          const namePrefix = `${this.formFieldId}[${instructionsIndex}][instructions][${instructionIndex}]`;

          ["pip", "value", "runStandalone"].forEach((property) => {
            const element = document.createElement("input");
            element.type = "hidden";
            element.name = `${namePrefix}[${property}]`;
            element.value = instruction.dataset[property]!;
            this.form.appendChild(element);
          });

          if (Instructions.applicationPips.indexOf(instruction.dataset.pip!) !== -1) {
            const application = document.createElement("input");
            application.type = "hidden";
            application.name = `${namePrefix}[application]`;
            application.value = instruction.dataset.application!;
            this.form.appendChild(application);
          }
        },
      );
    });
  }

  /**
   * Toggles the visibility of the application form field based on the selected pip for the instructions with the given id.
   */
  protected toggleApplicationFormField(instructionsId: InstructionsId): void {
    const pip = (document.getElementById(`${this.formFieldId}_instructions${instructionsId}_pip`) as HTMLInputElement)
      .value;

    const valueDlClassList = document
      .getElementById(`${this.formFieldId}_instructions${instructionsId}_value`)!
      .closest("dl")!.classList;
    const applicationDl = document
      .getElementById(`${this.formFieldId}_instructions${instructionsId}_application`)!
      .closest("dl")!;

    if (Instructions.applicationPips.indexOf(pip) !== -1) {
      valueDlClassList.remove("col-md-9");
      valueDlClassList.add("col-md-7");
      DomUtil.show(applicationDl);
    } else {
      valueDlClassList.remove("col-md-7");
      valueDlClassList.add("col-md-9");
      DomUtil.hide(applicationDl);
    }
  }

  /**
   * Toggles the visibility of the `fromVersion` form field based on the selected instructions type.
   */
  protected toggleFromVersionFormField(): void {
    const instructionsTypeList = this.instructionsType.closest("dl")!.classList;
    const fromVersionDl = this.fromVersion.closest("dl")!;

    if (this.instructionsType.value === "update") {
      instructionsTypeList.remove("col-md-10");
      instructionsTypeList.add("col-md-5");
      DomUtil.show(fromVersionDl);
    } else {
      instructionsTypeList.remove("col-md-5");
      instructionsTypeList.add("col-md-10");
      DomUtil.hide(fromVersionDl);
    }
  }

  /**
   * Returns `true` if the currently entered update "from version" is valid. Otherwise `false` is returned and an error
   * message is shown.
   */
  protected validateFromVersion(inputField: HTMLInputElement): boolean {
    const version = inputField.value;

    if (version === "") {
      DomUtil.innerError(inputField, Language.get("wcf.global.form.error.empty"));

      return false;
    }

    if (version.length > 50) {
      DomUtil.innerError(inputField, Language.get("wcf.acp.devtools.project.packageVersion.error.maximumLength"));

      return false;
    }

    // Allow either a single asterisk, an asterisk in the last digit or a regular version.
    if (version !== '*') {
      if (!Instructions.versionRegExp.test(version.replace(/\.\*$/, ".0"))) {
        DomUtil.innerError(inputField, Language.get("wcf.acp.devtools.project.packageVersion.error.format"));

        return false;
      }
    }

    // remove outdated errors
    DomUtil.innerError(inputField, "");

    return true;
  }

  /**
   * Returns `true` if the entered update instructions type is valid.
   * Otherwise `false` is returned and an error message is shown.
   */
  protected validateInstructionsType(): boolean {
    if (this.instructionsType.value !== "install" && this.instructionsType.value !== "update") {
      if (this.instructionsType.value === "") {
        DomUtil.innerError(this.instructionsType, Language.get("wcf.global.form.error.empty"));
      } else {
        DomUtil.innerError(this.instructionsType, Language.get("wcf.global.form.error.noValidSelection"));
      }

      return false;
    }

    // there may only be one set of installation instructions
    if (this.instructionsType.value === "install") {
      const hasInstall = Array.from(this.instructionsList.children).some(
        (instructions: HTMLElement) => instructions.dataset.type === "install",
      );

      if (hasInstall) {
        DomUtil.innerError(
          this.instructionsType,
          Language.get("wcf.acp.devtools.project.instructions.type.update.error.duplicate"),
        );

        return false;
      }
    }

    // remove outdated errors
    DomUtil.innerError(this.instructionsType, "");

    return true;
  }
}

Core.enableLegacyInheritance(Instructions);

export = Instructions;
