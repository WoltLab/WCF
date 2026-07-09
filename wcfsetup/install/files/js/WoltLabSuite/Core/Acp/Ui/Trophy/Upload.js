/**
 * Handles the trophy image upload.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Component/Snackbar", "../../../Core", "../../../Dom/Util", "WoltLabSuite/Core/Language", "../../../Upload"], function (require, exports, tslib_1, Snackbar_1, Core, Util_1, Language_1, Upload_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    Upload_1 = tslib_1.__importDefault(Upload_1);
    class TrophyUpload extends Upload_1.default {
        trophyId;
        tmpHash;
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
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
        }
        _failure(uploadId, data) {
            Util_1.default.innerError(this._button, (0, Language_1.getPhrase)(`wcf.acp.trophy.imageUpload.error.${data.returnValues.errorType}`));
            // remove previous images
            this._target.innerHTML = "";
            return false;
        }
    }
    return TrophyUpload;
});
