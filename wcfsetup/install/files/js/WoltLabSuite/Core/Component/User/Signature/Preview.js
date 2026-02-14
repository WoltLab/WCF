/**
 * Handles the preview of signatures.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Component/Ckeditor/Event"], function (require, exports, PromiseMutex_1, Ajax_1, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    let previewContainer;
    async function loadPreview(message) {
        const response = (await (0, Ajax_1.dboAction)("getMessagePreview", "wcf\\data\\user\\UserProfileAction")
            .payload({
            data: {
                message,
            },
        })
            .dispatch());
        if (previewContainer === undefined) {
            const template = document.getElementById("previewTemplate");
            const fragment = template.content.cloneNode(true);
            document.getElementById("signatureContainer").insertAdjacentElement("beforebegin", template);
            template.replaceWith(fragment);
            previewContainer = document.getElementById("previewContainer");
        }
        previewContainer.innerHTML = response.message;
    }
    function setup() {
        (0, Event_1.listenToCkeditor)(document.getElementById("text")).ready(({ ckeditor }) => {
            document.getElementById("previewButton")?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => loadPreview(ckeditor.getHtml())));
        });
    }
});
