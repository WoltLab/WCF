/**
 * Simple tab menu implementation with a straight-forward logic.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../Dom/Traverse", "../../Dom/Util", "../../Environment", "../../Event/Handler", "WoltLabSuite/Core/Ui/CloseOverlay"], function (require, exports, tslib_1, DomTraverse, Util_1, Environment, EventHandler, CloseOverlay_1) {
    "use strict";
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Util_1 = tslib_1.__importDefault(Util_1);
    Environment = tslib_1.__importStar(Environment);
    EventHandler = tslib_1.__importStar(EventHandler);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    class TabMenuSimple {
        container;
        containers = new Map();
        isLegacy = false;
        store = null;
        tabs = new Map();
        constructor(container) {
            this.container = container;
        }
        /**
         * Validates the properties and DOM structure of this container.
         *
         * Expected DOM:
         * <div class="tabMenuContainer">
         *  <nav>
         *    <ul>
         *      <li data-name="foo"><a>bar</a></li>
         *    </ul>
         *  </nav>
         *
         *  <div id="foo">baz</div>
         * </div>
         */
        validate() {
            if (!this.container.classList.contains("tabMenuContainer")) {
                return false;
            }
            const nav = DomTraverse.childByTag(this.container, "NAV");
            if (nav === null) {
                return false;
            }
            // get children
            const tabs = nav.querySelectorAll("li");
            if (tabs.length === 0) {
                return false;
            }
            DomTraverse.childrenByTag(this.container, "DIV").forEach((container) => {
                let name = container.dataset.name;
                if (!name) {
                    name = Util_1.default.identify(container);
                    container.dataset.name = name;
                }
                this.containers.set(name, container);
            });
            const containerId = this.container.id;
            tabs.forEach((tab) => {
                const name = this._getTabName(tab);
                if (!name) {
                    return;
                }
                if (this.tabs.has(name)) {
                    throw new Error("Tab names must be unique, li[data-name='" +
                        name +
                        "'] (tab menu id: '" +
                        containerId +
                        "') exists more than once.");
                }
                const container = this.containers.get(name);
                if (container === undefined) {
                    throw new Error("Expected content element for li[data-name='" + name + "'] (tab menu id: '" + containerId + "').");
                }
                else if (container.parentNode !== this.container) {
                    throw new Error("Expected content element '" + name + "' (tab menu id: '" + containerId + "') to be a direct children.");
                }
                // check if tab holds exactly one children which is an anchor element
                if (tab.childElementCount !== 1 || tab.children[0].nodeName !== "A") {
                    throw new Error("Expected exactly one <a> as children for li[data-name='" + name + "'] (tab menu id: '" + containerId + "').");
                }
                this.tabs.set(name, tab);
            });
            if (!this.tabs.size) {
                throw new Error("Expected at least one tab (tab menu id: '" + containerId + "').");
            }
            if (this.isLegacy) {
                this.container.dataset.isLegacy = "true";
                this.tabs.forEach(function (tab, name) {
                    tab.setAttribute("aria-controls", name);
                });
            }
            return true;
        }
        /**
         * Initializes this tab menu.
         */
        init() {
            // bind listeners
            this.tabs.forEach((tab) => {
                const firstChild = tab.children[0];
                firstChild.addEventListener("click", (ev) => this._onClick(ev));
                // iOS 13 changed the behavior for click events after scrolling the menu. It prevents
                // the synthetic mouse events like "click" from triggering for a short duration after
                // a scrolling has occurred. If the user scrolls to the end of the list and immediately
                // attempts to click the tab, nothing will happen. However, if the user waits for some
                // time, the tap will trigger a "click" event again.
                //
                // A "click" event is basically the result of a touch without any (significant) finger
                // movement indicated by a "touchmove" event. This changes allows the user to scroll
                // both the menu and the page normally, but still benefit from snappy reactions when
                // tapping a menu item.
                if (Environment.platform() === "ios") {
                    let isClick = false;
                    firstChild.addEventListener("touchstart", () => {
                        isClick = true;
                    });
                    firstChild.addEventListener("touchmove", () => {
                        isClick = false;
                    });
                    firstChild.addEventListener("touchend", (event) => {
                        if (isClick) {
                            isClick = false;
                            // This will block the regular click event from firing.
                            event.preventDefault();
                            // Invoke the click callback manually.
                            this._onClick(event);
                        }
                    });
                }
            });
            let returnValue = null;
            const hash = TabMenuSimple.getIdentifierFromHash();
            let selectTab = undefined;
            if (hash !== "") {
                selectTab = this.tabs.get(hash);
                // check for parent tab menu
                if (selectTab) {
                    const parent = this.container.parentElement;
                    if (parent?.classList.contains("tabMenuContainer")) {
                        returnValue = this.container;
                    }
                }
            }
            if (!selectTab) {
                let preselect = this.container.dataset.preselect || this.container.dataset.active;
                if (preselect === "true" || !preselect) {
                    preselect = true;
                }
                if (preselect === true) {
                    this.tabs.forEach(function (tab) {
                        if (!selectTab &&
                            !Util_1.default.isHidden(tab) &&
                            (!tab.previousElementSibling || Util_1.default.isHidden(tab.previousElementSibling))) {
                            selectTab = tab;
                        }
                    });
                }
                else if (typeof preselect === "string" && preselect !== "false") {
                    selectTab = this.tabs.get(preselect);
                }
            }
            if (selectTab) {
                this.containers.forEach((container) => {
                    container.classList.add("hidden");
                });
                this.select(null, selectTab, true);
            }
            const store = this.container.dataset.store;
            if (store) {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = store;
                input.value = this.getActiveTab().dataset.name || "";
                this.container.appendChild(input);
                this.store = input;
            }
            return returnValue;
        }
        /**
         * Selects a tab.
         *
         * @param  {?(string|int)}         name    tab name or sequence no
         * @param  {Element=}    tab    tab element
         * @param  {boolean=}    disableEvent  suppress event handling
         */
        select(name, tab, disableEvent) {
            name = name ? name.toString() : "";
            tab = tab || this.tabs.get(name);
            if (!tab) {
                // check if name is an integer
                if (~~name === +name) {
                    name = ~~name;
                    let i = 0;
                    this.tabs.forEach((item) => {
                        if (i === name) {
                            tab = item;
                        }
                        i++;
                    });
                }
                if (!tab) {
                    throw new Error(`Expected a valid tab name, '${name}' given (tab menu id: '${this.container.id}').`);
                }
            }
            name = (name || tab.dataset.name || "");
            // unmark active tab
            const oldTab = this.getActiveTab();
            let oldContent = null;
            if (oldTab) {
                const oldTabName = oldTab.dataset.name;
                if (oldTabName === name) {
                    // same tab
                    return;
                }
                if (!disableEvent) {
                    EventHandler.fire("com.woltlab.wcf.simpleTabMenu_" + this.container.id, "beforeSelect", {
                        tab: oldTab,
                        tabName: oldTabName,
                    });
                }
                oldTab.classList.remove("active");
                oldContent = this.containers.get(oldTab.dataset.name || "");
                oldContent.classList.remove("active");
                oldContent.classList.add("hidden");
                if (this.isLegacy) {
                    oldTab.classList.remove("ui-state-active");
                    oldContent.classList.remove("ui-state-active");
                }
            }
            tab.classList.add("active");
            const newContent = this.containers.get(name);
            newContent.classList.add("active");
            newContent.classList.remove("hidden");
            if (this.isLegacy) {
                tab.classList.add("ui-state-active");
                newContent.classList.add("ui-state-active");
            }
            if (this.store) {
                this.store.value = name;
            }
            if (!disableEvent) {
                EventHandler.fire("com.woltlab.wcf.simpleTabMenu_" + this.container.id, "select", {
                    active: tab,
                    activeName: name,
                    previous: oldTab,
                    previousName: oldTab ? oldTab.dataset.name : null,
                });
                const jQuery = this.isLegacy && typeof window.jQuery === "function" ? window.jQuery : null;
                if (jQuery) {
                    // simulate jQuery UI Tabs event
                    jQuery(this.container).trigger("wcftabsbeforeactivate", {
                        newTab: jQuery(tab),
                        oldTab: jQuery(oldTab),
                        newPanel: jQuery(newContent),
                        oldPanel: jQuery(oldContent),
                    });
                }
                let location = window.location.href.replace(/#+[^#]*$/, "");
                if (TabMenuSimple.getIdentifierFromHash() === name) {
                    location += window.location.hash;
                }
                else {
                    location += "#" + name;
                }
                // update history
                window.history.replaceState(undefined, "", location);
            }
            CloseOverlay_1.default.execute();
            void new Promise((resolve_1, reject_1) => { require(["../TabMenu"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((UiTabMenu) => {
                UiTabMenu.scrollToTab(tab);
            });
        }
        /**
         * Selects the first visible tab of the tab menu and return `true`. If there is no
         * visible tab, `false` is returned.
         *
         * The visibility of a tab is determined by calling `elIsHidden` with the tab menu
         * item as the parameter.
         */
        selectFirstVisible() {
            let selectTab = null;
            this.tabs.forEach((tab) => {
                if (!selectTab && !Util_1.default.isHidden(tab)) {
                    selectTab = tab;
                }
            });
            if (selectTab) {
                this.select(null, selectTab, false);
            }
            return selectTab !== null;
        }
        /**
         * Returns true if this tab menu has a tab with provided name.
         */
        hasTab(name) {
            return this.tabs.has(name);
        }
        /**
         * Handles clicks on a tab.
         */
        _onClick(event) {
            event.preventDefault();
            const target = event.currentTarget;
            this.select(null, target.parentNode);
        }
        /**
         * Returns the tab name.
         */
        _getTabName(tab) {
            let name = tab.dataset.name || null;
            // handle legacy tab menus
            if (!name) {
                if (tab.childElementCount === 1 && tab.children[0].nodeName === "A") {
                    const link = tab.children[0];
                    if (/#([^#]+)$/.exec(link.href)) {
                        name = RegExp.$1;
                        if (document.getElementById(name) === null) {
                            name = null;
                        }
                        else {
                            this.isLegacy = true;
                            tab.dataset.name = name;
                        }
                    }
                }
            }
            return name;
        }
        /**
         * Returns the currently active tab.
         */
        getActiveTab() {
            return document.querySelector("#" + this.container.id + " > nav > ul > li.active");
        }
        /**
         * Returns the list of registered content containers.
         */
        getContainers() {
            return this.containers;
        }
        /**
         * Returns the list of registered tabs.
         */
        getTabs() {
            return this.tabs;
        }
        static getIdentifierFromHash() {
            if (/^#+([^/]+)+(?:\/.+)?/.exec(window.location.hash)) {
                return RegExp.$1;
            }
            return "";
        }
    }
    return TabMenuSimple;
});
