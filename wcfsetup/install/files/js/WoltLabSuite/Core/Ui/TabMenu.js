/**
 * Common interface for tab menu access.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Dom/Change/Listener", "../Dom/Util", "./TabMenu/Simple", "./CloseOverlay", "./Screen", "./Scroll"], function (require, exports, tslib_1, Listener_1, Util_1, Simple_1, CloseOverlay_1, UiScreen, UiScroll) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.selectErroneousTabs = selectErroneousTabs;
    exports.setup = setup;
    exports.getTabMenu = getTabMenu;
    exports.scrollToTab = scrollToTab;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    UiScroll = tslib_1.__importStar(UiScroll);
    let _activeList = null;
    let _enableTabScroll = false;
    const _tabMenus = new Map();
    /**
     * Initializes available tab menus.
     */
    function init() {
        document.querySelectorAll(".tabMenuContainer:not(.staticTabMenuContainer)").forEach((container) => {
            const containerId = Util_1.default.identify(container);
            if (_tabMenus.has(containerId)) {
                return;
            }
            let tabMenu = new Simple_1.default(container);
            if (!tabMenu.validate()) {
                return;
            }
            const returnValue = tabMenu.init();
            _tabMenus.set(containerId, tabMenu);
            if (returnValue instanceof HTMLElement) {
                const parent = returnValue.parentNode;
                const parentTabMenu = getTabMenu(parent.id);
                if (parentTabMenu) {
                    tabMenu = parentTabMenu;
                    tabMenu.select(returnValue.id, undefined, true);
                }
            }
            const list = document.querySelector("#" + containerId + " > nav > ul");
            list.addEventListener("click", (event) => {
                event.preventDefault();
                event.stopPropagation();
                if (event.target === list) {
                    list.classList.add("active");
                    _activeList = list;
                }
                else {
                    list.classList.remove("active");
                    _activeList = null;
                }
            });
            // bind scroll listener
            container.querySelectorAll(".tabMenu, .menu").forEach((menu) => {
                function callback() {
                    timeout = null;
                    rebuildMenuOverflow(menu);
                }
                let timeout = null;
                menu.querySelector("ul").addEventListener("scroll", () => {
                    if (timeout !== null) {
                        window.clearTimeout(timeout);
                    }
                    // slight delay to avoid calling this function too often
                    timeout = window.setTimeout(callback, 10);
                }, { passive: true });
            });
            // The validation of input fields, e.g. [required], yields strange results when
            // the erroneous element is hidden inside a tab. The submit button will appear
            // to not work and a warning is displayed on the console. We can work around this
            // by manually checking if the input fields validate on submit and display the
            // parent tab ourselves.
            const form = container.closest("form");
            if (form !== null) {
                const submitButton = form.querySelector('input[type="submit"]');
                if (submitButton !== null) {
                    submitButton.addEventListener("click", (event) => {
                        if (event.defaultPrevented) {
                            return;
                        }
                        for (const element of container.querySelectorAll("input, select, textarea")) {
                            if (!element.checkValidity()) {
                                event.preventDefault();
                                // Select the tab that contains the erroneous element.
                                const tabMenu = getTabMenu(element.closest(".tabMenuContainer").id);
                                const tabMenuContent = element.closest(".tabMenuContent");
                                tabMenu.select(tabMenuContent.dataset.name || "");
                                UiScroll.element(element, () => {
                                    element.reportValidity();
                                });
                                return;
                            }
                        }
                    });
                }
            }
        });
    }
    /**
     * Selects the first tab containing an element with class `formError`.
     */
    function selectErroneousTabs() {
        _tabMenus.forEach((tabMenu) => {
            let foundError = false;
            tabMenu.getContainers().forEach((container) => {
                if (!foundError && container.querySelector(".formError") !== null) {
                    foundError = true;
                    tabMenu.select(container.id);
                }
            });
        });
    }
    function scrollEnable(isSetup) {
        _enableTabScroll = true;
        _tabMenus.forEach((tabMenu) => {
            const activeTab = tabMenu.getActiveTab();
            if (isSetup) {
                rebuildMenuOverflow(activeTab.closest(".menu, .tabMenu"));
            }
            else {
                scrollToTab(activeTab);
            }
        });
    }
    function scrollDisable() {
        _enableTabScroll = false;
    }
    function scrollMenu(list, left, scrollLeft, scrollWidth, width, paddingRight) {
        // allow some padding to indicate overflow
        if (paddingRight) {
            left -= 15;
        }
        else if (left > 0) {
            left -= 15;
        }
        if (left < 0) {
            left = 0;
        }
        else {
            // ensure that our left value is always within the boundaries
            left = Math.min(left, scrollWidth - width);
        }
        if (scrollLeft === left) {
            return;
        }
        list.classList.add("enableAnimation");
        // new value is larger, we're scrolling towards the end
        if (scrollLeft < left) {
            list.firstElementChild.style.setProperty("margin-left", `${scrollLeft - left}px`, "");
        }
        else {
            // new value is smaller, we're scrolling towards the start
            list.style.setProperty("padding-left", `${scrollLeft - left}px`, "");
        }
        setTimeout(() => {
            list.classList.remove("enableAnimation");
            list.firstElementChild.style.removeProperty("margin-left");
            list.style.removeProperty("padding-left");
            list.scrollLeft = left;
        }, 300);
    }
    function rebuildMenuOverflow(menu) {
        if (!_enableTabScroll) {
            return;
        }
        const width = menu.clientWidth;
        const list = menu.querySelector("ul");
        const scrollLeft = list.scrollLeft;
        const scrollWidth = list.scrollWidth;
        const overflowLeft = scrollLeft > 0;
        let overlayLeft = menu.querySelector(".tabMenuOverlayLeft");
        if (overflowLeft) {
            if (overlayLeft === null) {
                overlayLeft = document.createElement("button");
                overlayLeft.type = "button";
                overlayLeft.innerHTML = '<fa-icon size="24" name="angle-left"></fa-icon>';
                overlayLeft.className = "tabMenuOverlayLeft";
                overlayLeft.addEventListener("click", () => {
                    const listWidth = list.clientWidth;
                    scrollMenu(list, list.scrollLeft - ~~(listWidth / 2), list.scrollLeft, list.scrollWidth, listWidth, false);
                });
                menu.insertBefore(overlayLeft, menu.firstChild);
            }
            overlayLeft.classList.add("active");
        }
        else if (overlayLeft !== null) {
            overlayLeft.classList.remove("active");
        }
        const overflowRight = width + scrollLeft < scrollWidth;
        let overlayRight = menu.querySelector(".tabMenuOverlayRight");
        if (overflowRight) {
            if (overlayRight === null) {
                overlayRight = document.createElement("button");
                overlayRight.type = "button";
                overlayRight.innerHTML = '<fa-icon size="24" name="angle-right"></fa-icon>';
                overlayRight.className = "tabMenuOverlayRight";
                overlayRight.addEventListener("click", () => {
                    const listWidth = list.clientWidth;
                    scrollMenu(list, list.scrollLeft + ~~(listWidth / 2), list.scrollLeft, list.scrollWidth, listWidth, false);
                });
                menu.appendChild(overlayRight);
            }
            overlayRight.classList.add("active");
        }
        else if (overlayRight !== null) {
            overlayRight.classList.remove("active");
        }
    }
    /**
     * Sets up tab menus and binds listeners.
     */
    function setup() {
        init();
        selectErroneousTabs();
        Listener_1.default.add("WoltLabSuite/Core/Ui/TabMenu", init);
        CloseOverlay_1.default.add("WoltLabSuite/Core/Ui/TabMenu", () => {
            if (_activeList) {
                _activeList.classList.remove("active");
                _activeList = null;
            }
        });
        UiScreen.on("screen-sm-down", {
            match() {
                scrollEnable(false);
            },
            unmatch: scrollDisable,
            setup() {
                scrollEnable(true);
            },
        });
        window.addEventListener("hashchange", () => {
            const hash = Simple_1.default.getIdentifierFromHash();
            const element = hash ? document.getElementById(hash) : null;
            if (element !== null && element.classList.contains("tabMenuContent")) {
                _tabMenus.forEach((tabMenu) => {
                    if (tabMenu.hasTab(hash)) {
                        tabMenu.select(hash);
                    }
                });
            }
        });
        const hash = Simple_1.default.getIdentifierFromHash();
        if (hash) {
            window.setTimeout(() => {
                // check if page was initially scrolled using a tab id
                const tabMenuContent = document.getElementById(hash);
                if (tabMenuContent && tabMenuContent.classList.contains("tabMenuContent")) {
                    const scrollY = window.scrollY || window.pageYOffset;
                    if (scrollY > 0) {
                        const parent = tabMenuContent.parentNode;
                        let offsetTop = parent.offsetTop - 50;
                        if (offsetTop < 0) {
                            offsetTop = 0;
                        }
                        if (scrollY > offsetTop) {
                            let y = Util_1.default.offset(parent).top;
                            if (y <= 50) {
                                y = 0;
                            }
                            else {
                                y -= 50;
                            }
                            window.scrollTo(0, y);
                        }
                    }
                }
            }, 100);
        }
    }
    /**
     * Returns a TabMenuSimple instance for given container id.
     */
    function getTabMenu(containerId) {
        return _tabMenus.get(containerId);
    }
    function scrollToTab(tab) {
        if (!_enableTabScroll) {
            return;
        }
        const list = tab.closest("ul");
        const width = list.clientWidth;
        const scrollLeft = list.scrollLeft;
        const scrollWidth = list.scrollWidth;
        if (width === scrollWidth) {
            // no overflow, ignore
            return;
        }
        // check if tab is currently visible
        const left = tab.offsetLeft;
        let shouldScroll = false;
        if (left < scrollLeft) {
            shouldScroll = true;
        }
        let paddingRight = false;
        if (!shouldScroll) {
            const visibleWidth = width - (left - scrollLeft);
            let virtualWidth = tab.clientWidth;
            if (tab.nextElementSibling !== null) {
                paddingRight = true;
                virtualWidth += 20;
            }
            if (visibleWidth < virtualWidth) {
                shouldScroll = true;
            }
        }
        if (shouldScroll) {
            scrollMenu(list, left, scrollLeft, scrollWidth, width, paddingRight);
        }
    }
});
