/**
 * Handles the trophy image upload.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Trophy/Upload
 */
define(['Core', 'Dom/Traverse', 'Language', 'Upload', 'Ui/Notification'], function (Core, DomTraverse, Language, Upload, UINotification) {
    "use strict";
    /**
     * @constructor
     */
    function TrophyUpload(trophyID, tmpHash, options) {
        options = options || {};
        this._trophyID = ~~trophyID;
        this._tmpHash = tmpHash;
        if (options.input === undefined) {
            throw new TypeError("invalid input given");
        }
        Upload.call(this, 'uploadIconFileButton', 'uploadIconFileContent', Core.extend({
            className: 'wcf\\data\\trophy\\TrophyAction'
        }, options));
    }
    Core.inherit(TrophyUpload, Upload, {
        /**
         * @see	WoltLabSuite/Core/Upload#_getParameters
         */
        _getParameters: function () {
            return {
                trophyID: this._trophyID,
                tmpHash: this._tmpHash
            };
        },
        /**
         * @see	WoltLabSuite/Core/Upload#_success
         */
        _success: function (uploadId, data) {
            elInnerError(this._button, false);
            this._target.innerHTML = "<img src=\"" + data.returnValues.url + "?timestamp=" + Date.now() + "\" />";
            UINotification.show();
        },
        /**
         * @see	WoltLabSuite/Core/Upload#_failure
         */
        _failure: function (uploadId, data, responseText, xhr, requestOptions) {
            elInnerError(this._button, Language.get('wcf.acp.trophy.imageUpload.error.' + data.returnValues.errorType));
            // remove previous images 
            this._target.innerHTML = "";
            return false;
        }
    });
    return TrophyUpload;
});
