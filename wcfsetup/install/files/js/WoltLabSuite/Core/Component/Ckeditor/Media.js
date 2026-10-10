/**
 * Forwards upload requests from the editor to the media system.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || (function () {
    var ownKeys = function(o) {
        ownKeys = Object.getOwnPropertyNames || function (o) {
            var ar = [];
            for (var k in o) if (Object.prototype.hasOwnProperty.call(o, k)) ar[ar.length] = k;
            return ar;
        };
        return ownKeys(o);
    };
    return function (mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k = ownKeys(mod), i = 0; i < k.length; i++) if (k[i] !== "default") __createBinding(result, mod, k[i]);
        __setModuleDefault(result, mod);
        return result;
    };
})();
define(["require", "exports", "./Event", "./Filename"], function (require, exports, Event_1, Filename_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function uploadMedia(element, file, abortController) {
        const payload = { abortController, file: (0, Filename_1.normalizePastedFilename)(file) };
        (0, Event_1.dispatchToCkeditor)(element).uploadMedia(payload);
        // The media system works differently compared to the
        // attachments, because uploading a file will offer
        // the user to insert the content in different formats.
        //
        // Rejecting the upload promise will cause CKEditor to
        // stop caring about the file so that we regain control.
        return Promise.reject();
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element)
            .setupConfiguration(({ configuration, features }) => {
            if (features.attachment || !features.media) {
                return;
            }
            // TODO: The typings do not include our custom plugins yet.
            configuration.woltlabUpload = {
                uploadImage: (file, abortController) => uploadMedia(element, file, abortController),
                uploadOther: (file) => uploadMedia(element, file),
            };
        })
            .ready(({ ckeditor }) => {
            if (!ckeditor.features.media) {
                return;
            }
            void new Promise((resolve_1, reject_1) => { require(["../../Media/Manager/Editor"], resolve_1, reject_1); }).then(__importStar).then(({ MediaManagerEditor }) => {
                new MediaManagerEditor({
                    ckeditor,
                });
            });
        });
    }
});
