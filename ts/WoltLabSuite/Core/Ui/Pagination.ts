/**
 * Callback-based pagination.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.0 Use `<woltlab-core-pagination>` directly.
 */
class UiPagination {
  readonly #callbackSwitch?: CallbackSwitch;
  readonly #callbackShouldSwitch?: CallbackShouldSwitch;

  readonly #pagination: WoltlabCorePaginationElement;

  constructor(element: HTMLElement, options: PaginationOptions) {
    if (typeof options.callbackSwitch === "function") {
      this.#callbackSwitch = options.callbackSwitch;
    }
    if (typeof options.callbackShouldSwitch === "function") {
      this.#callbackShouldSwitch = options.callbackShouldSwitch;
    }

    this.#pagination = document.createElement("woltlab-core-pagination");
    this.#pagination.count = options.maxPage;
    this.#pagination.page = options.activePage;

    element.append(this.#pagination);

    this.#setupEventForwarding();
  }

  #setupEventForwarding() {
    this.#pagination.addEventListener("switchPage", (event: CustomEvent<number>) => {
      if (this.#callbackShouldSwitch !== undefined) {
        if (this.#callbackShouldSwitch(event.detail) === false) {
          event.preventDefault();
          return;
        }
      }

      if (this.#callbackSwitch) {
        this.#callbackSwitch(event.detail);
      }
    });
  }

  getActivePage(): number {
    return this.#pagination.page;
  }

  getElement(): HTMLElement {
    return this.#pagination.parentElement!;
  }

  getMaxPage(): number {
    return this.#pagination.count;
  }

  /**
   * Switches to given page number.
   */
  switchPage(pageNo: number, event?: MouseEvent): void {
    if (event instanceof MouseEvent) {
      event.preventDefault();

      const target = event.currentTarget as HTMLElement;
      // force tooltip to vanish and strip positioning
      if (target && target.dataset.tooltip) {
        const tooltip = document.getElementById("balloonTooltip");
        if (tooltip) {
          const event = new Event("mouseleave", {
            bubbles: true,
            cancelable: true,
          });
          target.dispatchEvent(event);

          tooltip.style.removeProperty("top");
          tooltip.style.removeProperty("bottom");
        }
      }
    }

    pageNo = ~~pageNo;
    if (pageNo > 0 && this.#pagination.page !== pageNo && pageNo <= this.#pagination.count) {
      if (this.#callbackShouldSwitch !== undefined) {
        if (!this.#callbackShouldSwitch(pageNo)) {
          return;
        }
      }

      this.#pagination.jumpToPage(pageNo);

      if (this.#callbackSwitch !== undefined) {
        this.#callbackSwitch(pageNo);
      }
    }
  }
}

export = UiPagination;

type CallbackSwitch = (pageNo: number) => void;
type CallbackShouldSwitch = (pageNo: number) => boolean;

interface PaginationOptions {
  activePage: number;
  maxPage: number;
  callbackShouldSwitch?: CallbackShouldSwitch | null;
  callbackSwitch?: CallbackSwitch | null;
}
