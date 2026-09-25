/**
 * Manages the list of configured object filters of an `ObjectFilterFormField`.
 * New filters are configured through a dialog, the resulting list is written
 * into a hidden input field when the form is submitted.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "../../Dialog", "WoltLabSuite/Core/Language"], function (require, exports, PromiseMutex_1, Dialog_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    class ObjectFilterBuilder {
        #conditions = new Map();
        #container;
        #endpoint;
        constructor(container, endpoint, values) {
            this.#container = container;
            this.#endpoint = endpoint;
            const button = document.createElement("button");
            button.type = "button";
            button.classList.add("button");
            button.textContent = "TODO: add object filter";
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#addFilter()));
            this.#container.insertAdjacentElement("beforebegin", button);
            const form = this.#container.closest("form");
            let shadow = undefined;
            form?.addEventListener("submit", () => {
                if (shadow === undefined) {
                    shadow = document.createElement("input");
                    shadow.type = "hidden";
                    shadow.name = this.#container.id;
                    this.#container.insertAdjacentElement("afterend", shadow);
                }
                shadow.value = this.#serializeConditions();
            });
            this.#fromSerializedData(values);
        }
        /**
         * Restores the filters that were configured before.
         */
        #fromSerializedData(values) {
            for (const filter of values) {
                this.#createCondition(filter);
            }
        }
        /**
         * Opens the dialog to configure a new filter and adds it to the list.
         */
        async #addFilter() {
            const response = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(this.#endpoint);
            if (response.ok) {
                this.#createCondition(response.result);
            }
        }
        /**
         * Adds the given filter to the list, showing its summary and a button to remove it.
         */
        #createCondition(data) {
            const item = document.createElement("div");
            item.textContent = data.summary;
            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.classList.add("button", "small", "jsTooltip");
            deleteButton.title = (0, Language_1.getPhrase)("wcf.global.button.delete");
            deleteButton.innerHTML = '<fa-icon name="times"></fa-icon>';
            deleteButton.addEventListener("click", () => {
                this.#deleteCondition(item);
            });
            item.append(deleteButton);
            this.#container.append(item);
            this.#conditions.set(item, data);
        }
        /**
         * Removes the given filter from the list.
         */
        #deleteCondition(element) {
            element.remove();
            this.#conditions.delete(element);
        }
        /**
         * Serializes the configured filters as a JSON-encoded list of
         * `[filterIdentifier, serializedValue]` pairs.
         */
        #serializeConditions() {
            const values = [];
            this.#conditions.forEach((condition) => {
                values.push([condition.identifier, condition.value]);
            });
            return JSON.stringify(values);
        }
    }
    /**
     * Initializes the filter builder for the given container.
     *
     * @param container element that holds the list of configured filters, its id is used as the name of the submitted value
     * @param endpoint URL of the dialog to configure a new filter
     * @param values filters that were configured before
     */
    function setup(container, endpoint, values) {
        new ObjectFilterBuilder(container, endpoint, values);
    }
});
