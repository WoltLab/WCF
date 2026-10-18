/**
 * Reloads the grid view of media files after new files have been uploaded.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Media/Upload"], function (require, exports, Upload_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup(uploadButton, container) {
        (0, Upload_1.setupMediaUpload)(uploadButton, {
            // Reload the grid view only once after a batch of files has been uploaded.
            allUploadsCompleted: () => {
                container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
            },
        });
    }
});
