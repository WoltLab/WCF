define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "../../Dialog", "WoltLabSuite/Core/Language"], function (require, exports, PromiseMutex_1, Dialog_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    class ObjectFilterBuilder {
        #conditions = new Map();
        #container;
        #endpoint;
        constructor(container, button) {
            this.#container = container;
            this.#endpoint = button.dataset.endpoint;
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#addFilter()));
        }
        async #addFilter() {
            const response = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(this.#endpoint);
            if (response.ok) {
                this.#createCondition(response.result);
            }
        }
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
        #deleteCondition(element) {
            element.remove();
            this.#conditions.delete(element);
        }
    }
    function setup(container, button) {
        new ObjectFilterBuilder(container, button);
    }
});
