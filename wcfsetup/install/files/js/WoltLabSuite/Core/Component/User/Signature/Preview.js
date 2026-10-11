/**
 * Handles the preview of signatures.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Api/Users/RenderSignaturePreview", "WoltLabSuite/Core/Component/Ckeditor/Event", "WoltLabSuite/Core/Dom/Util"], function (require, exports, PromiseMutex_1, RenderSignaturePreview_1, Event_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    let previewContainer;
    async function loadPreview(ckeditor) {
        const response = await (0, RenderSignaturePreview_1.renderSignaturePreview)(ckeditor.getHtml());
        if (!response.ok) {
            const validationError = response.error.getValidationError();
            if (validationError === undefined) {
                throw new Error("Unexpected validation error", { cause: response.error });
            }
            (0, Util_1.innerError)(ckeditor.element, validationError.code);
            return;
        }
        (0, Util_1.innerError)(ckeditor.element, false);
        if (previewContainer === undefined) {
            const template = document.getElementById("previewTemplate");
            const fragment = template.content.cloneNode(true);
            document.getElementById("signatureContainer").insertAdjacentElement("beforebegin", template);
            template.replaceWith(fragment);
            previewContainer = document.getElementById("previewContainer");
        }
        previewContainer.innerHTML = response.value.message;
    }
    function setup() {
        (0, Event_1.listenToCkeditor)(document.getElementById("text")).ready(({ ckeditor }) => {
            document.getElementById("previewButton")?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => loadPreview(ckeditor)));
        });
    }
});
