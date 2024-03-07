/**
 * Controls the behavior of the user menus.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { EventUpdateCounter, UserMenuProvider } from "./Data/Provider";
import UserMenuView from "./View";
import * as Alignment from "../../Alignment";
import CloseOverlay from "../../CloseOverlay";
import * as EventHandler from "../../../Event/Handler";
import DomUtil from "../../../Dom/Util";
import * as UiScreen from "../../Screen";
import { getPageOverlayContainer } from "../../../Helper/PageOverlay";

let container: HTMLElement | undefined = undefined;
const providers = new Set<UserMenuProvider>();
const views = new Map<UserMenuProvider, UserMenuView>();

function initProvider(provider: UserMenuProvider): void {
  providers.add(provider);

  const button = provider.getPanelButton();

  button.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (button.classList.contains("open")) {
      close(provider);
    } else {
      open(provider);
    }
  });
}

function open(provider: UserMenuProvider): void {
  CloseOverlay.execute();

  const view = getView(provider);
  void view.open();

  const button = provider.getPanelButton();
  button.querySelector("a")!.setAttribute("aria-expanded", "true");
  button.classList.add("open");

  const element = view.getElement();
  setAlignment(element, button);
}

function setAlignment(element: HTMLElement, referenceElement: HTMLElement): void {
  Alignment.set(element, referenceElement, { horizontal: "right" });

  if (window.getComputedStyle(element).position === "fixed" && DomUtil.getFixedParent(referenceElement) !== null) {
    const { top, height } = referenceElement.getBoundingClientRect();
    element.style.setProperty("top", `${top + height}px`);
  }
}

function close(provider: UserMenuProvider): void {
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
  button.querySelector("a")!.setAttribute("aria-expanded", "false");
}

function closeAll(): void {
  providers.forEach((provider) => close(provider));
}

function getView(provider: UserMenuProvider): UserMenuView {
  if (!views.has(provider)) {
    const view = provider.getView();
    const element = view.getElement();
    getContainer().append(element);

    element.addEventListener("shouldClose", () => close(provider));

    views.set(provider, view);
  }

  return views.get(provider)!;
}

export function getUserMenuProviders(): ReadonlySet<UserMenuProvider> {
  return providers;
}

/**
 * @since 6.1
 */
export function updateCounter(identifier: string, counter: number) {
  Array.from(providers)
    .filter((provider) => provider.getIdentifier() === identifier)
    .forEach((provider) => {
      provider.getPanelButton().dispatchEvent(
        new CustomEvent<EventUpdateCounter>("updateCounter", {
          detail: { counter: counter },
        }),
      );
    });
}

export function getContainer(): HTMLElement {
  if (container === undefined) {
    container = document.createElement("div");
    container.classList.add("dropdownMenuContainer");
    getPageOverlayContainer().append(container);
  }

  return container;
}

export function registerProvider(provider: UserMenuProvider): void {
  if (providers.size === 0) {
    CloseOverlay.add("WoltLabSuite/Ui/User/Menu", () => closeAll());

    EventHandler.add("com.woltlab.wcf.UserMenuMobile", "more", (data) => {
      providers.forEach((provider) => {
        if (data.identifier === provider.getIdentifier()) {
          open(provider);
        }
      });
    });

    // Update the position of the user menu if the browser is
    // resized while the menu is visible.
    window.addEventListener(
      "resize",
      () => {
        providers.forEach((provider) => {
          const button = provider.getPanelButton();
          if (button.classList.contains("open")) {
            const view = getView(provider);
            setAlignment(view.getElement(), button);
          }
        });
      },
      { passive: true },
    );

    UiScreen.on("screen-md-down", {
      match() {
        providers.forEach((provider) => {
          const button = provider.getPanelButton();
          if (button.classList.contains("open")) {
            close(provider);
          }
        });
      },
      setup() {
        providers.forEach((provider) => {
          const button = provider.getPanelButton();
          if (button.classList.contains("open")) {
            close(provider);
          }
        });
      },
    });
  }

  initProvider(provider);
}
