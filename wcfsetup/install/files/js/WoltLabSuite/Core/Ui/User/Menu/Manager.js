/**
 * Controls the behavior of the user menus.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/Manager
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Alignment", "../../CloseOverlay", "../../../Event/Handler", "../../../Dom/Util"], function (require, exports, tslib_1, Alignment, CloseOverlay_1, EventHandler, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerProvider = exports.getContainer = exports.getUserMenuProviders = void 0;
    Alignment = tslib_1.__importStar(Alignment);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Util_1 = tslib_1.__importDefault(Util_1);
    let container = undefined;
    const providers = new Set();
    const views = new Map();
    function initProvider(provider) {
        providers.add(provider);
        const button = provider.getPanelButton();
        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (button.classList.contains("open")) {
                close(provider);
            }
            else {
                open(provider);
            }
        });
    }
    function open(provider) {
        CloseOverlay_1.default.execute();
        const view = getView(provider);
        void view.open();
        const button = provider.getPanelButton();
        button.querySelector("a").setAttribute("aria-expanded", "true");
        button.classList.add("open");
        const element = view.getElement();
        setAlignment(element, button);
    }
    function setAlignment(element, referenceElement) {
        Alignment.set(element, referenceElement, { horizontal: "right" });
        if (window.getComputedStyle(element).position === "fixed" && Util_1.default.getFixedParent(referenceElement) !== null) {
            const { top, height } = referenceElement.getBoundingClientRect();
            element.style.setProperty("top", `${top + height}px`);
        }
    }
    function close(provider) {
        if (!views.has(provider)) {
            return;
        }
        const button = provider.getPanelButton();
        if (!button.classList.contains("open")) {
            return;
        }
        const view = getView(provider);
        view.close();
        button.classList.remove("open");
        button.querySelector("a").setAttribute("aria-expanded", "false");
    }
    function closeAll() {
        providers.forEach((provider) => close(provider));
    }
    function getView(provider) {
        if (!views.has(provider)) {
            const view = provider.getView();
            const element = view.getElement();
            getContainer().append(element);
            element.addEventListener("shouldClose", () => close(provider));
            views.set(provider, view);
        }
        return views.get(provider);
    }
    function getUserMenuProviders() {
        return providers;
    }
    exports.getUserMenuProviders = getUserMenuProviders;
    function getContainer() {
        if (container === undefined) {
            container = document.createElement("div");
            container.classList.add("dropdownMenuContainer");
            document.body.append(container);
        }
        return container;
    }
    exports.getContainer = getContainer;
    function registerProvider(provider) {
        if (providers.size === 0) {
            CloseOverlay_1.default.add("WoltLabSuite/Ui/User/Menu", () => closeAll());
            EventHandler.add("com.woltlab.wcf.UserMenuMobile", "more", (data) => {
                providers.forEach((provider) => {
                    if (data.identifier === provider.getIdentifier()) {
                        open(provider);
                    }
                });
            });
        }
        initProvider(provider);
    }
    exports.registerProvider = registerProvider;
});
