/**
 * Provides the program logic for node tree views.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "../Helper/Selector", "../Ui/Dropdown/Simple", "sortablejs"], function (require, exports, tslib_1, Selector_1, Simple_1, sortablejs_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.NodeTreeView = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    class NodeTreeView {
        #id;
        constructor(id) {
            this.#id = id;
            this.#initInteractions();
            this.#initializeSorting();
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
        #initializeSorting() {
            (0, Selector_1.wheneverFirstSeen)(`#${this.#id} .nodeTreeView__list`, (list) => {
                new sortablejs_1.default(list, {
                    group: "nested",
                    animation: 150,
                    fallbackOnBody: true,
                    draggable: "li",
                    handle: ".nodeTreeView__item__handle",
                });
            });
        }
    }
    exports.NodeTreeView = NodeTreeView;
});
