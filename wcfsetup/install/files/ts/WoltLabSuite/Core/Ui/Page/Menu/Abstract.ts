/**
 * Provides a touch-friendly fullscreen menu.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Menu/Abstract
 */

import * as Core from "../../../Core";
import * as Environment from "../../../Environment";
import * as EventHandler from "../../../Event/Handler";
import * as Language from "../../../Language";
import * as DomTraverse from "../../../Dom/Traverse";
import * as UiScreen from "../../Screen";

const _pageContainer = document.getElementById("pageContainer")!;

const enum TouchPosition {
  AtEdge = 20,
  MovedHorizontally = 5,
  MovedVertically = 20,
}

/**
 * Which edge of the menu is touched? Empty string
 * if no menu is currently touched.
 *
 * One 'left', 'right' or ''.
 */
let _androidTouching = "";

interface ItemData {
  itemList: HTMLOListElement;
  parentItemList: HTMLOListElement;
}

abstract class UiPageMenuAbstract {
  private readonly activeList: HTMLOListElement[] = [];
  protected readonly button: HTMLElement;
  private depth = 0;
  private enabled = true;
  private readonly eventIdentifier: string;
  private readonly items = new Map<HTMLAnchorElement, ItemData>();
  protected readonly menu: HTMLElement;
  private removeActiveList = false;

  protected constructor(eventIdentifier: string, elementId: string, buttonSelector: string) {
    if (document.body.dataset.template === "packageInstallationSetup") {
      // work-around for WCFSetup on mobile
      return;
    }

    this.eventIdentifier = eventIdentifier;
    this.menu = document.getElementById(elementId)!;

    const callbackOpen = this.open.bind(this);
    this.button = document.querySelector(buttonSelector) as HTMLElement;
    this.button.addEventListener("click", callbackOpen);

    this.initItems();
    this.initHeader();

    EventHandler.add(this.eventIdentifier, "open", callbackOpen);
    EventHandler.add(this.eventIdentifier, "close", this.close.bind(this));
    EventHandler.add(this.eventIdentifier, "updateButtonState", this.updateButtonState.bind(this));

    this.menu.addEventListener("animationend", () => {
      if (!this.menu.classList.contains("open")) {
        this.menu.querySelectorAll(".menuOverlayItemList").forEach((itemList) => {
          // force the main list to be displayed
          itemList.classList.remove("active", "hidden");
        });
      }
    });

    this.menu.children[0].addEventListener("transitionend", () => {
      this.menu.classList.add("allowScroll");

      if (this.removeActiveList) {
        this.removeActiveList = false;

        const list = this.activeList.pop();
        if (list) {
          list.classList.remove("activeList");
        }
      }
    });

    const backdrop = document.createElement("div");
    backdrop.className = "menuOverlayMobileBackdrop";
    backdrop.addEventListener("click", this.close.bind(this));

    this.menu.insertAdjacentElement("afterend", backdrop);

    this.menu.parentElement!.insertBefore(backdrop, this.menu.nextSibling);

    this.updateButtonState();

    if (Environment.platform() === "android") {
      this.initializeAndroid();
    }
  }

  /**
   * Opens the menu.
   */
  open(event?: MouseEvent): boolean {
    if (!this.enabled) {
      return false;
    }

    if (event instanceof Event) {
      event.preventDefault();
    }

    this.menu.classList.add("open");
    this.menu.classList.add("allowScroll");
    this.menu.children[0].classList.add("activeList");

    UiScreen.scrollDisable();

    _pageContainer.classList.add("menuOverlay-" + this.menu.id);

    UiScreen.pageOverlayOpen();

    return true;
  }

  /**
   * Closes the menu.
   */
  close(event?: Event): boolean {
    if (event instanceof Event) {
      event.preventDefault();
    }

    if (this.menu.classList.contains("open")) {
      this.menu.classList.remove("open");

      UiScreen.scrollEnable();
      UiScreen.pageOverlayClose();

      _pageContainer.classList.remove("menuOverlay-" + this.menu.id);

      return true;
    }

    return false;
  }

  /**
   * Enables the touch menu.
   */
  enable(): void {
    this.enabled = true;
  }

  /**
   * Disables the touch menu.
   */
  disable(): void {
    this.enabled = false;

    this.close();
  }

  /**
   * Initializes the Android Touch Menu.
   */
  private initializeAndroid(): void {
    // specify on which side of the page the menu appears
    let appearsAt: "left" | "right";
    switch (this.menu.id) {
      case "pageUserMenuMobile":
        appearsAt = "right";
        break;
      case "pageMainMenuMobile":
        appearsAt = "left";
        break;
      default:
        return;
    }

    const backdrop = this.menu.nextElementSibling as HTMLElement;

    // horizontal position of the touch start
    let touchStart: { x: number; y: number } | undefined = undefined;

    document.addEventListener("touchstart", (event) => {
      const touches = event.touches;

      let isLeftEdge: boolean;
      let isRightEdge: boolean;

      const isOpen = this.menu.classList.contains("open");

      // check whether we touch the edges of the menu
      if (appearsAt === "left") {
        isLeftEdge = !isOpen && touches[0].clientX < TouchPosition.AtEdge;
        isRightEdge = isOpen && Math.abs(this.menu.offsetWidth - touches[0].clientX) < TouchPosition.AtEdge;
      } else {
        isLeftEdge =
          isOpen &&
          Math.abs(document.body.clientWidth - this.menu.offsetWidth - touches[0].clientX) < TouchPosition.AtEdge;
        isRightEdge = !isOpen && document.body.clientWidth - touches[0].clientX < TouchPosition.AtEdge;
      }

      // abort if more than one touch
      if (touches.length > 1) {
        if (_androidTouching) {
          Core.triggerEvent(document, "touchend");
        }
        return;
      }

      // break if a touch is in progress
      if (_androidTouching) {
        return;
      }

      // break if no edge has been touched
      if (!isLeftEdge && !isRightEdge) {
        return;
      }

      // break if a different menu is open
      if (UiScreen.pageOverlayIsActive()) {
        const found = _pageContainer.classList.contains(`menuOverlay-${this.menu.id}`);
        if (!found) {
          return;
        }
      }
      // break if redactor is in use
      if (document.documentElement.classList.contains("redactorActive")) {
        return;
      }

      touchStart = {
        x: touches[0].clientX,
        y: touches[0].clientY,
      };

      if (isLeftEdge) {
        _androidTouching = "left";
      }
      if (isRightEdge) {
        _androidTouching = "right";
      }
    });

    document.addEventListener("touchend", (event) => {
      // break if we did not start a touch
      if (!_androidTouching || !touchStart) {
        return;
      }

      // break if the menu did not even start opening
      if (!this.menu.classList.contains("open")) {
        // reset
        touchStart = undefined;
        _androidTouching = "";
        return;
      }

      // last known position of the finger
      let position: number;
      if (event) {
        position = event.changedTouches[0].clientX;
      } else {
        position = touchStart.x;
      }

      // clean up touch styles
      this.menu.classList.add("androidMenuTouchEnd");
      this.menu.style.removeProperty("transform");
      backdrop.style.removeProperty(appearsAt);
      this.menu.addEventListener(
        "transitionend",
        () => {
          this.menu.classList.remove("androidMenuTouchEnd");
        },
        { once: true },
      );

      // check whether the user moved the finger far enough
      if (appearsAt === "left") {
        if (_androidTouching === "left" && position < touchStart.x + 100) {
          this.close();
        }
        if (_androidTouching === "right" && position < touchStart.x - 100) {
          this.close();
        }
      } else {
        if (_androidTouching === "left" && position > touchStart.x + 100) {
          this.close();
        }
        if (_androidTouching === "right" && position > touchStart.x - 100) {
          this.close();
        }
      }

      // reset
      touchStart = undefined;
      _androidTouching = "";
    });

    document.addEventListener("touchmove", (event) => {
      // break if we did not start a touch
      if (!_androidTouching || !touchStart) {
        return;
      }

      const touches = event.touches;

      // check whether the user started moving in the correct direction
      // this avoids false positives, in case the user just wanted to tap
      let movedFromEdge = false;
      if (_androidTouching === "left") {
        movedFromEdge = touches[0].clientX > touchStart.x + TouchPosition.MovedHorizontally;
      }
      if (_androidTouching === "right") {
        movedFromEdge = touches[0].clientX < touchStart.x - TouchPosition.MovedHorizontally;
      }

      const movedVertically = Math.abs(touches[0].clientY - touchStart.y) > TouchPosition.MovedVertically;

      let isOpen = this.menu.classList.contains("open");
      if (!isOpen && movedFromEdge && !movedVertically) {
        // the menu is not yet open, but the user moved into the right direction
        this.open();
        isOpen = true;
      }

      if (isOpen) {
        // update CSS to the new finger position
        let position = touches[0].clientX;
        if (appearsAt === "right") {
          position = document.body.clientWidth - position;
        }
        if (position > this.menu.offsetWidth) {
          position = this.menu.offsetWidth;
        }
        if (position < 0) {
          position = 0;
        }

        const offset = (appearsAt === "left" ? 1 : -1) * (position - this.menu.offsetWidth);
        this.menu.style.setProperty("transform", `translateX(${offset}px)`);
        backdrop.style.setProperty(appearsAt, Math.min(this.menu.offsetWidth, position).toString() + "px");
      }
    });
  }

  /**
   * Initializes all menu items.
   */
  private initItems(): void {
    this.menu.querySelectorAll(".menuOverlayItemLink").forEach((element: HTMLAnchorElement) => {
      this.initItem(element);
    });
  }

  /**
   * Initializes a single menu item.
   */
  private initItem(item: HTMLAnchorElement): void {
    // check if it should contain a 'more' link w/ an external callback
    const parent = item.parentElement!;
    const more = parent.dataset.more;
    if (more) {
      item.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        EventHandler.fire(this.eventIdentifier, "more", {
          handler: this,
          identifier: more,
          item: item,
          parent: parent,
        });
      });

      return;
    }

    const itemList = item.nextElementSibling as HTMLOListElement;
    if (itemList === null) {
      return;
    }

    // handle static items with an icon-type button next to it (acp menu)
    if (itemList.nodeName !== "OL" && itemList.classList.contains("menuOverlayItemLinkIcon")) {
      // add wrapper
      const wrapper = document.createElement("span");
      wrapper.className = "menuOverlayItemWrapper";
      parent.insertBefore(wrapper, item);
      wrapper.appendChild(item);

      while (wrapper.nextElementSibling) {
        wrapper.appendChild(wrapper.nextElementSibling);
      }

      return;
    }

    const isLink = item.href !== "#";
    const parentItemList = parent.parentElement as HTMLOListElement;
    let itemTitle = itemList.dataset.title;

    this.items.set(item, {
      itemList: itemList,
      parentItemList: parentItemList,
    });

    if (!itemTitle) {
      itemTitle = DomTraverse.childByClass(item, "menuOverlayItemTitle")!.textContent!;
      itemList.dataset.title = itemTitle;
    }

    const callbackLink = this.showItemList.bind(this, item);
    if (isLink) {
      const wrapper = document.createElement("span");
      wrapper.className = "menuOverlayItemWrapper";
      parent.insertBefore(wrapper, item);
      wrapper.appendChild(item);

      const moreLink = document.createElement("a");
      moreLink.href = "#";
      moreLink.className = "menuOverlayItemLinkIcon" + (item.classList.contains("active") ? " active" : "");
      moreLink.innerHTML = '<span class="icon icon24 fa-angle-right"></span>';
      moreLink.addEventListener("click", callbackLink);
      wrapper.appendChild(moreLink);
    } else {
      item.classList.add("menuOverlayItemLinkMore");
      item.addEventListener("click", callbackLink);
    }

    const backLinkItem = document.createElement("li");
    backLinkItem.className = "menuOverlayHeader";

    const wrapper = document.createElement("span");
    wrapper.className = "menuOverlayItemWrapper";

    const backLink = document.createElement("a");
    backLink.href = "#";
    backLink.className = "menuOverlayItemLink menuOverlayBackLink";
    backLink.textContent = parentItemList.dataset.title || "";
    backLink.addEventListener("click", this.hideItemList.bind(this, item));

    const closeLink = document.createElement("a");
    closeLink.href = "#";
    closeLink.className = "menuOverlayItemLinkIcon";
    closeLink.innerHTML = '<span class="icon icon24 fa-times"></span>';
    closeLink.addEventListener("click", this.close.bind(this));

    wrapper.appendChild(backLink);
    wrapper.appendChild(closeLink);
    backLinkItem.appendChild(wrapper);

    itemList.insertBefore(backLinkItem, itemList.firstElementChild);

    if (!backLinkItem.nextElementSibling!.classList.contains("menuOverlayTitle")) {
      const titleItem = document.createElement("li");
      titleItem.className = "menuOverlayTitle";
      const title = document.createElement("span");
      title.textContent = itemTitle;
      titleItem.appendChild(title);

      itemList.insertBefore(titleItem, backLinkItem.nextElementSibling);
    }
  }

  /**
   * Renders the menu item list header.
   */
  private initHeader(): void {
    const listItem = document.createElement("li");
    listItem.className = "menuOverlayHeader";

    const wrapper = document.createElement("span");
    wrapper.className = "menuOverlayItemWrapper";
    listItem.appendChild(wrapper);

    const logoWrapper = document.createElement("span");
    logoWrapper.className = "menuOverlayLogoWrapper";
    wrapper.appendChild(logoWrapper);

    const logo = document.createElement("span");
    logo.className = "menuOverlayLogo";
    const pageLogo = this.menu.dataset.pageLogo!;
    logo.style.setProperty("background-image", `url("${pageLogo}")`, "");
    logoWrapper.appendChild(logo);

    const closeLink = document.createElement("a");
    closeLink.href = "#";
    closeLink.className = "menuOverlayItemLinkIcon";
    closeLink.innerHTML = '<span class="icon icon24 fa-times"></span>';
    closeLink.addEventListener("click", this.close.bind(this));
    wrapper.appendChild(closeLink);

    const list = DomTraverse.childByClass(this.menu, "menuOverlayItemList")!;
    list.insertBefore(listItem, list.firstElementChild);
  }

  /**
   * Hides an item list, return to the parent item list.
   */
  private hideItemList(item: HTMLAnchorElement, event: MouseEvent): void {
    if (event instanceof Event) {
      event.preventDefault();
    }

    this.menu.classList.remove("allowScroll");
    this.removeActiveList = true;

    const data = this.items.get(item)!;
    data.parentItemList.classList.remove("hidden");

    this.updateDepth(false);
  }

  /**
   * Shows the child item list.
   */
  private showItemList(item: HTMLAnchorElement, event: MouseEvent): void {
    event.preventDefault();

    const data = this.items.get(item)!;

    const load = data.itemList.dataset.load;
    if (load) {
      if (!Core.stringToBool(item.dataset.loaded || "")) {
        const target = event.currentTarget as HTMLElement;
        const icon = target.firstElementChild!;
        if (icon.classList.contains("fa-angle-right")) {
          icon.classList.remove("fa-angle-right");
          icon.classList.add("fa-spinner");
        }

        EventHandler.fire(this.eventIdentifier, "load_" + load);

        return;
      }
    }

    this.menu.classList.remove("allowScroll");

    data.itemList.classList.add("activeList");
    data.parentItemList.classList.add("hidden");

    this.activeList.push(data.itemList);

    this.updateDepth(true);
  }

  private updateDepth(increase: boolean): void {
    this.depth += increase ? 1 : -1;

    let offset = this.depth * -100;
    if (Language.get("wcf.global.pageDirection") === "rtl") {
      // reverse logic for RTL
      offset *= -1;
    }

    const child = this.menu.children[0] as HTMLElement;
    child.style.setProperty("transform", `translateX(${offset}%)`, "");
  }

  protected updateButtonState(): void {
    let hasNewContent = false;
    const itemList = this.menu.querySelector(".menuOverlayItemList");
    this.menu.querySelectorAll(".badgeUpdate").forEach((badge) => {
      const value = badge.textContent!;
      if (~~value > 0 && badge.closest(".menuOverlayItemList") === itemList) {
        hasNewContent = true;
      }
    });

    this.button.classList[hasNewContent ? "add" : "remove"]("pageMenuMobileButtonHasContent");
  }
}

Core.enableLegacyInheritance(UiPageMenuAbstract);

export = UiPageMenuAbstract;
