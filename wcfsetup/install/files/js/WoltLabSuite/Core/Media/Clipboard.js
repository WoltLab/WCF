/**
 * Initializes modules required for media clipboard.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Controller/Clipboard", "../Ui/Notification", "../Event/Handler", "../Language", "../Ajax", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, tslib_1, Clipboard, UiNotification, EventHandler, Language_1, Ajax, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    exports.setMediaManager = setMediaManager;
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    EventHandler = tslib_1.__importStar(EventHandler);
    Ajax = tslib_1.__importStar(Ajax);
    let _mediaManager;
    let _didInit = false;
    class MediaClipboard {
        #dialog;
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\media\\MediaAction",
                },
            };
        }
        _ajaxSuccess(data) {
            switch (data.actionName) {
                case "getSetCategoryDialog":
                    this.#dialog = (0, Dialog_1.dialogFactory)().fromHtml(data.returnValues.template).asConfirmation();
                    this.#dialog.show((0, Language_1.getPhrase)("wcf.media.setCategory"));
                    this.#dialog.addEventListener("primary", () => {
                        const category = this.#dialog.content.querySelector('select[name="categoryID"]');
                        setCategory(~~category.value);
                    });
                    break;
                case "setCategory":
                    this.#dialog?.close();
                    UiNotification.show();
                    Clipboard.reload();
                    break;
            }
        }
    }
    const ajax = new MediaClipboard();
    let clipboardObjectIds = [];
    /**
     * Handles successful clipboard actions.
     */
    function clipboardAction(actionData) {
        const mediaIds = actionData.data.parameters.objectIDs;
        switch (actionData.data.actionName) {
            case "com.woltlab.wcf.media.delete":
                // only consider events if the action has been executed
                if (actionData.responseData !== null) {
                    _mediaManager.clipboardDeleteMedia(mediaIds);
                }
                break;
            case "com.woltlab.wcf.media.insert": {
                const mediaManagerEditor = _mediaManager;
                mediaManagerEditor.clipboardInsertMedia(mediaIds);
                break;
            }
            case "com.woltlab.wcf.media.setCategory":
                clipboardObjectIds = mediaIds;
                Ajax.api(ajax, {
                    actionName: "getSetCategoryDialog",
                });
                break;
        }
    }
    /**
     * Sets the category of the marked media files.
     */
    function setCategory(categoryID) {
        Ajax.api(ajax, {
            actionName: "setCategory",
            objectIDs: clipboardObjectIds,
            parameters: {
                categoryID: categoryID,
            },
        });
    }
    function init(pageClassName, hasMarkedItems, mediaManager) {
        if (!_didInit) {
            Clipboard.setup({
                hasMarkedItems: hasMarkedItems,
                pageClassName: pageClassName,
            });
            EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.media", (data) => clipboardAction(data));
            _didInit = true;
        }
        _mediaManager = mediaManager;
    }
    function setMediaManager(mediaManager) {
        _mediaManager = mediaManager;
    }
});
