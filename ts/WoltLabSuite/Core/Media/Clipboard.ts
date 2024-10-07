/**
 * Initializes modules required for media clipboard.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import MediaManager from "./Manager/Base";
import MediaManagerEditor from "./Manager/Editor";
import * as Clipboard from "../Controller/Clipboard";
import * as UiNotification from "../Ui/Notification";
import * as EventHandler from "../Event/Handler";
import { getPhrase } from "../Language";
import * as Ajax from "../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../Ajax/Data";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";

let _mediaManager: MediaManager;
let _didInit = false;

class MediaClipboard implements AjaxCallbackObject {
  #dialog?: WoltlabCoreDialogElement;

  public _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\media\\MediaAction",
      },
    };
  }

  public _ajaxSuccess(data): void {
    switch (data.actionName) {
      case "getSetCategoryDialog":
        this.#dialog = dialogFactory().fromHtml(data.returnValues.template).asConfirmation();
        this.#dialog.show(getPhrase("wcf.media.setCategory"));
        this.#dialog.addEventListener("primary", () => {
          const category = this.#dialog!.content.querySelector('select[name="categoryID"]') as HTMLSelectElement;
          setCategory(~~category.value);
        });

        break;

      case "setCategory":
        this.#dialog?.close();

        UiNotification.show();

        Clipboard.reload();

        break;
    }
  }
}

const ajax = new MediaClipboard();

let clipboardObjectIds: number[] = [];

interface ClipboardActionData {
  data: {
    actionName: "com.woltlab.wcf.media.delete" | "com.woltlab.wcf.media.insert" | "com.woltlab.wcf.media.setCategory";
    parameters: {
      objectIDs: number[];
    };
  };
  responseData: null;
}

/**
 * Handles successful clipboard actions.
 */
function clipboardAction(actionData: ClipboardActionData): void {
  const mediaIds = actionData.data.parameters.objectIDs;

  switch (actionData.data.actionName) {
    case "com.woltlab.wcf.media.delete":
      // only consider events if the action has been executed
      if (actionData.responseData !== null) {
        _mediaManager.clipboardDeleteMedia(mediaIds);
      }

      break;

    case "com.woltlab.wcf.media.insert": {
      const mediaManagerEditor = _mediaManager as MediaManagerEditor;
      mediaManagerEditor.clipboardInsertMedia(mediaIds);

      break;
    }

    case "com.woltlab.wcf.media.setCategory":
      clipboardObjectIds = mediaIds;

      Ajax.api(ajax, {
        actionName: "getSetCategoryDialog",
      });

      break;
  }
}

/**
 * Sets the category of the marked media files.
 */
function setCategory(categoryID: number) {
  Ajax.api(ajax, {
    actionName: "setCategory",
    objectIDs: clipboardObjectIds,
    parameters: {
      categoryID: categoryID,
    },
  });
}

export function init(pageClassName: string, hasMarkedItems: boolean, mediaManager: MediaManager): void {
  if (!_didInit) {
    Clipboard.setup({
      hasMarkedItems: hasMarkedItems,
      pageClassName: pageClassName,
    });

    EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.media", (data) => clipboardAction(data));

    _didInit = true;
  }

  _mediaManager = mediaManager;
}

export function setMediaManager(mediaManager: MediaManager): void {
  _mediaManager = mediaManager;
}
