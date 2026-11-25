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

import type { CKEditor5 } from "@woltlab/editor";

const limitedDropdowns = new WeakSet<CKEditor5.DropdownView>();
const watchedCollections = new WeakSet<CKEditor5.ViewCollection>();

function getScrollContainer(element: HTMLElement | null): HTMLElement | null {
  return element?.closest<HTMLElement>(".dialog__content, .dialogContent") ?? null;
}

function limitPanelPosition(dropdown: CKEditor5.DropdownView, modules: typeof CKEditor5): void {
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

function watchCollection(collection: CKEditor5.ViewCollection, modules: typeof CKEditor5): void {
  if (watchedCollections.has(collection)) {
    return;
  }

  watchedCollections.add(collection);

  const apply = (view: CKEditor5.View): void => {
    if (view instanceof modules.DropdownView) {
      limitPanelPosition(view, modules);
    }
  };

  for (const view of collection) {
    apply(view);
  }

  collection.on<CKEditor5.CollectionAddEvent<CKEditor5.View>>("add", (_evt, view) => {
    apply(view);
  });
}

export function setup(editor: CKEditor5.ClassicEditor, modules: typeof CKEditor5): void {
  const toolbar = editor.ui.view.toolbar;

  watchCollection(toolbar.items, modules);

  // The dropdown for items that do not fit into the toolbar is added on demand.
  watchCollection(toolbar.children, modules);
}
