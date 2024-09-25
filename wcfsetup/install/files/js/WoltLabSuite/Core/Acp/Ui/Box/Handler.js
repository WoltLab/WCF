/**
 * Provides the interface logic to add and edit boxes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../Language", "../../../Ui/Page/Search/Handler"], function (require, exports, tslib_1, Util_1, Language, UiPageSearchHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    UiPageSearchHandler = tslib_1.__importStar(UiPageSearchHandler);
    class AcpUiBoxHandler {
        activePageId = 0;
        boxController;
        boxType;
        cache = new Map();
        containerExternalLink;
        containerPageId;
        containerPageObjectId;
        handlers;
        pageId;
        pageObjectId;
        position;
        /**
         * Initializes the interface logic.
         */
        constructor(handlers, boxType) {
            this.boxType = boxType;
            this.handlers = handlers;
            this.boxController = document.getElementById("boxControllerID");
            if (boxType !== "system") {
                this.containerPageId = document.getElementById("linkPageIDContainer");
                this.containerExternalLink = document.getElementById("externalURLContainer");
                this.containerPageObjectId = document.getElementById("linkPageObjectIDContainer");
                if (this.handlers.size) {
                    this.pageId = document.getElementById("linkPageID");
                    this.pageId.addEventListener("change", () => this.togglePageId());
                    this.pageObjectId = document.getElementById("linkPageObjectID");
                    this.cache = new Map();
                    this.activePageId = ~~this.pageId.value;
                    if (this.activePageId && this.handlers.has(this.activePageId)) {
                        this.cache.set(this.activePageId, ~~this.pageObjectId.value);
                    }
                    const searchButton = document.getElementById("searchLinkPageObjectID");
                    searchButton.addEventListener("click", (ev) => this.openSearch(ev));
                    // toggle page object id container on init
                    if (this.handlers.has(~~this.pageId.value)) {
                        Util_1.default.show(this.containerPageObjectId);
                    }
                }
                document.querySelectorAll('input[name="linkType"]').forEach((input) => {
                    input.addEventListener("change", () => this.toggleLinkType(input.value));
                    if (input.checked) {
                        this.toggleLinkType(input.value);
                    }
                });
            }
            if (this.boxController) {
                this.position = document.getElementById("position");
                this.boxController.addEventListener("change", () => this.setAvailableBoxPositions());
                // update positions on init
                this.setAvailableBoxPositions();
            }
        }
        /**
         * Toggles between the interface for internal and external links.
         */
        toggleLinkType(value) {
            switch (value) {
                case "none":
                    Util_1.default.hide(this.containerPageId);
                    Util_1.default.hide(this.containerPageObjectId);
                    Util_1.default.hide(this.containerExternalLink);
                    break;
                case "internal":
                    Util_1.default.show(this.containerPageId);
                    Util_1.default.hide(this.containerExternalLink);
                    if (this.handlers.size) {
                        this.togglePageId();
                    }
                    break;
                case "external":
                    Util_1.default.hide(this.containerPageId);
                    Util_1.default.hide(this.containerPageObjectId);
                    Util_1.default.show(this.containerExternalLink);
                    break;
            }
        }
        /**
         * Handles the changed page selection.
         */
        togglePageId() {
            if (this.handlers.has(this.activePageId)) {
                this.cache.set(this.activePageId, ~~this.pageObjectId.value);
            }
            this.activePageId = ~~this.pageId.value;
            // page w/o pageObjectID support, discard value
            if (!this.handlers.has(this.activePageId)) {
                this.pageObjectId.value = "";
                Util_1.default.hide(this.containerPageObjectId);
                return;
            }
            const newValue = this.cache.get(this.activePageId);
            this.pageObjectId.value = newValue ? newValue.toString() : "";
            const selectedOption = this.pageId.options[this.pageId.selectedIndex];
            const pageIdentifier = selectedOption.dataset.identifier;
            let languageItem = `wcf.page.pageObjectID.${pageIdentifier}`;
            if (Language.get(languageItem) === languageItem) {
                languageItem = "wcf.page.pageObjectID";
            }
            this.containerPageObjectId.querySelector("label").textContent = Language.get(languageItem);
            Util_1.default.show(this.containerPageObjectId);
        }
        /**
         * Opens the handler lookup dialog.
         */
        openSearch(event) {
            event.preventDefault();
            const selectedOption = this.pageId.options[this.pageId.selectedIndex];
            const pageIdentifier = selectedOption.dataset.identifier;
            const languageItem = `wcf.page.pageObjectID.search.${pageIdentifier}`;
            let labelLanguageItem;
            if (Language.get(languageItem) !== languageItem) {
                labelLanguageItem = languageItem;
            }
            UiPageSearchHandler.open(this.activePageId, selectedOption.textContent.trim(), (objectId) => {
                this.pageObjectId.value = objectId.toString();
                this.cache.set(this.activePageId, objectId);
            }, labelLanguageItem);
        }
        /**
         * Updates the available box positions per box controller.
         */
        setAvailableBoxPositions() {
            const selectedOption = this.boxController.options[this.boxController.selectedIndex];
            const supportedPositions = JSON.parse(selectedOption.dataset.supportedPositions);
            Array.from(this.position).forEach((option) => {
                option.hidden = !supportedPositions.includes(option.value);
                // Safari does not support [hidden] on option elements.
                option.disabled = option.hidden;
            });
            // Changing the controller can cause the currently selected
            // option to become unavailable. Default to the first possible
            // option in that case.
            if (this.position.options[this.position.selectedIndex].hidden) {
                for (let i = 0; i < this.position.length; i++) {
                    if (!this.position.options[i].hidden) {
                        this.position.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    }
    let acpUiBoxHandler;
    /**
     * Initializes the interface logic.
     */
    function init(handlers, boxType) {
        if (!acpUiBoxHandler) {
            let map;
            if (!(handlers instanceof Map)) {
                map = new Map();
                handlers.forEach((value, key) => {
                    map.set(~~key, value);
                });
            }
            else {
                map = handlers;
            }
            acpUiBoxHandler = new AcpUiBoxHandler(map, boxType);
        }
    }
});
