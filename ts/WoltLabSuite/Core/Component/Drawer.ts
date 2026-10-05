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

import { createFocusTrap, FocusTrap } from "focus-trap";
import UiCloseOverlay, { Origin } from "../Ui/CloseOverlay";
import { on as onMediaQuery, pageOverlayClose, pageOverlayOpen, scrollDisable, scrollEnable } from "../Ui/Screen";

type DrawerState = {
  backdrop: HTMLElement;
  focusTrap: FocusTrap;
  opener: HTMLElement | undefined;
};

export type OpenOptions = {
  /** Receives the focus when the drawer closes. */
  opener?: HTMLElement;
  /** Passed as `detail` of the `drawer:open` event. */
  detail?: unknown;
};

const drawers = new Map<HTMLElement, DrawerState>();
let initialized = false;

function getDrawer(id: string): HTMLElement {
  const drawer = document.getElementById(id);
  if (drawer === null) {
    throw new Error(`Unable to find the drawer '${id}'.`);
  }

  return drawer;
}

function getState(drawer: HTMLElement): DrawerState {
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

    const newState: DrawerState = {
      backdrop,
      focusTrap: createFocusTrap(drawer, {
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

function setExpanded(id: string, expanded: boolean): void {
  document.querySelectorAll(`[data-drawer-target="${CSS.escape(id)}"]`).forEach((opener) => {
    opener.setAttribute("aria-expanded", expanded ? "true" : "false");
  });
}

function closeAll(): void {
  drawers.forEach((_state, drawer) => {
    close(drawer.id);
  });
}

export function isOpen(id: string): boolean {
  return document.getElementById(id)?.hasAttribute("data-open") ?? false;
}

export function open(id: string, options: OpenOptions = {}): void {
  const drawer = getDrawer(id);
  if (drawer.hasAttribute("data-open")) {
    return;
  }

  // Closes dropdowns, the search and other drawers.
  UiCloseOverlay.execute();

  const state = getState(drawer);
  state.opener = options.opener;

  drawer.setAttribute("data-open", "");
  drawer.setAttribute("role", "dialog");
  drawer.setAttribute("aria-modal", "true");
  state.backdrop.hidden = false;
  setExpanded(id, true);

  pageOverlayOpen();
  scrollDisable();

  drawer.dispatchEvent(new CustomEvent("drawer:open", { detail: options.detail }));

  state.focusTrap.activate();
}

export function close(id: string): void {
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

  pageOverlayClose();
  scrollEnable();

  // The focus is returned asynchronously, `state.opener` must survive until then.
  state.focusTrap.deactivate();

  drawer.dispatchEvent(new CustomEvent("drawer:close"));
}

export function setup(): void {
  if (initialized) {
    return;
  }
  initialized = true;

  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const opener = event.target.closest<HTMLElement>("[data-drawer-target]");
    if (opener !== null) {
      event.preventDefault();

      const id = opener.dataset.drawerTarget!;
      if (isOpen(id)) {
        close(id);
      } else {
        open(id, { opener });
      }

      return;
    }

    const closeButton = event.target.closest<HTMLElement>("[data-drawer-close]");
    if (closeButton !== null) {
      const drawer = closeButton.closest<HTMLElement>("[data-open]");
      if (drawer !== null) {
        close(drawer.id);
      }
    }
  });

  // Clicks inside an open drawer bubble up to the body and must not close it,
  // clicks outside of it can only reach the backdrop. Dropdowns close all other
  // overlays when they open, which must spare the drawer containing them.
  UiCloseOverlay.add("WoltLabSuite/Core/Component/Drawer", (origin, identifier) => {
    if (origin === Origin.Document) {
      return;
    }

    if (origin === Origin.DropDown && identifier !== undefined) {
      if (document.getElementById(identifier)?.closest("[data-open]")) {
        return;
      }
    }

    closeAll();
  });

  onMediaQuery("screen-lg", {
    match: () => closeAll(),
  });
}
