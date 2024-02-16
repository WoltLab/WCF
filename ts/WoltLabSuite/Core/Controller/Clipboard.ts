/**
 * Clipboard API Handler.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import * as Ajax from "../Ajax";
import { AjaxCallbackSetup } from "../Ajax/Data";
import * as Core from "../Core";
import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import * as EventHandler from "../Event/Handler";
import * as Language from "../Language";
import * as UiConfirmation from "../Ui/Confirmation";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import * as UiPageAction from "../Ui/Page/Action";
import * as UiScreen from "../Ui/Screen";
import { ClipboardOptions, ContainerData, ClipboardActionData, AjaxResponse } from "./Clipboard/Data";

class ControllerClipboard {
  private readonly containers = new Map<string, ContainerData>();
  private readonly editors = new Map<string, HTMLAnchorElement>();
  private readonly editorDropdowns = new Map<string, HTMLOListElement>();
  private itemData = new WeakMap<HTMLLIElement, ClipboardActionData>();
  private readonly knownCheckboxes = new WeakSet<HTMLInputElement>();
  private readonly pageClassNames: string[] = [];
  private pageObjectId? = 0;
  private readonly reloadPageOnSuccess = new Map<string, string[]>();

  /**
   * Initializes the clipboard API handler.
   */
  setup(options: ClipboardOptions) {
    if (!options.pageClassName) {
      throw new Error("Expected a non-empty string for parameter 'pageClassName'.");
    }

    let hasMarkedItems = false;
    if (this.pageClassNames.length === 0) {
      hasMarkedItems = options.hasMarkedItems;
      this.pageObjectId = options.pageObjectId;
    }

    this.pageClassNames.push(options.pageClassName);

    this.initContainers();

    if (hasMarkedItems && this.containers.size) {
      this.loadMarkedItems();
    }

    DomChangeListener.add("WoltLabSuite/Core/Controller/Clipboard", () => this.initContainers());
  }

  /**
   * Reloads the clipboard data.
   */
  reload(): void {
    if (this.containers.size) {
      this.loadMarkedItems();
    }
  }

  /**
   * Initializes clipboard containers.
   */
  private initContainers(): void {
    document.querySelectorAll(".jsClipboardContainer").forEach((container: HTMLElement) => {
      const containerId = DomUtil.identify(container);

      let containerData = this.containers.get(containerId);
      if (containerData === undefined) {
        const markAll = container.querySelector(".jsClipboardMarkAll") as HTMLInputElement;

        if (markAll !== null) {
          markAll.dataset.containerId = containerId;
          markAll.addEventListener("click", (ev) => this.markAll(ev));
        }

        containerData = {
          checkboxes: container.getElementsByClassName("jsClipboardItem") as HTMLCollectionOf<HTMLInputElement>,
          element: container,
          markAll: markAll,
          markedObjectIds: new Set<number>(),
        };
        this.containers.set(containerId, containerData);
      }

      Array.from(containerData.checkboxes).forEach((checkbox) => {
        if (this.knownCheckboxes.has(checkbox)) {
          return;
        }

        checkbox.dataset.containerId = containerId;

        const link = checkbox.closest("a");
        if (link === null) {
          checkbox.addEventListener("click", (ev) => this.mark(ev));
        } else {
          // Firefox will always trigger the link if the checkbox is
          // inside of one. Since 2000. Thanks Firefox.
          checkbox.addEventListener("click", (event) => {
            event.preventDefault();

            window.setTimeout(() => {
              checkbox.checked = !checkbox.checked;

              this.mark(checkbox);
            }, 10);
          });
        }

        this.knownCheckboxes.add(checkbox);
      });
    });
  }

  /**
   * Loads marked items from clipboard.
   */
  private loadMarkedItems(): void {
    Ajax.api(this, {
      actionName: "getMarkedItems",
      parameters: {
        pageClassNames: this.pageClassNames,
        pageObjectID: this.pageObjectId,
      },
    });
  }

  /**
   * Marks or unmarks all visible items at once.
   */
  private markAll(event: MouseEvent): void {
    const checkbox = event.currentTarget as HTMLInputElement;
    const isMarked = checkbox.nodeName !== "INPUT" || checkbox.checked;

    this.setParentAsMarked(checkbox, isMarked);

    const objectIds: number[] = [];

    const containerId = checkbox.dataset.containerId!;
    const data = this.containers.get(containerId)!;
    const type = data.element.dataset.type!;

    Array.from(data.checkboxes).forEach((item) => {
      const objectId = ~~item.dataset.objectId!;

      if (isMarked) {
        if (!item.checked) {
          item.checked = true;

          data.markedObjectIds.add(objectId);
          objectIds.push(objectId);
        }
      } else {
        if (item.checked) {
          item.checked = false;

          data.markedObjectIds["delete"](objectId);
          objectIds.push(objectId);
        }
      }

      this.setParentAsMarked(item, isMarked);

      const clipboardObject = checkbox.closest(".jsClipboardObject");
      if (clipboardObject !== null) {
        if (isMarked) {
          clipboardObject.classList.add("jsMarked");
        } else {
          clipboardObject.classList.remove("jsMarked");
        }
      }
    });

    this.saveState(type, objectIds, isMarked);
  }

  /**
   * Marks or unmarks an individual item.
   *
   */
  private mark(event: MouseEvent | HTMLInputElement): void {
    const checkbox = event instanceof Event ? (event.currentTarget as HTMLInputElement) : event;

    const objectId = ~~checkbox.dataset.objectId!;
    const isMarked = checkbox.checked;
    const containerId = checkbox.dataset.containerId!;
    const data = this.containers.get(containerId)!;
    const type = data.element.dataset.type!;

    const clipboardObject = checkbox.closest(".jsClipboardObject") as HTMLElement;
    if (isMarked) {
      data.markedObjectIds.add(objectId);
      clipboardObject.classList.add("jsMarked");
    } else {
      data.markedObjectIds.delete(objectId);
      clipboardObject.classList.remove("jsMarked");
    }

    if (data.markAll !== null) {
      data.markAll.checked = !Array.from(data.checkboxes).some((item) => !item.checked);

      this.setParentAsMarked(data.markAll, isMarked);
    }

    this.setParentAsMarked(checkbox, checkbox.checked);

    this.saveState(type, [objectId], isMarked);
  }

  /**
   * Saves the state for given item object ids.
   */
  private saveState(objectType: string, objectIds: number[], isMarked: boolean): void {
    Ajax.api(this, {
      actionName: isMarked ? "mark" : "unmark",
      parameters: {
        pageClassNames: this.pageClassNames,
        pageObjectID: this.pageObjectId,
        objectIDs: objectIds,
        objectType,
      },
    });
  }

  /**
   * Executes an editor action.
   */
  private executeAction(event: MouseEvent): void {
    const listItem = event.currentTarget as HTMLLIElement;
    const data = this.itemData.get(listItem)!;

    if (data.url) {
      window.location.href = data.url;
      return;
    }

    function triggerEvent() {
      const type = listItem.dataset.type!;

      EventHandler.fire("com.woltlab.wcf.clipboard", type, {
        data,
        listItem,
        responseData: null,
      });
    }

    const message = typeof data.internalData.confirmMessage === "string" ? data.internalData.confirmMessage : "";
    let fireEvent = true;

    if (Core.isPlainObject(data.parameters) && data.parameters.actionName && data.parameters.className) {
      if (data.parameters.actionName === "unmarkAll" || Array.isArray(data.parameters.objectIDs)) {
        if (message.length) {
          const template = typeof data.internalData.template === "string" ? data.internalData.template : "";

          UiConfirmation.show({
            confirm: () => {
              const formData = {};

              if (template.length) {
                UiConfirmation.getContentElement()
                  .querySelectorAll("input, select, textarea")
                  .forEach((item: HTMLInputElement) => {
                    const name = item.name;

                    switch (item.nodeName) {
                      case "INPUT":
                        if ((item.type !== "checkbox" && item.type !== "radio") || item.checked) {
                          formData[name] = item.value;
                        }
                        break;

                      case "SELECT":
                        formData[name] = item.value;
                        break;

                      case "TEXTAREA":
                        formData[name] = item.value.trim();
                        break;
                    }
                  });
              }

              this.executeProxyAction(listItem, data, formData);
            },
            message,
            template,
          });
        } else {
          this.executeProxyAction(listItem, data);
        }
      }
    } else if (message.length) {
      fireEvent = false;

      UiConfirmation.show({
        confirm: triggerEvent,
        message,
      });
    }

    if (fireEvent) {
      triggerEvent();
    }
  }

  /**
   * Forwards clipboard actions to an individual handler.
   */
  private executeProxyAction(listItem: HTMLLIElement, data: ClipboardActionData, formData: ArbitraryObject = {}): void {
    const objectIds = data.parameters.actionName !== "unmarkAll" ? data.parameters.objectIDs : [];
    const parameters = { data: formData };

    if (Core.isPlainObject(data.internalData.parameters)) {
      Object.entries(data.internalData.parameters as ArbitraryObject).forEach(([key, value]) => {
        parameters[key] = value;
      });
    }

    Ajax.api(
      this,
      {
        actionName: data.parameters.actionName,
        className: data.parameters.className,
        objectIDs: objectIds,
        parameters,
      },
      (responseData: AjaxResponse) => {
        if (data.actionName !== "unmarkAll") {
          const type = listItem.dataset.type!;

          EventHandler.fire("com.woltlab.wcf.clipboard", type, {
            data,
            listItem,
            responseData,
          });

          const reloadPageOnSuccess = this.reloadPageOnSuccess.get(type);
          if (reloadPageOnSuccess && reloadPageOnSuccess.includes(responseData.actionName)) {
            window.location.reload();
            return;
          }
        }

        this.loadMarkedItems();
      },
    );
  }

  /**
   * Unmarks all clipboard items for an object type.
   */
  private unmarkAll(event: MouseEvent): void {
    const listItem = event.currentTarget as HTMLElement;

    Ajax.api(this, {
      actionName: "unmarkAll",
      parameters: {
        objectType: listItem.dataset.type!,
      },
    });
  }

  /**
   * Sets up ajax request object.
   */
  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\clipboard\\item\\ClipboardItemAction",
      },
    };
  }

  /**
   * Handles successful AJAX requests.
   */
  _ajaxSuccess(data: AjaxResponse): void {
    if (data.actionName === "unmarkAll") {
      const objectType = data.returnValues.objectType;
      this.containers.forEach((containerData) => {
        if (containerData.element.dataset.type !== objectType) {
          return;
        }

        containerData.element.querySelectorAll(".jsMarked").forEach((element) => element.classList.remove("jsMarked"));

        if (containerData.markAll !== null) {
          containerData.markAll.checked = false;

          this.setParentAsMarked(containerData.markAll, false);
        }

        Array.from(containerData.checkboxes).forEach((checkbox) => {
          checkbox.checked = false;

          this.setParentAsMarked(checkbox, false);
        });

        UiPageAction.remove(`wcfClipboard-${objectType}`);
      });

      return;
    }

    this.itemData = new WeakMap<HTMLLIElement, ClipboardActionData>();
    this.reloadPageOnSuccess.clear();

    // rebuild markings
    const markings = Core.isPlainObject(data.returnValues.markedItems) ? data.returnValues.markedItems! : {};
    this.containers.forEach((containerData) => {
      const typeName = containerData.element.dataset.type!;

      const objectIds = Array.isArray(markings[typeName]) ? markings[typeName] : [];
      this.rebuildMarkings(containerData, objectIds);
    });

    const keepEditors: string[] = Object.keys(data.returnValues.items || {});

    // clear editors
    this.editors.forEach((editor, typeName) => {
      if (!keepEditors.includes(typeName)) {
        UiPageAction.remove(`wcfClipboard-${typeName}`);

        this.editorDropdowns.get(typeName)!.innerHTML = "";
      }
    });

    // no items
    if (!data.returnValues.items) {
      return;
    }

    // rebuild editors
    Object.entries(data.returnValues.items).forEach(([typeName, typeData]) => {
      this.reloadPageOnSuccess.set(typeName, typeData.reloadPageOnSuccess);

      let created = false;

      let editor = this.editors.get(typeName);
      let dropdown = this.editorDropdowns.get(typeName)!;
      if (editor === undefined) {
        created = true;

        editor = document.createElement("a");
        editor.className = "dropdownToggle";
        editor.textContent = typeData.label;

        this.editors.set(typeName, editor);

        dropdown = document.createElement("ol");
        dropdown.className = "dropdownMenu";

        this.editorDropdowns.set(typeName, dropdown);
      } else {
        editor.textContent = typeData.label;
        dropdown.innerHTML = "";
      }

      // create editor items
      Object.values(typeData.items).forEach((itemData) => {
        const item = document.createElement("li");
        const label = document.createElement("span");
        label.textContent = itemData.label;
        item.appendChild(label);
        dropdown.appendChild(item);

        item.dataset.type = typeName;
        item.addEventListener("click", (ev) => this.executeAction(ev));

        this.itemData.set(item, itemData);
      });

      const divider = document.createElement("li");
      divider.classList.add("dropdownDivider");
      dropdown.appendChild(divider);

      // add 'unmark all'
      const unmarkAll = document.createElement("li");
      unmarkAll.dataset.type = typeName;
      const label = document.createElement("span");
      label.textContent = Language.get("wcf.clipboard.item.unmarkAll");
      unmarkAll.appendChild(label);
      unmarkAll.addEventListener("click", (ev) => this.unmarkAll(ev));
      dropdown.appendChild(unmarkAll);

      if (keepEditors.indexOf(typeName) !== -1) {
        const actionName = `wcfClipboard-${typeName}`;

        if (UiPageAction.has(actionName)) {
          UiPageAction.show(actionName);
        } else {
          UiPageAction.add(actionName, editor);
          created = true;
        }
      }

      if (created) {
        const parent = editor.parentElement!;
        parent.classList.add("dropdown");
        parent.appendChild(dropdown);
        UiDropdownSimple.init(editor);
      }
    });
  }

  /**
   * Rebuilds the mark state for each item.
   */
  private rebuildMarkings(data: ContainerData, objectIds: number[]): void {
    let markAll = true;

    Array.from(data.checkboxes).forEach((checkbox) => {
      const clipboardObject = checkbox.closest(".jsClipboardObject") as HTMLElement;

      const isMarked = objectIds.includes(~~checkbox.dataset.objectId!);
      if (!isMarked) {
        markAll = false;
      }

      checkbox.checked = isMarked;
      if (isMarked) {
        clipboardObject.classList.add("jsMarked");
      } else {
        clipboardObject.classList.remove("jsMarked");
      }

      this.setParentAsMarked(checkbox, isMarked);
    });

    if (data.markAll !== null) {
      data.markAll.checked = markAll;

      this.setParentAsMarked(data.markAll, markAll);

      const parent = data.markAll.closest(".columnMark")?.parentNode as HTMLElement | null;
      if (parent) {
        if (markAll) {
          parent.classList.add("jsMarked");
        } else {
          parent.classList.remove("jsMarked");
        }
      }
    }
  }

  private setParentAsMarked(element: HTMLElement, isMarked: boolean): void {
    const parent = element.parentElement!;
    if (parent.getAttribute("role") === "checkbox") {
      parent.setAttribute("aria-checked", isMarked ? "true" : "false");
    }
  }

  /**
   * Hides the clipboard editor for the given object type.
   */
  hideEditor(objectType: string): void {
    UiPageAction.remove("wcfClipboard-" + objectType);

    UiScreen.pageOverlayOpen();
  }

  /**
   * Shows the clipboard editor.
   */
  showEditor(): void {
    this.loadMarkedItems();

    UiScreen.pageOverlayClose();
  }

  /**
   * Unmarks the objects with given clipboard object type and ids.
   */
  unmark(objectType: string, objectIds: number[]): void {
    this.saveState(objectType, objectIds, false);
  }
}

let controllerClipboard: ControllerClipboard;

function getControllerClipboard(): ControllerClipboard {
  if (!controllerClipboard) {
    controllerClipboard = new ControllerClipboard();
  }

  return controllerClipboard;
}

/**
 * Initializes the clipboard API handler.
 */
export function setup(options: ClipboardOptions): void {
  getControllerClipboard().setup(options);
}

/**
 * Reloads the clipboard data.
 */
export function reload(): void {
  getControllerClipboard().reload();
}

/**
 * Hides the clipboard editor for the given object type.
 */
export function hideEditor(objectType: string): void {
  getControllerClipboard().hideEditor(objectType);
}

/**
 * Shows the clipboard editor.
 */
export function showEditor(): void {
  getControllerClipboard().showEditor();
}

/**
 * Unmarks the objects with given clipboard object type and ids.
 */
export function unmark(objectType: string, objectIds: number[]): void {
  getControllerClipboard().unmark(objectType, objectIds);
}
