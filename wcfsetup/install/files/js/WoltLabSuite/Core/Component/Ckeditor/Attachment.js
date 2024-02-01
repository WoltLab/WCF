/**
 * Forwards upload requests from the editor to the attachment system.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "./Event"], function (require, exports, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function uploadAttachment(element, file, abortController) {
        const payload = { abortController, file };
        (0, Event_1.dispatchToCkeditor)(element).uploadAttachment(payload);
        return new Promise((resolve) => {
            void payload.promise.then(({ attachmentId, url }) => {
                resolve({
                    "data-attachment-id": attachmentId.toString(),
                    urls: {
                        default: url,
                    },
                });
            });
        });
    }
    function setupInsertAttachment(ckeditor) {
        (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).insertAttachment(({ attachmentId, url }) => {
            if (url === "") {
                ckeditor.insertText(`[attach=${attachmentId}][/attach]`);
            }
            else {
                ckeditor.insertHtml(`<img src="${url}" class="woltlabAttachment" data-attachment-id="${attachmentId.toString()}">`);
            }
        });
    }
    function setupRemoveAttachment(ckeditor) {
        (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).removeAttachment(({ attachmentId }) => {
            ckeditor.removeAll("imageBlock", { attachmentId });
            ckeditor.removeAll("imageInline", { attachmentId });
        });
    }
    function getInlineImageIds(element) {
        const messageTabMenu = document.querySelector(`.messageTabMenu[data-wysiwyg-container-id="${element.id}"]`);
        if (!messageTabMenu) {
            return [];
        }
        const attachmentList = messageTabMenu.querySelector(".formAttachmentContent > .formAttachmentList");
        if (!attachmentList) {
            return [];
        }
        return Array.from(attachmentList.querySelectorAll('.formAttachmentListItem[data-is-image="1"]')).map((listItem) => parseInt(listItem.dataset.objectId));
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element).setupConfiguration(({ configuration, features }) => {
            if (!features.attachment) {
                return;
            }
            const inlineImageIds = getInlineImageIds(element);
            configuration.woltlabAttachment = {
                inlineImageIds,
            };
            configuration.woltlabUpload = {
                uploadImage: (file, abortController) => uploadAttachment(element, file, abortController),
                uploadOther: (file) => uploadAttachment(element, file),
            };
            (0, Event_1.listenToCkeditor)(element).ready(({ ckeditor }) => {
                setupInsertAttachment(ckeditor);
                setupRemoveAttachment(ckeditor);
            });
        });
    }
    exports.setup = setup;
});
