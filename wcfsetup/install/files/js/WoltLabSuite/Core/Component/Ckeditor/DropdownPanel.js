/**
 * Limits the automatic placement of toolbar dropdown panels to the visible
 * area of the surrounding dialog.
 *
 * Dropdown panels are rendered inside the toolbar and are therefore part of
 * the dialog's scrollable content area. CKEditor only checks the viewport
 * when picking the panel position, which causes panels near the right edge of
 * the toolbar to overflow the dialog and make its content horizontally
 * scrollable.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    const limitedDropdowns = new WeakSet();
    const watchedCollections = new WeakSet();
    function getScrollContainer(element) {
        return element?.closest(".dialog__content, .dialogContent") ?? null;
    }
    function limitPanelPosition(dropdown, modules) {
        if (limitedDropdowns.has(dropdown)) {
            return;
        }
        limitedDropdowns.add(dropdown);
        dropdown.panelPositionLimiter = () => getScrollContainer(dropdown.element);
        // Nested toolbars are created lazily when the dropdown is opened for the
        // first time, before any of their own dropdowns can be opened.
        dropdown.on("change:isOpen", () => {
            if (dropdown.toolbarView !== undefined) {
                watchCollection(dropdown.toolbarView.items, modules);
            }
        });
    }
    function watchCollection(collection, modules) {
        if (watchedCollections.has(collection)) {
            return;
        }
        watchedCollections.add(collection);
        const apply = (view) => {
            if (view instanceof modules.DropdownView) {
                limitPanelPosition(view, modules);
            }
        };
        for (const view of collection) {
            apply(view);
        }
        collection.on("add", (_evt, view) => {
            apply(view);
        });
    }
    function setup(editor, modules) {
        const toolbar = editor.ui.view.toolbar;
        watchCollection(toolbar.items, modules);
        // The dropdown for items that do not fit into the toolbar is added on demand.
        watchCollection(toolbar.children, modules);
    }
});
