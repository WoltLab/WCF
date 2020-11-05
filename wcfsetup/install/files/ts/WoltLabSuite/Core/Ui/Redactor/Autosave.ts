/**
 * Manages the autosave process storing the current editor message in the local
 * storage to recover it on browser crash or accidental navigation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Redactor/Autosave
 */

import * as Core from "../../Core";
import Devtools from "../../Devtools";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import { RedactorEditor } from "./Editor";
import * as UiRedactorMetacode from "./Metacode";

interface AutosaveMetaData {
  [key: string]: unknown;
}

interface AutosaveContent {
  content: string;
  meta: AutosaveMetaData;
  timestamp: number;
}

// time between save requests in seconds
const _frequency = 15;

class UiRedactorAutosave {
  protected _container: HTMLElement | null = null;
  protected _editor: RedactorEditor | null = null;
  protected readonly _element: HTMLTextAreaElement;
  protected _isActive = true;
  protected _isPending = false;
  protected readonly _key: string;
  protected _lastMessage = "";
  protected _metaData: AutosaveMetaData = {};
  protected _originalMessage = "";
  protected _restored = false;
  protected _timer: number | null = null;

  /**
   * Initializes the autosave handler and removes outdated messages from storage.
   *
   * @param       {Element}       element         textarea element
   */
  constructor(element: HTMLTextAreaElement) {
    this._element = element;
    this._key = Core.getStoragePrefix() + this._element.dataset.autosave!;

    this._cleanup();

    // remove attribute to prevent Redactor's built-in autosave to kick in
    delete this._element.dataset.autosave;

    const form = this._element.closest("form");
    if (form !== null) {
      form.addEventListener("submit", this.destroy.bind(this));
    }

    // export meta data
    EventHandler.add("com.woltlab.wcf.redactor2", `getMetaData_${this._element.id}`, (data: AutosaveMetaData) => {
      Object.entries(this._metaData).forEach(([key, value]) => {
        data[key] = value;
      });
    });

    // clear editor content on reset
    EventHandler.add("com.woltlab.wcf.redactor2", `reset_${this._element.id}`, () => this.hideOverlay());

    document.addEventListener("visibilitychange", () => this._onVisibilityChange());
  }

  protected _onVisibilityChange(): void {
    this._isActive = !document.hidden;
    this._isPending = document.hidden;
  }

  /**
   * Returns the initial value for the textarea, used to inject message
   * from storage into the editor before initialization.
   *
   * @return      {string}        message content
   */
  getInitialValue(): string {
    if (window.ENABLE_DEVELOPER_TOOLS && !Devtools._internal_.editorAutosave()) {
      return this._element.value;
    }

    let value = "";
    try {
      value = window.localStorage.getItem(this._key) || "";
    } catch (e) {
      const errorMessage = (e as Error).message;
      window.console.warn(`Unable to access local storage: ${errorMessage}`);
    }

    let metaData: AutosaveContent | null = null;
    try {
      metaData = JSON.parse(value);
    } catch (e) {
      // We do not care for JSON errors.
    }

    // Check if the storage is outdated.
    if (metaData !== null && typeof metaData === "object" && metaData.content) {
      const lastEditTime = ~~this._element.dataset.autosaveLastEditTime!;
      if (lastEditTime * 1_000 <= metaData.timestamp) {
        // Compare the stored version with the editor content, but only use the `innerText` property
        // in order to ignore differences in whitespace, e. g. caused by indentation of HTML tags.
        const div1 = document.createElement("div");
        div1.innerHTML = this._element.value;
        const div2 = document.createElement("div");
        div2.innerHTML = metaData.content;

        if (div1.innerText.trim() !== div2.innerText.trim()) {
          this._originalMessage = this._element.value;
          this._restored = true;

          this._metaData = metaData.meta || {};

          return metaData.content;
        }
      }
    }

    return this._element.value;
  }

  /**
   * Returns the stored meta data.
   */
  getMetaData(): AutosaveMetaData {
    return this._metaData;
  }

  /**
   * Enables periodical save of editor contents to local storage.
   */
  watch(editor: RedactorEditor): void {
    this._editor = editor;

    if (this._timer !== null) {
      throw new Error("Autosave timer is already active.");
    }

    this._timer = window.setInterval(() => this._saveToStorage(), _frequency * 1_000);

    this._saveToStorage();

    this._isPending = false;
  }

  /**
   * Disables autosave handler, for use on editor destruction.
   */
  destroy(): void {
    this.clear();

    this._editor = null;

    if (this._timer) {
      window.clearInterval(this._timer);
    }

    this._timer = null;
    this._isPending = false;
  }

  /**
   * Removed the stored message, for use after a message has been submitted.
   */
  clear(): void {
    this._metaData = {};
    this._lastMessage = "";

    try {
      window.localStorage.removeItem(this._key);
    } catch (e) {
      const errorMessage = (e as Error).message;
      window.console.warn(`Unable to remove from local storage: ${errorMessage}`);
    }
  }

  /**
   * Creates the autosave controls, used to keep or discard the restored draft.
   */
  createOverlay(): void {
    if (!this._restored) {
      return;
    }

    const editor = this._editor!;

    const container = document.createElement("div");
    container.className = "redactorAutosaveRestored active";

    const title = document.createElement("span");
    title.textContent = Language.get("wcf.editor.autosave.restored");
    container.appendChild(title);

    const buttonKeep = document.createElement("a");
    buttonKeep.className = "jsTooltip";
    buttonKeep.href = "#";
    buttonKeep.title = Language.get("wcf.editor.autosave.keep");
    buttonKeep.innerHTML = '<span class="icon icon16 fa-check green"></span>';
    buttonKeep.addEventListener("click", (event) => {
      event.preventDefault();

      this.hideOverlay();
    });
    container.appendChild(buttonKeep);

    const buttonDiscard = document.createElement("a");
    buttonDiscard.className = "jsTooltip";
    buttonDiscard.href = "#";
    buttonDiscard.title = Language.get("wcf.editor.autosave.discard");
    buttonDiscard.innerHTML = '<span class="icon icon16 fa-times red"></span>';
    buttonDiscard.addEventListener("click", (event) => {
      event.preventDefault();

      // remove from storage
      this.clear();

      // set code
      const content = UiRedactorMetacode.convertFromHtml(editor.core.element()[0].id, this._originalMessage);
      editor.code.start(content);

      // set value
      editor.core.textarea().val(editor.clean.onSync(editor.$editor.html()));

      this.hideOverlay();
    });
    container.appendChild(buttonDiscard);

    editor.core.box()[0].appendChild(container);

    editor.core.editor()[0].addEventListener("click", () => this.hideOverlay(), { once: true });

    this._container = container;
  }

  /**
   * Hides the autosave controls.
   */
  hideOverlay(): void {
    if (this._container !== null) {
      this._container.classList.remove("active");

      window.setTimeout(() => {
        if (this._container !== null) {
          this._container.remove();
        }

        this._container = null;
        this._originalMessage = "";
      }, 1_000);
    }
  }

  /**
   * Saves the current message to storage unless there was no change.
   */
  protected _saveToStorage(): void {
    if (!this._isActive) {
      if (!this._isPending) {
        return;
      }

      // save one last time before suspending
      this._isPending = false;
    }

    //noinspection JSUnresolvedVariable
    if (window.ENABLE_DEVELOPER_TOOLS && !Devtools._internal_.editorAutosave()) {
      return;
    }

    const editor = this._editor!;
    let content = editor.code.get();
    if (editor.utils.isEmpty(content)) {
      content = "";
    }

    if (this._lastMessage === content) {
      // break if content hasn't changed
      return;
    }

    if (content === "") {
      return this.clear();
    }

    try {
      EventHandler.fire("com.woltlab.wcf.redactor2", `autosaveMetaData_${this._element.id}`, this._metaData);

      window.localStorage.setItem(
        this._key,
        JSON.stringify({
          content: content,
          meta: this._metaData,
          timestamp: Date.now(),
        } as AutosaveContent),
      );

      this._lastMessage = content;
    } catch (e) {
      const errorMessage = (e as Error).message;
      window.console.warn(`Unable to write to local storage: ${errorMessage}`);
    }
  }

  /**
   * Removes stored messages older than one week.
   */
  protected _cleanup(): void {
    const oneWeekAgo = Date.now() - 7 * 24 * 3_600 * 1_000;

    Object.keys(window.localStorage)
      .filter((key) => key.startsWith(Core.getStoragePrefix()))
      .forEach((key) => {
        let value = "";
        try {
          value = window.localStorage.getItem(key) || "";
        } catch (e) {
          const errorMessage = (e as Error).message;
          window.console.warn(`Unable to access local storage: ${errorMessage}`);
        }

        let timestamp = 0;
        try {
          const content: AutosaveContent = JSON.parse(value);
          timestamp = content.timestamp;
        } catch (e) {
          // We do not care for JSON errors.
        }

        if (!value || timestamp < oneWeekAgo) {
          try {
            window.localStorage.removeItem(key);
          } catch (e) {
            const errorMessage = (e as Error).message;
            window.console.warn(`Unable to remove from local storage: ${errorMessage}`);
          }
        }
      });
  }
}

Core.enableLegacyInheritance(UiRedactorAutosave);

export = UiRedactorAutosave;
