/**
 * The `<woltlab-core-pagination>` creates a pagination.
 * Usage: `<woltlab-core-pagination page="1" count="10" url="https://www.woltlab.com"></woltlab-core-pagination>`
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

{
  let mediaQuery: MediaQueryList;
  const getMediaQueryScreenXs = (): MediaQueryList => {
    if (mediaQuery === undefined) {
      mediaQuery = window.matchMedia("(max-width: 544px)");
    }

    return mediaQuery;
  };

  interface WoltlabCorePaginationEventMap {
    jumpToPage: CustomEvent;
    switchPage: CustomEvent<number>;
  }

  class WoltlabCorePaginationElement extends HTMLElement {
    readonly #className = "pagination";

    connectedCallback() {
      this.#render();

      getMediaQueryScreenXs().addEventListener("change", () => this.#render());
    }

    #render(): void {
      this.innerHTML = "";

      if (this.count < 2) {
        return;
      }

      this.classList.add(`${this.#className}__wrapper`);

      const nav = this.#getNavElement();
      this.append(nav);

      const previousLinkElement = this.#getPreviousLinkElement();
      if (previousLinkElement) {
        nav.append(previousLinkElement);
      }

      const ul = document.createElement("ul");
      ul.classList.add(`${this.#className}__list`);
      nav.append(ul);

      ul.append(this.#getLinkItem(1));
      if (this.page > this.thresholdForEllipsis + 1) {
        ul.append(this.#getEllipsesItem());
      }

      this.#getLinkItems().forEach((item) => {
        ul.append(item);
      });

      if (this.count - this.page > this.thresholdForEllipsis) {
        ul.append(this.#getEllipsesItem());
      }
      ul.append(this.#getLinkItem(this.count));

      const nextLinkElement = this.#getNextLinkElement();
      if (nextLinkElement) {
        nav.append(nextLinkElement);
      }
    }

    #getNavElement(): HTMLElement {
      const nav = document.createElement("nav");
      nav.setAttribute("role", "navigation");
      nav.ariaLabel = window.WoltLabLanguage.getPhrase("wcf.page.pagination");
      nav.classList.add(this.#className);

      return nav;
    }

    #getPreviousLinkElement(): HTMLDivElement | undefined {
      if (this.page === 1) {
        return;
      }

      const div = document.createElement("div");
      div.classList.add(`${this.#className}__prev`);

      const button = this.#getButtonElement(this.page - 1);
      if (button instanceof HTMLAnchorElement) {
        button.rel = "prev";
      }
      button.title = window.WoltLabLanguage.getPhrase("wcf.global.page.previous");
      button.classList.add("jsTooltip");
      div.append(button);

      const icon = document.createElement("fa-icon");
      icon.setIcon("arrow-left");
      icon.ariaHidden = "true";
      button.append(icon);

      return div;
    }

    #getNextLinkElement(): HTMLDivElement | undefined {
      if (this.page === this.count) {
        return;
      }

      const div = document.createElement("div");
      div.classList.add(`${this.#className}__next`);

      const button = this.#getButtonElement(this.page + 1);
      if (button instanceof HTMLAnchorElement) {
        button.rel = "next";
      }
      button.title = window.WoltLabLanguage.getPhrase("wcf.global.page.next");
      button.classList.add("jsTooltip");
      div.append(button);

      const icon = document.createElement("fa-icon");
      icon.setIcon("arrow-right");
      icon.ariaHidden = "true";
      button.append(icon);

      return div;
    }

    #getButtonElement(page: number): HTMLAnchorElement | HTMLButtonElement {
      let button: HTMLAnchorElement | HTMLButtonElement;
      const url = this.getLinkUrl(page);

      if (url) {
        button = document.createElement("a");
        button.href = url;
      } else {
        button = document.createElement("button");
        button.type = "button";

        if (this.page === page) {
          button.disabled = true;
        } else {
          button.addEventListener("click", () => {
            this.#switchPage(page);
          });
        }
      }

      button.classList.add(`${this.#className}__link`);

      return button;
    }

    #getLinkItem(page: number): HTMLLIElement {
      const li = document.createElement("li");
      li.classList.add(`${this.#className}__item`);

      const button = this.#getButtonElement(page);
      button.ariaLabel = window.WoltLabLanguage.getPhrase("wcf.page.pageNo", { pageNo: page });
      if (page === this.page) {
        button.ariaCurrent = "page";
        button.classList.add(`${this.#className}__link--current`);
      }
      button.textContent = page.toString();
      li.append(button);

      return li;
    }

    /**
     * Generates page numbers that are adjacent to the current page.
     */
    #getLinkItems(): HTMLLIElement[] {
      const items: HTMLLIElement[] = [];

      let start: number;
      let end: number;

      if (getMediaQueryScreenXs().matches) {
        // On small devices show only the first, last and current
        // page number.
        start = this.page;
        end = this.page;
      } else {
        // Avoid generating an ellipsis which only skips a single
        // page number. Instead of `1 ⋯ 3` this code will always
        // generate `1 2 3`, but on page 5 it will be `1 ⋯ 4 5`.
        start = this.page - 1;
        if (start === 3) {
          start--;
        }
        end = this.page + 1;
        if (end === this.count - 2) {
          end++;
        }
      }

      for (let i = start; i <= end; i++) {
        if (i <= 1 || i >= this.count) {
          continue;
        }

        items.push(this.#getLinkItem(i));
      }

      return items;
    }

    #getEllipsesItem(): HTMLLIElement {
      const li = document.createElement("li");
      li.classList.add(`${this.#className}__item`, `${this.#className}__item--ellipses`);

      const button = document.createElement("button");
      button.type = "button";
      button.title = window.WoltLabLanguage.getPhrase("wcf.page.jumpTo");
      button.classList.add("pagination__link", "jsTooltip");
      button.innerHTML = "&ctdot;";
      button.addEventListener("click", () => {
        this.dispatchEvent(new CustomEvent("jumpToPage"));
      });
      li.append(button);

      return li;
    }

    /**
     * On smaller screens the ellipsis is shown when there
     * is at least one page in-between the current page and
     * the first or last page. On larger screens the ellipsis
     * is only shown if it hides at least two numbers.
     */
    get thresholdForEllipsis(): number {
      if (getMediaQueryScreenXs().matches) {
        return 1;
      }

      return 3;
    }

    getLinkUrl(page: number): string {
      if (!this.url) {
        return "";
      }

      const url = new URL(this.url);
      url.search += url.search !== "" ? "&" : "?";
      url.search += new URLSearchParams([["pageNo", page.toString()]]);

      return url.toString();
    }

    jumpToPage(page: number): void {
      const url = this.getLinkUrl(page);
      if (url) {
        window.location.href = url;
      } else {
        this.#switchPage(page);
      }
    }

    #switchPage(page: number): void {
      const event = new CustomEvent("switchPage", {
        cancelable: true,
        detail: page,
      });
      this.dispatchEvent(event);

      if (!event.defaultPrevented) {
        this.page = page;
      }
    }

    get count(): number {
      return this.hasAttribute("count") ? parseInt(this.getAttribute("count")!) : 0;
    }

    set count(count: number) {
      this.setAttribute("count", count.toString());
      this.#render();
    }

    get page(): number {
      return this.hasAttribute("page") ? parseInt(this.getAttribute("page")!) : 1;
    }

    set page(page: number) {
      this.setAttribute("page", page.toString());
      this.#render();
    }

    get url(): string {
      return this.getAttribute("url")!;
    }

    set url(url: string) {
      this.setAttribute("url", url);
      this.#render();
    }

    public addEventListener<T extends keyof WoltlabCorePaginationEventMap>(
      type: T,
      listener: (this: WoltlabCorePaginationElement, ev: WoltlabCorePaginationEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
    public addEventListener(
      type: string,
      listener: (this: WoltlabCorePaginationElement, ev: Event) => any,
      options?: boolean | AddEventListenerOptions,
    ): void {
      super.addEventListener(type, listener, options);
    }
  }

  window.customElements.define("woltlab-core-pagination", WoltlabCorePaginationElement);
}
