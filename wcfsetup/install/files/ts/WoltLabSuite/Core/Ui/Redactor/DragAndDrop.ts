/**
 * Drag and Drop file uploads.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/DragAndDrop
 */

import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import { RedactorEditor } from "./Editor";

type Uuid = string;

interface EditorData {
  editor: RedactorEditor;
  element: HTMLElement | null;
}

let _didInit = false;
const _dragArea = new Map<Uuid, EditorData>();
let _isDragging = false;
let _isFile = false;
let _timerLeave: number | null = null;

/**
 * Handles items dragged into the browser window.
 */
function _dragOver(event: DragEvent): void {
  event.preventDefault();

  if (!event.dataTransfer || !event.dataTransfer.types) {
    return;
  }

  let isFirefox = false;
  Object.keys(event.dataTransfer).forEach((property) => {
    if (property.startsWith("moz")) {
      isFirefox = true;
    }
  });

  // IE and WebKit set 'Files', Firefox sets 'application/x-moz-file' for files being dragged
  // and Safari just provides 'Files' along with a huge list of garbage
  _isFile = false;
  if (isFirefox) {
    // Firefox sets the 'Files' type even if the user is just dragging an on-page element
    if (event.dataTransfer.types[0] === "application/x-moz-file") {
      _isFile = true;
    }
  } else {
    event.dataTransfer.types.forEach((type) => {
      if (type === "Files") {
        _isFile = true;
      }
    });
  }

  if (!_isFile) {
    // user is just dragging around some garbage, ignore it
    return;
  }

  if (_isDragging) {
    // user is still dragging the file around
    return;
  }

  _isDragging = true;

  _dragArea.forEach((data, uuid) => {
    const editor = data.editor.$editor[0];
    if (!editor.parentElement) {
      _dragArea.delete(uuid);
      return;
    }

    let element: HTMLElement | null = data.element;
    if (element === null) {
      element = document.createElement("div");
      element.className = "redactorDropArea";
      element.dataset.elementId = data.editor.$element[0].id;
      element.dataset.dropHere = Language.get("wcf.attachment.dragAndDrop.dropHere");
      element.dataset.dropNow = Language.get("wcf.attachment.dragAndDrop.dropNow");

      element.addEventListener("dragover", () => {
        element!.classList.add("active");
      });
      element.addEventListener("dragleave", () => {
        element!.classList.remove("active");
      });
      element.addEventListener("drop", (ev) => drop(ev));

      data.element = element;
    }

    editor.parentElement.insertBefore(element, editor);
    element.style.setProperty("top", `${editor.offsetTop}px`, "");
  });
}

/**
 * Handles items dropped onto an editor's drop area
 */
function drop(event: DragEvent): void {
  if (!_isFile) {
    return;
  }

  if (!event.dataTransfer || !event.dataTransfer.files.length) {
    return;
  }

  event.preventDefault();

  const target = event.currentTarget as HTMLElement;
  const elementId = target.dataset.elementId!;

  Array.from(event.dataTransfer.files).forEach((file) => {
    EventHandler.fire("com.woltlab.wcf.redactor2", `dragAndDrop_${elementId}`, {
      file,
    });
  });

  // this will reset all drop areas
  dragLeave();
}

/**
 * Invoked whenever the item is no longer dragged or was dropped.
 *
 * @protected
 */
function dragLeave() {
  if (!_isDragging || !_isFile) {
    return;
  }

  if (_timerLeave !== null) {
    window.clearTimeout(_timerLeave);
  }

  _timerLeave = window.setTimeout(() => {
    if (!_isDragging) {
      _dragArea.forEach((data) => {
        if (data.element && data.element.parentElement) {
          data.element.classList.remove("active");
          data.element.remove();
        }
      });
    }

    _timerLeave = null;
  }, 100);

  _isDragging = false;
}

/**
 * Handles the global drop event.
 */
function globalDrop(event: DragEvent): void {
  const target = event.target as HTMLElement;
  if (target.closest(".redactor-layer") === null) {
    const eventData = { cancelDrop: true, event: event };
    _dragArea.forEach((data) => {
      EventHandler.fire("com.woltlab.wcf.redactor2", `dragAndDrop_globalDrop_${data.editor.$element[0].id}`, eventData);
    });

    if (eventData.cancelDrop) {
      event.preventDefault();
    }
  }

  dragLeave();
}

/**
 * Binds listeners to global events.
 *
 * @protected
 */
function setup() {
  // discard garbage event
  window.addEventListener("dragend", (ev) => ev.preventDefault());

  window.addEventListener("dragover", (ev) => _dragOver(ev));
  window.addEventListener("dragleave", () => dragLeave());
  window.addEventListener("drop", (ev) => globalDrop(ev));

  _didInit = true;
}

/**
 * Initializes drag and drop support for provided editor instance.
 */
export function init(editor: RedactorEditor): void {
  if (!_didInit) {
    setup();
  }

  _dragArea.set(editor.uuid, {
    editor: editor,
    element: null,
  });
}
