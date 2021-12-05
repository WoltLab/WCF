import { UserMenuProvider } from "./Data/Provider";
import UserMenuView from "./View";
import * as Alignment from "../../Alignment";
import CloseOverlay from "../../CloseOverlay";
import DropDown from "../../Dropdown/Simple";

let container: HTMLElement | undefined = undefined;
const providers = new Set<UserMenuProvider>();
const views = new Map<UserMenuProvider, UserMenuView>();

function initProvider(provider: UserMenuProvider): void {
  providers.add(provider);

  const button = provider.getPanelButton();

  prepareButton(button);

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

function prepareButton(button: HTMLElement): void {
  const link = button.querySelector("a")!;
  link.setAttribute("role", "button");
  link.tabIndex = 0;
  link.setAttribute("aria-haspopup", "true");
  link.setAttribute("aria-expanded", "false");
}

function open(provider: UserMenuProvider): void {
  closeAll();
  DropDown.closeAll();

  const view = getView(provider);
  void view.open();

  const button = provider.getPanelButton();
  button.querySelector("a")!.setAttribute("aria-expanded", "true");
  button.classList.add("open");

  const element = view.getElement();
  Alignment.set(element, button, { horizontal: "right" });
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

function getContainer(): HTMLElement {
  if (container === undefined) {
    container = document.createElement("div");
    container.classList.add("dropdownMenuContainer");
    document.body.append(container);
  }

  return container;
}

export function registerProvider(provider: UserMenuProvider): void {
  if (providers.size === 0) {
    CloseOverlay.add("WoltLabSuite/Ui/User/Menu", () => closeAll());
  }

  initProvider(provider);
}
