/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/StringUtil", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, PromiseMutex_1, Dialog_1, Util_1, StringUtil_1, Selector_1) {
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
            (0, Selector_1.wheneverFirstSeen)(`#${containerId}Container .condition-remove`, (element) => {
                element.addEventListener("click", () => {
                    element.parentElement?.remove();
                });
            });
        }
        async #showConditionAddDialog(endpoint) {
            const url = new URL((0, StringUtil_1.unescapeHTML)(endpoint));
            url.searchParams.set("containerId", this.#containerId);
            url.searchParams.set("index", this.#index.toString());
            const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url.toString());
            if (ok) {
                this.#index++;
                (0, Util_1.insertHtml)(result.field, this.#container, "append");
            }
        }
    }
    exports.ConditionFormField = ConditionFormField;
});
