/**
 * Periodically stores the editor contents to the local storage. Opening the
 * same view again offers to restore the stored contents.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "../../Core", "../../Language", "../../StringUtil", "./Event"], function (require, exports, Core_1, Language_1, StringUtil_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.initializeAutosave = exports.setupRestoreDraft = exports.deleteDraft = void 0;
    function getLocalStorageKey(identifier) {
        return `${(0, Core_1.getStoragePrefix)()}ckeditor5-${identifier}`;
    }
    function getRestoreDialog() {
        const dialog = document.createElement("div");
        dialog.classList.add("ckeditor5__restoreDraft__dialog");
        dialog.setAttribute("role", "alertdialog");
        dialog.tabIndex = 0;
        dialog.innerHTML = `
    <div class="ckeditor5__restoreDraft__question">
      ${(0, StringUtil_1.escapeHTML)((0, Language_1.getPhrase)("wcf.editor.restoreDraft"))}
    </div>
    <div class="ckeditor5__restoreDraft__buttons">
      <button type="button" class="button buttonPrimary small" data-type="submit">
        ${(0, StringUtil_1.escapeHTML)((0, Language_1.getPhrase)("wcf.dialog.button.primary.restore"))}
      </button>
      <button type="button" class="button small" data-type="cancel">
        ${(0, StringUtil_1.escapeHTML)((0, Language_1.getPhrase)("wcf.dialog.button.cancel"))}
      </button>
      <button type="button" class="button small" data-type="preview">
        ${(0, StringUtil_1.escapeHTML)((0, Language_1.getPhrase)("wcf.editor.restoreDraft.preview"))}
      </button>
    </div>
  `;
        return dialog;
    }
    function getReturnToRestoreDialogOverlay() {
        const overlay = document.createElement("div");
        overlay.classList.add("ck", "ck-toolbar__items");
        overlay.innerHTML = `
    <button type="button" class="ck ck-button">
      ${(0, StringUtil_1.escapeHTML)((0, Language_1.getPhrase)("wcf.editor.restoreDraft.restoreOrDiscard"))}
    </button>
  `;
        return overlay;
    }
    function deleteDraft(identifier) {
        try {
            window.localStorage.removeItem(getLocalStorageKey(identifier));
        }
        catch {
            // We cannot do anything meaningful if this fails.
        }
    }
    exports.deleteDraft = deleteDraft;
    function saveDraft(element, identifier, html) {
        if (html === "") {
            deleteDraft(identifier);
            return;
        }
        const payload = {
            html,
            timestamp: Date.now(),
        };
        try {
            window.localStorage.setItem(getLocalStorageKey(identifier), JSON.stringify(payload));
            (0, Event_1.dispatchToCkeditor)(element).autosave(payload);
        }
        catch (e) {
            console.warn("Unable to write to the local storage.", e);
        }
    }
    function setupRestoreDraft(editor, identifier) {
        let value = undefined;
        try {
            const payload = window.localStorage.getItem(getLocalStorageKey(identifier));
            if (payload !== null) {
                value = JSON.parse(payload);
            }
        }
        catch {
            deleteDraft(identifier);
            return;
        }
        if (value === undefined) {
            return;
        }
        // Check if the stored value is outdated.
        const lastEditTime = parseInt(editor.sourceElement.dataset.autosaveLastEditTime) || 0;
        if (lastEditTime && lastEditTime * 1000 >= value.timestamp) {
            return;
        }
        const originalValue = editor.data.get();
        // Check if the stored value is identical to the current value.
        if (originalValue === value.html) {
            return;
        }
        editor.data.set(value.html);
        const wrapper = document.createElement("div");
        wrapper.classList.add("ckeditor5__restoreDraft");
        const dialog = getRestoreDialog();
        const dialogWrapper = document.createElement("div");
        dialogWrapper.classList.add("ckeditor5_restoreDraft__wrapper");
        dialogWrapper.append(dialog);
        editor.ui.element.insertAdjacentElement("beforebegin", wrapper);
        wrapper.append(editor.ui.element, dialogWrapper);
        const callbackIsFocused = (_evt, _name, value) => {
            if (value) {
                dialog.focus();
            }
        };
        editor.ui.focusTracker.on("change:isFocused", callbackIsFocused);
        const revertEditor = () => {
            editor.ui.focusTracker.off("change:isFocused", callbackIsFocused);
            wrapper.insertAdjacentElement("beforebegin", editor.ui.element);
            wrapper.remove();
        };
        dialog.querySelector('button[data-type="submit"]').addEventListener("click", () => {
            revertEditor();
        });
        dialog.querySelector('button[data-type="cancel"]').addEventListener("click", () => {
            editor.data.set(originalValue);
            deleteDraft(identifier);
            if (originalValue === "") {
                (0, Event_1.dispatchToCkeditor)(editor.sourceElement).discardRecoveredData();
            }
            revertEditor();
        });
        const lockId = Symbol("autosave");
        dialog.querySelector('button[data-type="preview"]').addEventListener("click", () => {
            editor.enableReadOnlyMode(lockId);
            const overlay = getReturnToRestoreDialogOverlay();
            const toolbar = editor.ui.view.toolbar.element;
            const existingItems = Array.from(toolbar.children);
            existingItems.forEach((items) => (items.hidden = true));
            toolbar.append(overlay);
            dialogWrapper.hidden = true;
            const closeOverlayButton = overlay.querySelector("button");
            closeOverlayButton.addEventListener("click", () => {
                editor.disableReadOnlyMode(lockId);
                dialogWrapper.hidden = false;
                overlay.remove();
                existingItems.forEach((items) => (items.hidden = false));
            });
            closeOverlayButton.focus();
        });
    }
    exports.setupRestoreDraft = setupRestoreDraft;
    function removeExpiredDrafts() {
        const oneWeekAgo = Date.now() - 7 * 86400;
        Object.keys(localStorage)
            .filter((key) => key.startsWith(`ckeditor5-`))
            .forEach((key) => {
            let value;
            try {
                value = window.localStorage.getItem(key);
            }
            catch {
                // Nothing we can do, forget it.
                return;
            }
            if (value === null) {
                // The value is no longer available.
                return;
            }
            let payload = undefined;
            try {
                payload = JSON.parse(value);
            }
            catch {
                // `payload` remains set to `undefined`.
            }
            if (payload === undefined || payload.timestamp < oneWeekAgo) {
                try {
                    localStorage.removeItem(key);
                }
                catch {
                    // Nothing we can do, forget it.
                }
            }
        });
    }
    function initializeAutosave(element, configuration, identifier) {
        removeExpiredDrafts();
        configuration.autosave = {
            save(editor) {
                saveDraft(element, identifier, editor.data.get());
                return Promise.resolve();
            },
            waitingTime: 15000,
        };
        (0, Event_1.listenToCkeditor)(element).reset(() => deleteDraft(identifier));
        if (element instanceof HTMLTextAreaElement && element.form) {
            element.form.addEventListener("submit", () => deleteDraft(identifier));
        }
    }
    exports.initializeAutosave = initializeAutosave;
});
