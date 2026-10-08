/**
 * Opens and closes drawers, the modal panels the page header uses on small screens.
 *
 * Openers reference a drawer through `data-drawer-target="<id>"`, elements with
 * `data-drawer-close` inside a drawer close it. The state is exposed as `data-open`
 * on the drawer and as `aria-expanded` on its openers, the visibility is up to CSS.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */
define(["require", "exports", "tslib", "focus-trap", "../Ui/CloseOverlay", "../Ui/Screen"], function (require, exports, tslib_1, focus_trap_1, CloseOverlay_1, Screen_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isOpen = isOpen;
    exports.open = open;
    exports.close = close;
    exports.setup = setup;
    CloseOverlay_1 = tslib_1.__importStar(CloseOverlay_1);
    const drawers = new Map();
    let initialized = false;
    function getDrawer(id) {
        const drawer = document.getElementById(id);
        if (drawer === null) {
            throw new Error(`Unable to find the drawer '${id}'.`);
        }
        return drawer;
    }
    function getState(drawer) {
        let state = drawers.get(drawer);
        if (state === undefined) {
            const backdrop = document.createElement("div");
            backdrop.classList.add("drawerBackdrop");
            backdrop.hidden = true;
            backdrop.addEventListener("click", () => close(drawer.id));
            drawer.before(backdrop);
            drawer.tabIndex = -1;
            drawer.addEventListener("keydown", (event) => {
                if (event.key === "Escape") {
                    event.preventDefault();
                    close(drawer.id);
                }
            });
            const newState = {
                backdrop,
                focusTrap: (0, focus_trap_1.createFocusTrap)(drawer, {
                    allowOutsideClick: true,
                    escapeDeactivates: false,
                    fallbackFocus: drawer,
                    setReturnFocus: (previousActiveElement) => newState.opener ?? previousActiveElement,
                }),
                opener: undefined,
            };
            drawers.set(drawer, newState);
            state = newState;
        }
        return state;
    }
    function setExpanded(id, expanded) {
        document.querySelectorAll(`[data-drawer-target="${CSS.escape(id)}"]`).forEach((opener) => {
            opener.setAttribute("aria-expanded", expanded ? "true" : "false");
        });
    }
    function closeAll() {
        drawers.forEach((_state, drawer) => {
            close(drawer.id);
        });
    }
    function isOpen(id) {
        return document.getElementById(id)?.hasAttribute("data-open") ?? false;
    }
    function open(id, options = {}) {
        const drawer = getDrawer(id);
        if (drawer.hasAttribute("data-open")) {
            return;
        }
        // Closes dropdowns, the search and other drawers.
        CloseOverlay_1.default.execute();
        const state = getState(drawer);
        state.opener = options.opener;
        drawer.setAttribute("data-open", "");
        drawer.setAttribute("role", "dialog");
        drawer.setAttribute("aria-modal", "true");
        state.backdrop.hidden = false;
        setExpanded(id, true);
        (0, Screen_1.pageOverlayOpen)();
        (0, Screen_1.scrollDisable)();
        drawer.dispatchEvent(new CustomEvent("drawer:open", { detail: options.detail }));
        state.focusTrap.activate();
    }
    function close(id) {
        const drawer = document.getElementById(id);
        if (drawer === null || !drawer.hasAttribute("data-open")) {
            return;
        }
        const state = getState(drawer);
        drawer.removeAttribute("data-open");
        drawer.removeAttribute("role");
        drawer.removeAttribute("aria-modal");
        state.backdrop.hidden = true;
        setExpanded(id, false);
        (0, Screen_1.pageOverlayClose)();
        (0, Screen_1.scrollEnable)();
        // The focus is returned asynchronously, `state.opener` must survive until then.
        state.focusTrap.deactivate();
        drawer.dispatchEvent(new CustomEvent("drawer:close"));
    }
    function setup() {
        if (initialized) {
            return;
        }
        initialized = true;
        document.addEventListener("click", (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }
            const opener = event.target.closest("[data-drawer-target]");
            if (opener !== null) {
                event.preventDefault();
                const id = opener.dataset.drawerTarget;
                if (isOpen(id)) {
                    close(id);
                }
                else {
                    open(id, { opener });
                }
                return;
            }
            const closeButton = event.target.closest("[data-drawer-close]");
            if (closeButton !== null) {
                const drawer = closeButton.closest("[data-open]");
                if (drawer !== null) {
                    close(drawer.id);
                }
            }
        });
        // Clicks inside an open drawer bubble up to the body and must not close it,
        // clicks outside of it can only reach the backdrop. Dropdowns close all other
        // overlays when they open, which must spare the drawer containing them.
        CloseOverlay_1.default.add("WoltLabSuite/Core/Component/Drawer", (origin, identifier) => {
            if (origin === CloseOverlay_1.Origin.Document) {
                return;
            }
            if (origin === CloseOverlay_1.Origin.DropDown && identifier !== undefined) {
                if (document.getElementById(identifier)?.closest("[data-open]")) {
                    return;
                }
            }
            closeAll();
        });
        (0, Screen_1.on)("screen-lg", {
            match: () => closeAll(),
        });
    }
});
