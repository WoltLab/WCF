/**
 * Provides the program logic for node tree views.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "../Api/PostObject", "../Helper/PromiseMutex", "../Helper/Selector", "../Ui/Dropdown/Simple", "sortablejs"], function (require, exports, tslib_1, PostObject_1, PromiseMutex_1, Selector_1, Simple_1, sortablejs_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.NodeTreeView = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    class NodeTreeView {
        #id;
        #setPositionsEndpoint;
        #sortables = new Map();
        constructor(id, setPositionsEndpoint = "") {
            this.#id = id;
            this.#setPositionsEndpoint = setPositionsEndpoint;
            this.#initInteractions();
            if (this.#setPositionsEndpoint) {
                this.#initializeSorting();
            }
        }
        #initInteractions() {
            (0, Selector_1.wheneverFirstSeen)(`#${this.#id} .nodeTreeView__item`, (node) => {
                const content = node.querySelector(":scope > .nodeTreeView__item__content");
                const containers = [content];
                content.querySelectorAll(".dropdownToggle").forEach((element) => {
                    const dropdown = Simple_1.default.getDropdownMenu(element.dataset.target);
                    if (dropdown) {
                        containers.push(dropdown);
                    }
                });
                for (const container of containers) {
                    container.querySelectorAll("[data-interaction]").forEach((element) => {
                        element.addEventListener("click", () => {
                            node.dispatchEvent(new CustomEvent("interaction:execute", {
                                detail: element.dataset,
                                bubbles: true,
                            }));
                        });
                    });
                }
            });
        }
        #showFooter() {
            document.getElementById(`${this.#id}_footer`).hidden = false;
        }
        #hideFooter() {
            document.getElementById(`${this.#id}_footer`).hidden = true;
        }
        async #setPositions() {
            const positions = {};
            for (const [objectId, sortables] of this.#sortables) {
                const objectIds = sortables.toArray();
                if (objectIds.length === 0) {
                    continue;
                }
                positions[objectId] = objectIds.map((objectId) => parseInt(objectId));
            }
            await (0, PostObject_1.postObject)(`${window.WSC_RPC_API_URL}${this.#setPositionsEndpoint}`, { positions });
            this.#hideFooter();
        }
        #initializeSorting() {
            const button = document.getElementById(`${this.#id}_submitButton`);
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#setPositions()));
            (0, Selector_1.wheneverFirstSeen)(`#${this.#id} .nodeTreeView__list`, (list) => {
                this.#sortables.set(parseInt(list.dataset.parentObjectId), new sortablejs_1.default(list, {
                    group: "nested",
                    animation: 150,
                    fallbackOnBody: true,
                    draggable: "li",
                    handle: ".nodeTreeView__item__handle",
                    dataIdAttr: "data-object-id",
                    onChange: () => {
                        this.#showFooter();
                    },
                }));
            });
        }
    }
    exports.NodeTreeView = NodeTreeView;
});
