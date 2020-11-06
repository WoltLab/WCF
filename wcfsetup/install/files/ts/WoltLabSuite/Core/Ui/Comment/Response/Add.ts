/**
 * Handles the comment response add feature.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Comment/Add
 */

import { AjaxCallbackSetup } from "../../../Ajax/Data";
import * as Core from "../../../Core";
import DomChangeListener from "../../../Dom/Change/Listener";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";
import UiCommentAdd from "../Add";
import * as UiNotification from "../../Notification";

type CallbackInsert = () => void;

interface ResponseAddOptions {
  callbackInsert: CallbackInsert | null;
}

interface AjaxResponse {
  returnValues: {
    guestDialog?: string;
    template: string;
  };
}

class UiCommentResponseAdd extends UiCommentAdd {
  protected _options: ResponseAddOptions;

  constructor(container: HTMLElement, options: Partial<ResponseAddOptions>) {
    super(container);

    this._options = Core.extend(
      {
        callbackInsert: null,
      },
      options,
    ) as ResponseAddOptions;
  }

  /**
   * Returns the editor container for placement.
   */
  getContainer(): HTMLElement {
    return this._container;
  }

  /**
   * Retrieves the current content from the editor.
   */
  getContent(): string {
    return window.jQuery(this._textarea).redactor("code.get") as string;
  }

  /**
   * Sets the content and places the caret at the end of the editor.
   */
  setContent(html: string): void {
    window.jQuery(this._textarea).redactor("code.set", html);
    window.jQuery(this._textarea).redactor("WoltLabCaret.endOfEditor");

    // the error message can appear anywhere in the container, not exclusively after the textarea
    const innerError = this._textarea.parentElement!.querySelector(".innerError");
    if (innerError !== null) {
      innerError.remove();
    }

    this._content.classList.remove("collapsed");
    this._focusEditor();
  }

  protected _getParameters(): ArbitraryObject {
    const parameters = super._getParameters();

    const comment = this._container.closest(".comment") as HTMLElement;
    (parameters.data as ArbitraryObject).commentID = ~~comment.dataset.objectId!;

    return parameters;
  }

  protected _insertMessage(data: AjaxResponse): HTMLElement {
    const commentContent = this._container.parentElement!.querySelector(".commentContent")!;
    let responseList = commentContent.nextElementSibling as HTMLElement;
    if (responseList === null || !responseList.classList.contains("commentResponseList")) {
      responseList = document.createElement("ul");
      responseList.className = "containerList commentResponseList";
      responseList.dataset.responses = "0";

      commentContent.insertAdjacentElement("afterend", responseList);
    }

    // insert HTML
    DomUtil.insertHtml(data.returnValues.template, responseList, "append");

    UiNotification.show(Language.get("wcf.global.success.add"));

    DomChangeListener.trigger();

    // reset editor
    window.jQuery(this._textarea).redactor("code.set", "");

    if (this._options.callbackInsert !== null) {
      this._options.callbackInsert();
    }

    // update counter
    responseList.dataset.responses = responseList.children.length.toString();

    return responseList.lastElementChild as HTMLElement;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    const data = super._ajaxSetup();
    (data.data as ArbitraryObject).actionName = "addResponse";

    return data;
  }
}

Core.enableLegacyInheritance(UiCommentResponseAdd);

export = UiCommentResponseAdd;
