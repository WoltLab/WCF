/**
 * Turns an element into a drop zone for a `<woltlab-core-file-upload>` element.
 * Files dropped anywhere on the `area` are passed to the upload element and
 * clicking the zone opens the file picker.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupDropZone = setupDropZone;
    function hasFiles(event) {
        return event.dataTransfer?.types.includes("Files") === true;
    }
    function setupDropZone(zone, area, uploadButton) {
        zone.addEventListener("click", () => {
            uploadButton.click();
        });
        let dragCounter = 0;
        const reset = () => {
            dragCounter = 0;
            area.classList.remove("fileDropZone--dragOver");
        };
        area.addEventListener("dragenter", (event) => {
            if (!hasFiles(event)) {
                return;
            }
            event.preventDefault();
            dragCounter++;
            area.classList.add("fileDropZone--dragOver");
        });
        area.addEventListener("dragover", (event) => {
            if (hasFiles(event)) {
                event.preventDefault();
            }
        });
        area.addEventListener("dragleave", (event) => {
            if (!hasFiles(event)) {
                return;
            }
            dragCounter = Math.max(0, dragCounter - 1);
            if (dragCounter === 0) {
                reset();
            }
        });
        area.addEventListener("drop", (event) => {
            if (!hasFiles(event)) {
                return;
            }
            event.preventDefault();
            reset();
            const files = Array.from(event.dataTransfer.files);
            if (files.length > 0) {
                uploadButton.uploadFiles(files);
            }
        });
    }
});
