import { UserMenuDataNotification } from "./Data/Notification";
import { UserMenuProvider } from "./Data/Provider";
import UserMenuView from "./View";
import * as Alignment from "../../Alignment";
import CloseOverlay from "../../CloseOverlay";

let container: HTMLElement | undefined = undefined;
const providers = new Set<UserMenuProvider>();
const views = new Map<UserMenuProvider, UserMenuView>();

function init(): void {
  providers.forEach((provider) => {
    const button = document.getElementById(provider.getPanelButtonId());
    if (button === null) {
      throw new Error(`Cannot find a panel button with the id '${provider.getPanelButtonId()}'.`);
    }

    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      const view = getView(provider);
      void view.open();

      const element = view.getElement();
      Alignment.set(element, button, { horizontal: "right" });
    });
  });

  CloseOverlay.add("WoltLabSuite/Ui/User/Menu", () => {
    providers.forEach((provider) => {
      if (views.has(provider)) {
        getView(provider).close();
      }
    });
  });
}

function getView(provider: UserMenuProvider): UserMenuView {
  if (!views.has(provider)) {
    const view = new UserMenuView(provider);
    getContainer().append(view.getElement());

    views.set(provider, view);
  }

  return views.get(provider)!;
}

function getContainer(): HTMLElement {
  if (container === undefined) {
    container = document.createElement("div");
    container.classList.add("dropdownMenuContainer");
    document.body.append(container);
  }

  return container;
}

export function setup(): void {
  if (providers.size === 0) {
    providers.add(new UserMenuDataNotification());

    init();
  }
}
