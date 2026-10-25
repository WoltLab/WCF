/**
 * Shows the poll manager in a dialog to select a standalone poll, which can be
 * created or edited from within the dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Api/Polls/GetPoll", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/StringUtil", "WoltLabSuite/Core/Component/Snackbar"], function (require, exports, Dialog_1, GetPoll_1, Language_1, StringUtil_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.openPollManager = openPollManager;
    /**
     * Keeps track of the selected poll, which survives the reloading of the list.
     */
    class PollSelection {
        #dialog;
        #listViewItems;
        #selectionInfo;
        #selectedId = undefined;
        #selectedQuestion = "";
        constructor(dialog, listViewItems) {
            this.#dialog = dialog;
            this.#listViewItems = listViewItems;
            this.#selectionInfo = document.createElement("div");
            this.#selectionInfo.classList.add("pollManager__selectionInfo");
            dialog.querySelector("woltlab-core-dialog-control").append(this.#selectionInfo);
            listViewItems.setAttribute("role", "listbox");
            // The items are replaced whenever the list is reloaded.
            new MutationObserver(() => this.render()).observe(listViewItems, { childList: true });
            this.#initEventListeners();
            this.render();
        }
        getSelectedId() {
            return this.#selectedId;
        }
        select(item) {
            this.#selectedId = parseInt(item.dataset.objectId);
            this.#selectedQuestion = item.dataset.question;
            this.render();
        }
        getItem(pollId) {
            return this.#listViewItems.querySelector(`.pollManager__item[data-object-id="${pollId}"]`);
        }
        render() {
            const items = Array.from(this.#listViewItems.querySelectorAll(".pollManager__item"));
            let focusableItem = items.find((item) => parseInt(item.dataset.objectId) === this.#selectedId);
            focusableItem ??= items[0];
            for (const item of items) {
                const isSelected = parseInt(item.dataset.objectId) === this.#selectedId;
                item.classList.toggle("pollManager__item--selected", isSelected);
                item.setAttribute("aria-selected", isSelected ? "true" : "false");
                item.tabIndex = item === focusableItem ? 0 : -1;
            }
            if (this.#selectedId === undefined) {
                this.#selectionInfo.textContent = (0, Language_1.getPhrase)("wcf.poll.manager.selection.none");
            }
            else {
                this.#selectionInfo.innerHTML = `<strong>${(0, StringUtil_1.escapeHTML)(this.#selectedQuestion)}</strong>`;
            }
            this.#selectionInfo.title = this.#selectionInfo.textContent || "";
            this.#dialog.incomplete = this.#selectedId === undefined;
        }
        confirm() {
            if (this.#selectedId === undefined) {
                return;
            }
            // The `disabled` state of the primary button is updated asynchronously
            // when `incomplete` changes, `click()` would be a no-op immediately after
            // selecting the first item.
            const button = this.#dialog.querySelector(".dialog__control__button--primary");
            button.form.requestSubmit(button);
        }
        #initEventListeners() {
            this.#listViewItems.addEventListener("click", (event) => {
                const item = this.#getItemFromEvent(event);
                if (item !== null) {
                    this.select(item);
                }
            });
            // Deleted polls must not remain selected.
            this.#listViewItems.addEventListener("interaction:remove", (event) => {
                const pollId = parseInt(event.target.dataset.objectId);
                if (pollId === this.#selectedId) {
                    this.#selectedId = undefined;
                    this.render();
                }
            });
            this.#listViewItems.addEventListener("dblclick", (event) => {
                const item = this.#getItemFromEvent(event);
                if (item !== null) {
                    this.select(item);
                    this.confirm();
                }
            });
            this.#listViewItems.addEventListener("keydown", (event) => {
                const item = this.#getItemFromEvent(event);
                if (item === null || event.target !== item) {
                    return;
                }
                switch (event.key) {
                    case " ":
                        event.preventDefault();
                        this.select(item);
                        break;
                    case "Enter":
                        event.preventDefault();
                        this.select(item);
                        this.confirm();
                        break;
                    case "ArrowUp":
                    case "ArrowDown": {
                        event.preventDefault();
                        const sibling = (event.key === "ArrowUp" ? item.previousElementSibling : item.nextElementSibling);
                        if (sibling?.classList.contains("pollManager__item")) {
                            item.tabIndex = -1;
                            sibling.tabIndex = 0;
                            sibling.focus();
                        }
                        break;
                    }
                }
            });
        }
        #getItemFromEvent(event) {
            const target = event.target;
            // The context menu must not change the selection.
            if (target.closest(".pollManager__item__menu") !== null) {
                return null;
            }
            return target.closest(".pollManager__item");
        }
    }
    /**
     * Resolves with the item of the given poll once the list has been reloaded.
     */
    function waitForItem(selection, listViewItems, pollId) {
        return new Promise((resolve) => {
            const observer = new MutationObserver(() => {
                observer.disconnect();
                window.clearTimeout(timeout);
                resolve(selection.getItem(pollId));
            });
            observer.observe(listViewItems, { childList: true });
            // An empty list that remains empty does not cause any mutation.
            const timeout = window.setTimeout(() => {
                observer.disconnect();
                resolve(selection.getItem(pollId));
            }, 5_000);
        });
    }
    function setupSearch(dialog, listViewItems) {
        const input = dialog.content.querySelector(".pollManager__searchInput");
        if (input === null) {
            return;
        }
        let timeout = undefined;
        input.addEventListener("input", () => {
            window.clearTimeout(timeout);
            timeout = window.setTimeout(() => {
                listViewItems.dispatchEvent(new CustomEvent("interaction:set-parameters", {
                    detail: { searchString: input.value.trim() },
                }));
            }, 200);
        });
        // Prevent Enter from submitting the prompt form of the dialog.
        input.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                event.preventDefault();
            }
        });
    }
    function setupAddButton(dialog, listViewItems, selection) {
        const addButton = dialog.content.querySelector(".pollManager__addButton");
        const endpoint = dialog.content.querySelector(".pollManager__config")?.dataset.addEndpoint;
        if (addButton === null || endpoint === undefined) {
            return;
        }
        addButton.addEventListener("click", async () => {
            const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
            if (!ok || !listViewItems.isConnected) {
                return;
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
            const itemPromise = waitForItem(selection, listViewItems, result.pollID);
            listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
            const item = await itemPromise;
            if (item !== null) {
                selection.select(item);
                item.scrollIntoView({ block: "nearest" });
            }
        });
    }
    /**
     * Opens the poll manager and resolves with the selected poll, `undefined` is
     * returned if the dialog was closed.
     */
    async function openPollManager() {
        const dialog = await (0, Dialog_1.dialogFactory)()
            .usingListView()
            .fromPreset((0, Language_1.getPhrase)("wcf.poll.manager"), "wcf\\system\\listView\\user\\PollManagerListView", new Map(), undefined, "time", "DESC", 1, {
            primary: (0, Language_1.getPhrase)("wcf.global.button.insert"),
        });
        dialog.dialog.classList.add("dialog--pollManager");
        const listViewItems = dialog.content.querySelector(".listView__items");
        const selection = new PollSelection(dialog, listViewItems);
        setupSearch(dialog, listViewItems);
        setupAddButton(dialog, listViewItems, selection);
        return new Promise((resolve) => {
            let completed = false;
            dialog.addEventListener("primary", async () => {
                const pollId = selection.getSelectedId();
                if (completed || pollId === undefined) {
                    return;
                }
                completed = true;
                try {
                    resolve(await (0, GetPoll_1.getPoll)(pollId));
                }
                finally {
                    if (dialog.isConnected) {
                        dialog.close();
                    }
                }
            });
            dialog.addEventListener("afterClose", () => {
                if (!completed) {
                    completed = true;
                    resolve(undefined);
                }
            });
        });
    }
});
