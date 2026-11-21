/**
 * Lists the text modules in a tab below the editor. The list is loaded when
 * the tab is opened for the first time, clicking a text module inserts it
 * into the editor.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/GridViews/RenderGridView", "WoltLabSuite/Core/Api/TextModules/GetTextModule", "WoltLabSuite/Core/Component/Ckeditor", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, RenderGridView_1, GetTextModule_1, Ckeditor_1, Dialog_1, Snackbar_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    const gridViewClass = "wcf\\system\\gridView\\user\\TextModuleGridView";
    const initializedContainers = new WeakSet();
    class TextModuleList {
        #editor;
        #gridViewContainer;
        #loadPromise = undefined;
        constructor(editor, container) {
            this.#editor = editor;
            this.#gridViewContainer = container.querySelector(".textModuleList__gridView");
            container.addEventListener("messageTabMenu:activate", () => {
                void this.#load();
            });
            // The tab may have been preselected before this component was set up.
            if (container.classList.contains("active")) {
                void this.#load();
            }
            this.#gridViewContainer.addEventListener("click", (event) => {
                const target = event.target;
                const insertButton = target.closest("[data-text-module-insert]");
                if (insertButton !== null) {
                    event.preventDefault();
                    void this.#insert(parseInt(insertButton.dataset.textModuleInsert));
                    return;
                }
                if (target.closest(".textModuleList__addButton") !== null) {
                    void this.#add();
                }
            });
        }
        #load() {
            this.#loadPromise ??= (0, RenderGridView_1.renderGridView)(gridViewClass, 1, "title", "ASC").then(({ gridView }) => {
                Util_1.default.setInnerHtml(this.#gridViewContainer, gridView);
            }, (error) => {
                // Allows the next activation of the tab to retry.
                this.#loadPromise = undefined;
                throw error;
            });
            return this.#loadPromise;
        }
        async #insert(textModuleId) {
            const ckeditor = (0, Ckeditor_1.getCkeditor)(this.#editor);
            if (ckeditor === undefined) {
                return;
            }
            const { message } = await (0, GetTextModule_1.getTextModule)(textModuleId);
            ckeditor.insertHtml(message);
        }
        async #add() {
            const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(this.#gridViewContainer.dataset.addEndpoint);
            if (!ok) {
                return;
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
            this.#gridViewContainer
                .querySelector(".gridView__table")
                ?.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
        }
    }
    function setup(editorId, containerId) {
        const editor = document.getElementById(editorId);
        if (editor === null) {
            throw new Error(`The editor '${editorId}' does not exist.`);
        }
        const container = document.getElementById(containerId);
        if (container === null) {
            throw new Error(`The text module container '${containerId}' does not exist.`);
        }
        if (initializedContainers.has(container)) {
            return;
        }
        initializedContainers.add(container);
        new TextModuleList(editor, container);
    }
});
