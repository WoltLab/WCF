/**
 * Handles main menu overflow and a11y.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Environment", "../../../Language", "../../Screen"], function (require, exports, tslib_1, Environment, Language, UiScreen) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Environment = tslib_1.__importStar(Environment);
    Language = tslib_1.__importStar(Language);
    UiScreen = tslib_1.__importStar(UiScreen);
    let _enabled = false;
    let _buttonShowNext;
    let _buttonShowPrevious;
    let _firstElement;
    let _menu;
    let _marginLeft = 0;
    let _invisibleLeft = [];
    let _invisibleRight = [];
    /**
     * Enables the overflow handler.
     */
    function enable() {
        _enabled = true;
        // Safari waits three seconds for a font to be loaded which causes the header menu items
        // to be extremely wide while waiting for the font to be loaded. The extremely wide menu
        // items in turn can cause the overflow controls to be shown even if the width of the header
        // menu, after the font has been loaded successfully, does not require them. This width
        // issue results in the next button being shown for a short time. To circumvent this issue,
        // we wait a second before showing the obverflow controls in Safari.
        // see https://webkit.org/blog/6643/improved-font-loading/
        if (Environment.browser() === "safari") {
            window.setTimeout(rebuildVisibility, 1000);
        }
        else {
            window.requestAnimationFrame(() => rebuildVisibility());
        }
    }
    /**
     * Disables the overflow handler.
     */
    function disable() {
        _enabled = false;
    }
    /**
     * Displays the next three menu items.
     */
    function showNext() {
        if (_invisibleRight.length) {
            const showItem = _invisibleRight.slice(0, 3).pop();
            setMarginLeft(_menu.clientWidth - (showItem.offsetLeft + showItem.clientWidth));
            if (_menu.lastElementChild === showItem) {
                _buttonShowNext.classList.remove("active");
            }
            _buttonShowPrevious.classList.add("active");
        }
    }
    /**
     * Displays the previous three menu items.
     */
    function showPrevious() {
        if (_invisibleLeft.length) {
            const showItem = _invisibleLeft.slice(-3)[0];
            setMarginLeft(showItem.offsetLeft * -1);
            if (_menu.firstElementChild === showItem) {
                _buttonShowPrevious.classList.remove("active");
            }
            _buttonShowNext.classList.add("active");
        }
    }
    /**
     * Sets the first item's margin-left value that is
     * used to move the menu contents around.
     */
    function setMarginLeft(offset) {
        _marginLeft = Math.min(_marginLeft + offset, 0);
        _firstElement.style.setProperty("margin-left", `${_marginLeft}px`, "");
    }
    /**
     * Toggles button overlays and rebuilds the list
     * of invisible items from left to right.
     */
    function rebuildVisibility() {
        if (!_enabled)
            return;
        _invisibleLeft = [];
        _invisibleRight = [];
        const menuWidth = _menu.clientWidth;
        const scrollWidth = _menu.scrollWidth;
        if (!_buttonShowPrevious && scrollWidth > menuWidth) {
            initOverflowNavigation();
        }
        if (scrollWidth > menuWidth || _marginLeft < 0) {
            Array.from(_menu.children).forEach((child) => {
                const offsetLeft = child.offsetLeft;
                if (offsetLeft < 0) {
                    _invisibleLeft.push(child);
                }
                else if (offsetLeft + child.clientWidth > menuWidth) {
                    _invisibleRight.push(child);
                }
            });
        }
        _buttonShowPrevious?.classList[_invisibleLeft.length ? "add" : "remove"]("active");
        _buttonShowNext?.classList[_invisibleRight.length ? "add" : "remove"]("active");
    }
    /**
     * Builds the UI and binds the event listeners.
     */
    function setup() {
        setupOverflow();
        setupA11y();
    }
    /**
     * Setups overflow handling.
     */
    function setupOverflow() {
        _firstElement.addEventListener("transitionend", rebuildVisibility);
        const observer = new ResizeObserver(() => {
            _firstElement.style.setProperty("margin-left", "0px", "");
            _marginLeft = 0;
            rebuildVisibility();
        });
        observer.observe(_menu);
        enable();
    }
    function initOverflowNavigation() {
        _buttonShowNext = document.createElement("a");
        _buttonShowNext.className = "mainMenuShowNext";
        _buttonShowNext.href = "#";
        _buttonShowNext.innerHTML = '<fa-icon size="32" name="angle-right" solid></fa-icon>';
        _buttonShowNext.setAttribute("aria-hidden", "true");
        _buttonShowNext.addEventListener("click", (event) => {
            event.preventDefault();
            showNext();
        });
        _menu.insertAdjacentElement("beforebegin", _buttonShowNext);
        _buttonShowPrevious = document.createElement("a");
        _buttonShowPrevious.className = "mainMenuShowPrevious";
        _buttonShowPrevious.href = "#";
        _buttonShowPrevious.innerHTML = '<fa-icon size="32" name="angle-left" solid></fa-icon>';
        _buttonShowPrevious.setAttribute("aria-hidden", "true");
        _buttonShowPrevious.addEventListener("click", (event) => {
            event.preventDefault();
            showPrevious();
        });
        _menu.insertAdjacentElement("afterend", _buttonShowPrevious);
    }
    /**
     * Setups a11y improvements.
     */
    function setupA11y() {
        _menu.querySelectorAll(".boxMenuHasChildren").forEach((element) => {
            const link = element.querySelector(".boxMenuLink");
            link.setAttribute("aria-haspopup", "true");
            link.setAttribute("aria-expanded", "false");
            const showMenuButton = document.createElement("button");
            showMenuButton.type = "button";
            showMenuButton.className = "visuallyHidden";
            showMenuButton.setAttribute("aria-label", Language.get("wcf.global.button.showMenu"));
            element.insertBefore(showMenuButton, link.nextSibling);
            let showMenu = false;
            showMenuButton.addEventListener("click", () => {
                showMenu = !showMenu;
                link.setAttribute("aria-expanded", showMenu ? "true" : "false");
                showMenuButton.setAttribute("aria-label", Language.get(showMenu ? "wcf.global.button.hideMenu" : "wcf.global.button.showMenu"));
            });
        });
    }
    /**
     * Initializes the main menu overflow handling.
     */
    function init() {
        const menu = document.querySelector(".mainMenu .boxMenu");
        const firstElement = menu && menu.childElementCount ? menu.children[0] : null;
        if (firstElement === null) {
            throw new Error("Unable to find the main menu.");
        }
        _menu = menu;
        _firstElement = firstElement;
        UiScreen.on("screen-lg", {
            match: enable,
            unmatch: disable,
            setup: setup,
        });
    }
});
