/**
 * Provides editing support for comment responses.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Response/Edit
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackSetup } from "../../../Ajax/Data";
import * as Core from "../../../Core";
import DomChangeListener from "../../../Dom/Change/Listener";
import DomUtil from "../../../Dom/Util";
import UiCommentEdit from "../Edit";
import * as UiNotification from "../../Notification";

interface AjaxResponse {
  actionName: string;
  returnValues: {
    message: string;
    template: string;
  };
}

class UiCommentResponseEdit extends UiCommentEdit {
  protected readonly _responses = new WeakSet<HTMLElement>();

  /**
   * Initializes the comment edit manager.
   *
   * @param  {Element}       container       container element
   */
  constructor(container: HTMLElement) {
    super(container);

    this.rebuildResponses();

    DomChangeListener.add("Ui/Comment/Response/Edit_" + DomUtil.identify(this._container), () =>
      this.rebuildResponses(),
    );
  }

  rebuild(): void {
    // Do nothing, we want to avoid implicitly invoking `UiCommentEdit.rebuild()`.
  }

  /**
   * Initializes each applicable message, should be called whenever new
   * messages are being displayed.
   */
  rebuildResponses(): void {
    this._container.querySelectorAll(".commentResponse").forEach((response: HTMLElement) => {
      if (this._responses.has(response)) {
        return;
      }

      if (Core.stringToBool(response.dataset.canEdit || "")) {
        const button = response.querySelector(".jsCommentResponseEditButton") as HTMLAnchorElement;
        if (button !== null) {
          button.addEventListener("click", (ev) => this._click(ev));
        }
      }

      this._responses.add(response);
    });
  }

  /**
   * Handles clicks on the edit button.
   */
  protected _click(event: MouseEvent): void {
    event.preventDefault();

    if (this._activeElement === null) {
      const target = event.currentTarget as HTMLElement;
      this._activeElement = target.closest(".commentResponse") as HTMLElement;

      this._prepare();

      Ajax.api(this, {
        actionName: "beginEdit",
        objectIDs: [this._getObjectId(this._activeElement)],
      });
    } else {
      UiNotification.show("wcf.message.error.editorAlreadyInUse", null, "warning");
    }
  }

  /**
   * Prepares the message for editor display.
   *
   * @protected
   */
  protected _prepare(): void {
    this._editorContainer = document.createElement("div");
    this._editorContainer.className = "commentEditorContainer";
    this._editorContainer.innerHTML = '<span class="icon icon48 fa-spinner"></span>';

    const content = this._activeElement!.querySelector(".commentResponseContent")!;
    content.insertBefore(this._editorContainer, content.firstChild);
  }

  /**
   * Shows the update message.
   */
  protected _showMessage(data: AjaxResponse): void {
    // set new content
    const parent = this._editorContainer!.parentElement!;
    DomUtil.setInnerHtml(parent.querySelector(".commentResponseContent .userMessage")!, data.returnValues.message);

    this._restoreMessage();

    UiNotification.show();
  }

  /**
   * Returns the unique editor id.
   */
  protected _getEditorId(): string {
    return `commentResponseEditor${this._getObjectId(this._activeElement!)}`;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    const objectTypeId = ~~this._container.dataset.objectTypeId!;

    return {
      data: {
        className: "wcf\\data\\comment\\response\\CommentResponseAction",
        parameters: {
          data: {
            objectTypeID: objectTypeId,
          },
        },
      },
      silent: true,
    };
  }
}

Core.enableLegacyInheritance(UiCommentResponseEdit);

export = UiCommentResponseEdit;
