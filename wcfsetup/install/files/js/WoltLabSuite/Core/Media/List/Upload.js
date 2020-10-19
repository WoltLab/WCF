/**
 * Uploads media files.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/List/Upload
 */
define([
    'Core', 'Dom/Util', '../Upload'
], function (Core, DomUtil, MediaUpload) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            _createButton: function () { },
            _success: function () { },
            _upload: function () { },
            _createFileElement: function () { },
            _getParameters: function () { },
            _uploadFiles: function () { },
            _createFileElements: function () { },
            _failure: function () { },
            _insertButton: function () { },
            _progress: function () { },
            _removeButton: function () { }
        };
        return Fake;
    }
    /**
     * @constructor
     */
    function MediaListUpload(buttonContainerId, targetId, options) {
        MediaUpload.call(this, buttonContainerId, targetId, options);
    }
    Core.inherit(MediaListUpload, MediaUpload, {
        /**
         * Creates the upload button.
         */
        _createButton: function () {
            MediaListUpload._super.prototype._createButton.call(this);
            var span = elBySel('span', this._button);
            var space = document.createTextNode(' ');
            DomUtil.prepend(space, span);
            var icon = elCreate('span');
            icon.className = 'icon icon16 fa-upload';
            DomUtil.prepend(icon, span);
        },
        /**
         * @see	WoltLabSuite/Core/Upload#_getParameters
         */
        _getParameters: function () {
            if (this._options.categoryId) {
                return Core.extend(MediaListUpload._super.prototype._getParameters.call(this), {
                    categoryID: this._options.categoryId
                });
            }
            return MediaListUpload._super.prototype._getParameters.call(this);
        }
    });
    return MediaListUpload;
});
