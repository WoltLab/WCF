/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.FileProcessor = void 0;
    class FileProcessor {
        #container;
        #uploadButton;
        #fieldId;
        constructor(fieldId) {
            this.#fieldId = fieldId;
            this.#container = document.getElementById(fieldId + "Container");
            if (this.#container === null) {
                throw new Error("Unknown field with id '" + fieldId + "'");
            }
            this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload");
            this.#uploadButton.addEventListener("uploadStart", (event) => {
                void this.#registerFile(event.detail);
            });
        }
        async #registerFile(element) {
            this.#uploadButton.insertAdjacentElement("beforebegin", element);
            await element.ready;
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = this.#fieldId + (this.#uploadButton.maximumCount === 1 ? "" : "[]");
            input.value = element.fileId.toString();
            element.insertAdjacentElement("afterend", input);
            if (this.#uploadButton.maximumCount === 1) {
                // single file upload
                element.dataset.previewUrl = element.link;
                element.unbounded = true;
            }
        }
    }
    exports.FileProcessor = FileProcessor;
});
