/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/StringUtil", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language"], function (require, exports, PromiseMutex_1, Dialog_1, Util_1, StringUtil_1, Selector_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConditionFormField = void 0;
    class ConditionFormField {
        #containerId;
        #container;
        #button;
        #index;
        constructor(containerId, endpoint, index) {
            this.#containerId = containerId;
            this.#index = index;
            this.#container = document.getElementById(`${containerId}Conditions`);
            this.#button = document.getElementById(`${containerId}AddCondition`);
            this.#button?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(async () => {
                await this.#showConditionAddDialog(endpoint);
            }));
            (0, Selector_1.wheneverFirstSeen)(`#${containerId}Container .condition__container`, (container) => {
                const deleteButton = document.createElement("button");
                deleteButton.type = "button";
                deleteButton.classList.add("button", "small", "jsTooltip", "condition__remove");
                deleteButton.title = (0, Language_1.getPhrase)("wcf.global.button.delete");
                const icon = document.createElement("fa-icon");
                icon.setIcon("times");
                deleteButton.appendChild(icon);
                container.prepend(deleteButton);
                deleteButton.addEventListener("click", () => {
                    container.remove();
                });
                const index = parseInt(container.dataset.conditionIndex);
                this.#index = Math.max(this.#index, index);
                const hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = `${containerId}[${index}]`;
                hidden.value = container.dataset.conditionType;
                container.appendChild(hidden);
            });
        }
        async #showConditionAddDialog(endpoint) {
            const url = new URL((0, StringUtil_1.unescapeHTML)(endpoint));
            url.searchParams.set("containerId", this.#containerId);
            url.searchParams.set("index", this.#index.toString());
            const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url.toString());
            if (ok) {
                (0, Util_1.insertHtml)(result.field, this.#container, "append");
            }
        }
    }
    exports.ConditionFormField = ConditionFormField;
});
