/**
 * Inserts media files into a CodeMirror editor.
 *
 * @author Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "../../../Component/Media/Manager", "../../../Component/Media/Insert", "../../../Helper/PromiseMutex"], function (require, exports, Manager_1, Insert_1, PromiseMutex_1) {
    "use strict";
    class AcpUiCodeMirrorMedia {
        element;
        constructor(elementId) {
            this.element = document.getElementById(elementId);
            const button = document.getElementById(`codemirror-${elementId}-media`);
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.insert()));
        }
        async insert() {
            const media = await (0, Manager_1.openMediaManager)({ mode: "editor" });
            if (media.length === 0) {
                return;
            }
            const size = await (0, Insert_1.chooseImageSize)(media);
            if (size === undefined) {
                return;
            }
            const content = media
                .map((item) => {
                const effectiveSize = item.isImage ? (0, Insert_1.getEffectiveSize)(item, size) : "original";
                const sizeArgument = effectiveSize !== "original" ? ` size="${effectiveSize}"` : "";
                return `{{ media="${item.mediaID}"${sizeArgument} }}`;
            })
                .join("");
            this.element.codemirror.replaceSelection(content);
        }
    }
    return AcpUiCodeMirrorMedia;
});
