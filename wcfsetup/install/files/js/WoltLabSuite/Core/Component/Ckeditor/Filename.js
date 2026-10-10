/**
 * Replaces the generic filename of images pasted from the clipboard.
 *
 * Browsers and CKEditor name pasted images `image.<ext>`, which makes them
 * indistinguishable from each other in the list of uploaded files.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.normalizePastedFilename = normalizePastedFilename;
    let lastTimestamp = "";
    let counter = 0;
    function getTimestamp() {
        const date = new Date();
        const pad = (value) => value.toString().padStart(2, "0");
        return [
            date.getFullYear(),
            pad(date.getMonth() + 1),
            pad(date.getDate()),
            pad(date.getHours()) + pad(date.getMinutes()) + pad(date.getSeconds()),
        ].join("-");
    }
    function normalizePastedFilename(file) {
        const match = /^image\.([a-z]+)$/i.exec(file.name);
        if (match === null) {
            return file;
        }
        const timestamp = getTimestamp();
        if (timestamp === lastTimestamp) {
            counter++;
        }
        else {
            lastTimestamp = timestamp;
            counter = 1;
        }
        let filename = `image-${timestamp}`;
        if (counter > 1) {
            filename += `-${counter}`;
        }
        return new File([file], `${filename}.${match[1]}`, {
            type: file.type,
            lastModified: file.lastModified,
        });
    }
});
