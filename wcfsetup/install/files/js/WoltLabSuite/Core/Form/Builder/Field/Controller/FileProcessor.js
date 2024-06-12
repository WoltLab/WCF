/**
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.FileProcessor = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    class FileProcessor {
        #container;
        #uploadButton;
        #files = new Map();
        #fieldId;
        constructor(fieldId, values) {
            this.#fieldId = fieldId;
            this.#container = document.getElementById(fieldId + "Container");
            if (this.#container === null) {
                throw new Error("Unknown field with id '" + fieldId + "'");
            }
            values.forEach((html) => {
                const element = document.createElement("template");
                Util_1.default.setInnerHtml(element, html);
                void this.#registerFile(element.content.querySelector("woltlab-core-file"));
            });
            this.#uploadButton = this.#container.querySelector("woltlab-core-file-upload");
            this.#uploadButton.addEventListener("uploadStart", (event) => {
                void this.#registerFile(event.detail);
            });
        }
        async #registerFile(element) {
            this.#uploadButton.insertAdjacentElement("beforebegin", element);
            await element.ready;
            this.#files.set(element.fileId, element);
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
