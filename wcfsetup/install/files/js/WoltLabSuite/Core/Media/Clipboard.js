/**
 * Initializes modules required for media clipboard.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Controller/Clipboard", "../Ui/Notification", "../Ui/Dialog", "../Event/Handler", "../Language", "../Ajax"], function (require, exports, tslib_1, Clipboard, UiNotification, UiDialog, EventHandler, Language, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    exports.setMediaManager = setMediaManager;
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    UiDialog = tslib_1.__importStar(UiDialog);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    Ajax = tslib_1.__importStar(Ajax);
    let _mediaManager;
    let _didInit = false;
    class MediaClipboard {
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
                    UiDialog.open(this, data.returnValues.template);
                    break;
                case "setCategory":
                    UiDialog.close(this);
                    UiNotification.show();
                    Clipboard.reload();
                    break;
            }
        }
        _dialogSetup() {
            return {
                id: "mediaSetCategoryDialog",
                options: {
                    onSetup: (content) => {
                        content.querySelector("button").addEventListener("click", (event) => {
                            event.preventDefault();
                            const category = content.querySelector('select[name="categoryID"]');
                            setCategory(~~category.value);
                            const target = event.currentTarget;
                            target.disabled = true;
                        });
                    },
                    title: Language.get("wcf.media.setCategory"),
                },
                source: null,
            };
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
