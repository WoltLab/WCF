/**
 * Uploads media files.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/List/Upload
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Upload", "../../Core"], function (require, exports, tslib_1, Upload_1, Core) {
    "use strict";
    Upload_1 = tslib_1.__importDefault(Upload_1);
    Core = tslib_1.__importStar(Core);
    class MediaListUpload extends Upload_1.default {
        _createButton() {
            super._createButton();
            const span = this._button.querySelector("span");
            const space = document.createTextNode(" ");
            span.insertBefore(space, span.childNodes[0]);
            const icon = document.createElement("span");
            icon.className = "icon icon16 fa-upload";
            span.insertBefore(icon, span.childNodes[0]);
        }
        _getParameters() {
            if (this._options.categoryId) {
                return Core.extend(super._getParameters(), {
                    categoryID: this._options.categoryId,
                });
            }
            return super._getParameters();
        }
    }
    Core.enableLegacyInheritance(MediaListUpload);
    return MediaListUpload;
});
