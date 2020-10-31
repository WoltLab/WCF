/**
 * Delete files which are uploaded via AJAX.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/File/Delete
 * @since  5.2
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, DatabaseObjectActionResponse } from "../../Ajax/Data";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import * as Language from "../../Language";

interface AjaxResponse extends DatabaseObjectActionResponse {
  uniqueFileId: string;
}

interface ElementData {
  uniqueFileId: string;
  element: HTMLElement;
}

class UiFileDelete implements AjaxCallbackObject {
  private readonly buttonContainer: HTMLElement;
  private readonly containers = new Map<string, ElementData>();
  private deleteButton?: HTMLElement = undefined;
  private readonly internalId: string;
  private readonly isSingleImagePreview: boolean;
  private readonly target: HTMLElement;
  // TODO: uploadHandler should not be `any`
  private readonly uploadHandler: any;

  // TODO: uploadHandler should not be `any`
  constructor(buttonContainerId: string, targetId: string, isSingleImagePreview: boolean, uploadHandler: any) {
    this.isSingleImagePreview = isSingleImagePreview;
    this.uploadHandler = uploadHandler;

    const buttonContainer = document.getElementById(buttonContainerId);
    if (buttonContainer === null) {
      throw new Error(`Element id '${buttonContainerId}' is unknown.`);
    }
    this.buttonContainer = buttonContainer;

    const target = document.getElementById(targetId);
    if (target === null) {
      throw new Error(`Element id '${targetId}' is unknown.`);
    }
    this.target = target;

    const internalId = this.target.dataset.internalId;
    if (!internalId) {
      throw new Error("InternalId is unknown.");
    }
    this.internalId = internalId;

    this.rebuild();
  }

  /**
   * Creates the upload button.
   */
  private createButtons(): void {
    let triggerChange = false;
    this.target.querySelectorAll("li.uploadedFile").forEach((element: HTMLElement) => {
      const uniqueFileId = element.dataset.uniqueFileId!;
      if (this.containers.has(uniqueFileId)) {
        return;
      }

      const elementData: ElementData = {
        uniqueFileId: uniqueFileId,
        element: element,
      };

      this.containers.set(uniqueFileId, elementData);
      this.initDeleteButton(element, elementData);

      triggerChange = true;
    });

    if (triggerChange) {
      DomChangeListener.trigger();
    }
  }

  /**
   * Init the delete button for a specific element.
   */
  private initDeleteButton(element: HTMLElement, elementData: ElementData): void {
    const buttonGroup = element.querySelector(".buttonGroup");
    if (buttonGroup === null) {
      throw new Error(`Button group in '${this.target.id}' is unknown.`);
    }

    const li = document.createElement("li");
    const span = document.createElement("span");
    span.className = "button jsDeleteButton small";
    span.textContent = Language.get("wcf.global.button.delete");
    li.appendChild(span);
    buttonGroup.appendChild(li);

    li.addEventListener("click", this.deleteElement.bind(this, elementData.uniqueFileId));
  }

  /**
   * Delete a specific file with the given uniqueFileId.
   */
  private deleteElement(uniqueFileId: string): void {
    Ajax.api(this, {
      uniqueFileId: uniqueFileId,
      internalId: this.internalId,
    });
  }

  /**
   * Rebuilds the delete buttons for unknown files.
   */
  rebuild(): void {
    if (!this.isSingleImagePreview) {
      this.createButtons();
      return;
    }

    const img = this.target.querySelector("img");
    if (img !== null) {
      const uniqueFileId = img.dataset.uniqueFileId!;

      if (!this.containers.has(uniqueFileId)) {
        const elementData = {
          uniqueFileId: uniqueFileId,
          element: img,
        };

        this.containers.set(uniqueFileId, elementData);

        this.deleteButton = document.createElement("p");
        this.deleteButton.className = "button deleteButton";

        const span = document.createElement("span");
        span.textContent = Language.get("wcf.global.button.delete");
        this.deleteButton.appendChild(span);

        this.buttonContainer.appendChild(this.deleteButton);

        this.deleteButton.addEventListener("click", this.deleteElement.bind(this, elementData.uniqueFileId));
      }
    }
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const elementData = this.containers.get(data.uniqueFileId)!;
    elementData.element.remove();

    if (this.isSingleImagePreview && this.deleteButton) {
      this.deleteButton.remove();
      this.deleteButton = undefined;
    }

    this.uploadHandler.checkMaxFiles();
    Core.triggerEvent(this.target, "change");
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      url: "index.php?ajax-file-delete/&t=" + window.SECURITY_TOKEN,
    };
  }
}

Core.enableLegacyInheritance(UiFileDelete);

export = UiFileDelete;
