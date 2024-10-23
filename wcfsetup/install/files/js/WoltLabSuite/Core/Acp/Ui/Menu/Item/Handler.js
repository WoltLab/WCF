/**
 * Provides the interface logic to add and edit menu items.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../Dom/Util", "../../../../Language", "../../../../Ui/Page/Search/Handler"], function (require, exports, tslib_1, Util_1, Language, UiPageSearchHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    UiPageSearchHandler = tslib_1.__importStar(UiPageSearchHandler);
    class AcpUiMenuItemHandler {
        activePageId = 0;
        cache = new Map();
        containerExternalLink;
        containerInternalLink;
        containerPageObjectId;
        handlers;
        pageId;
        pageObjectId;
        /**
         * Initializes the interface logic.
         */
        constructor(handlers) {
            this.handlers = handlers;
            this.containerInternalLink = document.getElementById("pageIDContainer");
            this.containerExternalLink = document.getElementById("externalURLContainer");
            this.containerPageObjectId = document.getElementById("pageObjectIDContainer");
            if (this.handlers.size) {
                this.pageId = document.getElementById("pageID");
                this.pageId.addEventListener("change", this.togglePageId.bind(this));
                this.pageObjectId = document.getElementById("pageObjectID");
                this.activePageId = ~~this.pageId.value;
                if (this.activePageId && this.handlers.has(this.activePageId)) {
                    this.cache.set(this.activePageId, ~~this.pageObjectId.value);
                }
                const searchButton = document.getElementById("searchPageObjectID");
                searchButton.addEventListener("click", (ev) => this.openSearch(ev));
                // toggle page object id container on init
                if (this.handlers.has(~~this.pageId.value)) {
                    Util_1.default.show(this.containerPageObjectId);
                }
            }
            document.querySelectorAll('input[name="isInternalLink"]').forEach((input) => {
                input.addEventListener("change", () => this.toggleIsInternalLink(input.value));
                if (input.checked) {
                    this.toggleIsInternalLink(input.value);
                }
            });
        }
        /**
         * Toggles between the interface for internal and external links.
         */
        toggleIsInternalLink(value) {
            if (~~value) {
                Util_1.default.show(this.containerInternalLink);
                Util_1.default.hide(this.containerExternalLink);
                if (this.handlers.size) {
                    this.togglePageId();
                }
            }
            else {
                Util_1.default.hide(this.containerInternalLink);
                Util_1.default.hide(this.containerPageObjectId);
                Util_1.default.show(this.containerExternalLink);
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
    }
    let acpUiMenuItemHandler;
    function init(handlers) {
        if (!acpUiMenuItemHandler) {
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
            acpUiMenuItemHandler = new AcpUiMenuItemHandler(map);
        }
    }
});
