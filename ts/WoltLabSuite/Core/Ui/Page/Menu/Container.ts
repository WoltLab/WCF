import { PageMenuProvider } from "./Provider";
import { createFocusTrap, FocusTrap } from "focus-trap";
import { pageOverlayClose, pageOverlayOpen, scrollDisable, scrollEnable } from "../../Screen";
import UiCloseOverlay from "../../CloseOverlay";
import DomUtil from "../../../Dom/Util";

export class PageMenuContainer {
  private readonly container = document.createElement("div");
  private readonly content = document.createElement("div");
  private focusTrap?: FocusTrap = undefined;
  private readonly provider: PageMenuProvider;

  constructor(provider: PageMenuProvider) {
    this.provider = provider;

    const menuId = DomUtil.identify(this.provider.getMenuButton());
    UiCloseOverlay.add(`WoltLabSuite/Core/Ui/PageMenu/Container-${menuId}`, () => {
      if (!this.container.hidden) {
        this.close();
      }
    });
  }

  open(): void {
    UiCloseOverlay.execute();

    this.buildElements();

    if (this.content.childElementCount === 0) {
      this.content.append(this.provider.getContent());
    }

    this.provider.getMenuButton().setAttribute("aria-expanded", "true");

    pageOverlayOpen();
    scrollDisable();

    this.container.hidden = false;
    this.provider.refresh();

    this.getFocusTrap().activate();
  }

  close(): void {
    this.provider.getMenuButton().setAttribute("aria-expanded", "false");

    pageOverlayClose();
    scrollEnable();

    this.container.hidden = true;
    this.getFocusTrap().deactivate();
  }

  toggle(): void {
    if (this.container.hidden) {
      this.open();
    } else {
      this.close();
    }
  }

  getContent(): HTMLElement {
    return this.content;
  }

  private buildElements(): void {
    if (this.container.classList.contains("pageMenuContainer")) {
      return;
    }

    this.container.classList.add("pageMenuContainer");
    this.container.hidden = true;
    this.container.addEventListener("click", (event) => {
      if (event.target === this.container) {
        this.close();
      }
    });

    this.content.classList.add("pageMenuContent");
    this.content.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    this.container.append(this.content);

    document.body.append(this.container);
  }

  private getFocusTrap(): FocusTrap {
    if (this.focusTrap === undefined) {
      this.focusTrap = createFocusTrap(this.content, {
        allowOutsideClick: true,
      });
    }

    return this.focusTrap;
  }
}

export default PageMenuContainer;
