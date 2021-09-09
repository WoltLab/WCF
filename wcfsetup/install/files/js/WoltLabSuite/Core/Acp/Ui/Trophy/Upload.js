/**
 * Handles the trophy image upload.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Trophy/Upload
 */
define(["require", "exports", "tslib", "../../../Core", "../../../Dom/Util", "../../../Language", "../../../Ui/Notification", "../../../Upload"], function (require, exports, tslib_1, Core, Util_1, Language, UiNotification, Upload_1) {
    "use strict";
    Core = (0, tslib_1.__importStar)(Core);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    Language = (0, tslib_1.__importStar)(Language);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    Upload_1 = (0, tslib_1.__importDefault)(Upload_1);
    class TrophyUpload extends Upload_1.default {
        constructor(trophyId, tmpHash, options) {
            super("uploadIconFileButton", "uploadIconFileContent", Core.extend({
                className: "wcf\\data\\trophy\\TrophyAction",
            }, options));
            this.trophyId = ~~trophyId;
            this.tmpHash = tmpHash;
        }
        _getParameters() {
            return {
                trophyID: this.trophyId,
                tmpHash: this.tmpHash,
            };
        }
        _success(uploadId, data) {
            Util_1.default.innerError(this._button, false);
            this._target.innerHTML = `<img src="${data.returnValues.url}?timestamp=${Date.now()}" alt="">`;
            UiNotification.show();
        }
        _failure(uploadId, data) {
            Util_1.default.innerError(this._button, Language.get(`wcf.acp.trophy.imageUpload.error.${data.returnValues.errorType}`));
            // remove previous images
            this._target.innerHTML = "";
            return false;
        }
    }
    Core.enableLegacyInheritance(TrophyUpload);
    return TrophyUpload;
});
