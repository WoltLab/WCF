/**
 * Provides the program logic for node tree views.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "../Api/PostObject", "../Api/NodeTreeViews/GetNode", "../Api/NodeTreeViews/GetNodes", "../Helper/PromiseMutex", "../Helper/Selector", "../Dom/Util", "../Ui/Dropdown/Simple", "sortablejs"], function (require, exports, tslib_1, PostObject_1, GetNode_1, GetNodes_1, PromiseMutex_1, Selector_1, Util_1, Simple_1, sortablejs_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.NodeTreeView = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    class NodeTreeView {
        #id;
        #viewClassName;
        #viewParameters;
        #setPositionsEndpoint;
        #sortables = new Map();
        constructor(id, viewClassName, viewParameters, setPositionsEndpoint = "") {
            this.#id = id;
            this.#viewClassName = viewClassName;
            this.#viewParameters = viewParameters;
            this.#setPositionsEndpoint = setPositionsEndpoint;
            this.#initInteractions();
            this.#initEventListeners();
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
        async #reloadTree() {
            const { template } = await (0, GetNodes_1.getNodes)(this.#viewClassName, this.#viewParameters);
            const rootList = document.querySelector(`#${this.#id} > .nodeTreeView__list`);
            for (const [parentObjectId, sortable] of this.#sortables) {
                if (parentObjectId === 0) {
                    continue;
                }
                sortable.destroy();
                this.#sortables.delete(parentObjectId);
            }
            (0, Util_1.setInnerHtml)(rootList, template);
        }
        async #reloadNode(item) {
            const objectId = parseInt(item.dataset.objectId);
            const { template } = await (0, GetNode_1.getNode)(this.#viewClassName, objectId, this.#viewParameters);
            for (const list of item.querySelectorAll(".nodeTreeView__list")) {
                const parentObjectId = parseInt(list.dataset.parentObjectId);
                this.#sortables.get(parentObjectId)?.destroy();
                this.#sortables.delete(parentObjectId);
            }
            item.replaceWith((0, Util_1.createFragmentFromHtml)(template));
        }
        #initEventListeners() {
            const nodeTreeView = document.getElementById(this.#id);
            nodeTreeView.addEventListener("interaction:invalidate-all", () => {
                void this.#reloadTree();
            });
            nodeTreeView.addEventListener("interaction:invalidate", (event) => {
                void this.#reloadNode(event.target);
            });
            nodeTreeView.addEventListener("interaction:remove", (event) => {
                const item = event.target;
                const childList = item.querySelector(":scope > .nodeTreeView__list");
                if (childList) {
                    const objectId = parseInt(item.dataset.objectId);
                    this.#sortables.get(objectId)?.destroy();
                    this.#sortables.delete(objectId);
                    item.before(...childList.children);
                }
                item.remove();
            });
        }
    }
    exports.NodeTreeView = NodeTreeView;
});
