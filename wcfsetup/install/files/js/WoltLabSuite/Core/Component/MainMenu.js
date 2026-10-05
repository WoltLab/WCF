/**
 * Submenus and the overflow ("priority+") of the main menu rendered by `system_pageHeaderMenu`.
 *
 * Submenus are opened through the `aria-expanded` state of their toggle button. On desktop
 * they also open on hover intent, items that do not fit into the bar are moved into the
 * trailing overflow item, except for the item of the current page.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */
define(["require", "exports", "tslib", "../Ui/CloseOverlay", "../Ui/Screen"], function (require, exports, tslib_1, CloseOverlay_1, Screen_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    CloseOverlay_1 = tslib_1.__importStar(CloseOverlay_1);
    const OPEN_DELAY = 150;
    const CLOSE_DELAY = 300;
    let menu;
    let overflowItem;
    let overflowList;
    let items = [];
    let lastPointerType = "";
    let isUpdatingOverflow = false;
    const timers = new WeakMap();
    function isDesktop() {
        return (0, Screen_1.is)("screen-lg");
    }
    function getToggle(item) {
        return item.querySelector(":scope > .boxMenuToggle");
    }
    function isOpen(item) {
        return getToggle(item)?.getAttribute("aria-expanded") === "true";
    }
    function setOpen(item, open) {
        window.clearTimeout(timers.get(item));
        const toggle = getToggle(item);
        if (toggle === null) {
            return;
        }
        if (open) {
            for (const sibling of item.parentElement.children) {
                if (sibling !== item) {
                    getToggle(sibling)?.setAttribute("aria-expanded", "false");
                }
            }
        }
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
    }
    function closeAll() {
        menu.querySelectorAll(".boxMenuToggle").forEach((toggle) => {
            toggle.setAttribute("aria-expanded", "false");
        });
    }
    function scheduleOpen(item, open) {
        window.clearTimeout(timers.get(item));
        timers.set(item, window.setTimeout(() => setOpen(item, open), open ? OPEN_DELAY : CLOSE_DELAY));
    }
    function setupHoverIntent(item) {
        // Items that were moved into the overflow item are part of its panel and do not open on their own.
        const isTopLevel = (event) => event.pointerType === "mouse" && item.parentElement === menu && isDesktop();
        item.addEventListener("pointerenter", (event) => {
            if (isTopLevel(event)) {
                scheduleOpen(item, true);
            }
        });
        item.addEventListener("pointerleave", (event) => {
            if (isTopLevel(event)) {
                scheduleOpen(item, false);
            }
        });
        item.addEventListener("focusout", (event) => {
            // Moving the focused element into or out of the overflow item blurs it, the focus is restored afterwards.
            if (isUpdatingOverflow || item.parentElement !== menu || !isDesktop()) {
                return;
            }
            if (!(event.relatedTarget instanceof Node) || !item.contains(event.relatedTarget)) {
                setOpen(item, false);
            }
        });
    }
    function isOverflowing() {
        const { left, right } = menu.getBoundingClientRect();
        // The toggles overlap the edge of their item, therefore the items are measured
        // instead of relying on the scroll width of the menu. Both edges are checked
        // because the items overflow to the left in RTL.
        return Array.from(menu.children).some((item) => {
            if (item.hidden) {
                return false;
            }
            const rect = item.getBoundingClientRect();
            return rect.left < left - 0.5 || rect.right > right + 0.5;
        });
    }
    function updateOverflow() {
        const previousOverflow = new Set(overflowList.children);
        const focusedElement = document.activeElement;
        isUpdatingOverflow = true;
        // Only the items in the overflow are moved back, items that stay in place keep their focus.
        let reference = overflowItem;
        for (let i = items.length - 1; i >= 0; i--) {
            if (items[i].parentElement !== menu) {
                menu.insertBefore(items[i], reference);
            }
            reference = items[i];
        }
        overflowItem.hidden = true;
        // The bar is only collapsed on desktop, the mobile drawer lists all items.
        if (isDesktop() && isOverflowing()) {
            overflowItem.hidden = false;
            const movableItems = items.filter((item) => !item.classList.contains("active"));
            for (let i = movableItems.length - 1; i >= 0 && isOverflowing(); i--) {
                overflowList.prepend(movableItems[i]);
            }
        }
        // Submenus stay open unless their item changed places, the bar is resized
        // for unrelated reasons too, e.g. by a scrollbar appearing.
        for (const item of items) {
            if (previousOverflow.has(item) !== (item.parentElement === overflowList)) {
                item.querySelectorAll(".boxMenuToggle").forEach((toggle) => {
                    toggle.setAttribute("aria-expanded", "false");
                });
            }
        }
        if (overflowItem.hidden) {
            setOpen(overflowItem, false);
        }
        if (focusedElement instanceof HTMLElement &&
            document.activeElement !== focusedElement &&
            menu.contains(focusedElement)) {
            focusedElement.focus({ preventScroll: true });
        }
        isUpdatingOverflow = false;
    }
    function setup() {
        const nav = document.getElementById("mainMenu");
        if (nav === null) {
            return;
        }
        menu = nav.querySelector(".boxMenu");
        overflowItem = menu.querySelector(":scope > .mainMenuOverflow");
        overflowList = overflowItem.querySelector("ol");
        items = Array.from(menu.children).filter((item) => item !== overflowItem);
        for (const item of menu.children) {
            if (getToggle(item) !== null) {
                setupHoverIntent(item);
            }
        }
        menu.addEventListener("pointerdown", (event) => {
            lastPointerType = event.pointerType;
        });
        menu.addEventListener("click", (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }
            const toggle = event.target.closest(".boxMenuToggle");
            if (toggle !== null && menu.contains(toggle)) {
                const item = toggle.parentElement;
                // Hover intent has usually opened the submenu by the time the click arrives, a click
                // with the mouse must not close it again. Clicks from the keyboard have no `detail`.
                const isMouseClick = event.detail > 0 && lastPointerType === "mouse";
                if (isMouseClick && item.parentElement === menu && isDesktop()) {
                    setOpen(item, true);
                }
                else {
                    setOpen(item, !isOpen(item));
                }
            }
        });
        menu.addEventListener("keydown", (event) => {
            if (event.key !== "Escape" || !(document.activeElement instanceof HTMLElement)) {
                return;
            }
            const item = document.activeElement.closest(".boxMenuHasChildren");
            if (item === null || !menu.contains(item)) {
                return;
            }
            const openItem = isOpen(item) ? item : item.parentElement?.closest(".boxMenuHasChildren");
            if (openItem && isOpen(openItem)) {
                // Collapses the submenu instead of closing the drawer that contains the menu.
                event.stopPropagation();
                setOpen(openItem, false);
                getToggle(openItem).focus();
            }
        });
        document.addEventListener("click", (event) => {
            if (isDesktop() && event.target instanceof Node && !nav.contains(event.target)) {
                closeAll();
            }
        });
        CloseOverlay_1.default.add("WoltLabSuite/Core/Component/MainMenu", (origin) => {
            if (origin !== CloseOverlay_1.Origin.Document && isDesktop()) {
                closeAll();
            }
        });
        // Submenus behave differently in the bar and in the drawer, therefore none stays open.
        const resetMenu = () => {
            closeAll();
            updateOverflow();
        };
        (0, Screen_1.on)("screen-lg", {
            match: resetMenu,
            unmatch: resetMenu,
        });
        // The bar changes its width with the viewport, the menu shrinks when other items in the bar grow.
        const observer = new ResizeObserver(() => updateOverflow());
        observer.observe(nav.parentElement);
        observer.observe(nav);
        void document.fonts.ready.then(() => updateOverflow());
        updateOverflow();
    }
});
