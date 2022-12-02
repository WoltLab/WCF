/**
 * The `<woltlab-core-pagination>` creates a pagination.
 * Usage: `<woltlab-core-pagination page="1" count="10" url="https://www.woltlab.com"></woltlab-core-pagination>`
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

{
  class WoltlabCorePaginationElement extends HTMLElement {
    #className = "new-pagination";

    connectedCallback() {
      this.#render();
    }

    #render(): void {
      this.innerHTML = "";

      if (this.#getCount() < 2) return;

      const nav = this.#getNavElement();
      this.append(nav);

      const previousLinkElement = this.#getPreviousLinkElement();
      if (previousLinkElement) {
        nav.append(previousLinkElement);
      }

      const ul = document.createElement("ul");
      ul.classList.add(this.#className + "__list");
      nav.append(ul);

      ul.append(this.#getLinkItem(1));
      if (this.#getPage() > 4) {
        ul.append(this.#getEllipsesItem());
      }

      this.#getLinkItems().forEach((item) => {
        ul.append(item);
      });

      if (this.#getCount() - this.#getPage() > 3) {
        ul.append(this.#getEllipsesItem());
      }
      ul.append(this.#getLinkItem(this.#getCount()));

      const nextLinkElement = this.#getNextLinkElement();
      if (nextLinkElement) {
        nav.append(nextLinkElement);
      }
    }

    #getNavElement(): HTMLElement {
      const nav = document.createElement("nav");
      nav.setAttribute("role", "navigation");
      nav.ariaLabel = "Pagination Navigation";
      nav.classList.add(this.#className);

      return nav;
    }

    #getPreviousLinkElement(): HTMLDivElement | undefined {
      if (this.#getPage() === 1) {
        return;
      }

      const div = document.createElement("div");
      div.classList.add(this.#className + "__prev");

      const a = document.createElement("a");
      a.href = this.#getLinkUrl(this.#getPage() - 1);
      a.rel = "prev";
      a.title = "Goto Previous Page";
      a.classList.add("jsTooltip", this.#className + "__link");
      div.append(a);

      const icon = document.createElement("fa-icon");
      icon.setIcon("arrow-left");
      icon.ariaHidden = "true";
      a.append(icon);

      return div;
    }

    #getNextLinkElement(): HTMLDivElement | undefined {
      if (this.#getPage() === this.#getCount()) {
        return;
      }

      const div = document.createElement("div");
      div.classList.add(this.#className + "__next");

      const a = document.createElement("a");
      a.href = this.#getLinkUrl(this.#getPage() + 1);
      a.rel = "next";
      a.title = "Goto Next Page";
      a.classList.add("jsTooltip", this.#className + "__link");
      div.append(a);

      const icon = document.createElement("fa-icon");
      icon.setIcon("arrow-right");
      icon.ariaHidden = "true";
      a.append(icon);

      return div;
    }

    #getLinkItem(page: number): HTMLLIElement {
      const li = document.createElement("li");
      li.classList.add(this.#className + "__item");

      const a = document.createElement("a");
      a.href = this.#getLinkUrl(page);
      a.ariaLabel = `Page ${page}`;
      a.classList.add(this.#className + "__link");
      if (page === this.#getPage()) {
        a.ariaCurrent = "page";
        a.classList.add(this.#className + "__link--current");
      }
      a.textContent = page.toString();
      li.append(a);

      return li;
    }

    #getLinkItems(): HTMLLIElement[] {
      const items: HTMLLIElement[] = [];

      let start = this.#getPage() - 1;
      if (start === 3) {
        start--;
      }
      let end = this.#getPage() + 1;
      if (end === this.#getCount() - 2) {
        end++;
      }

      for (let i = start; i <= end; i++) {
        if (i <= 1 || i >= this.#getCount()) {
          continue;
        }

        items.push(this.#getLinkItem(i));
      }

      return items;
    }

    #getEllipsesItem(): HTMLLIElement {
      const li = document.createElement("li");
      li.classList.add(this.#className + "__item", this.#className + "__item--ellipses");
      li.innerHTML = "&ctdot;";

      return li;
    }

    #getLinkUrl(page: number): string {
      const url = new URL(this.#getUrl());
      url.search += url.search !== "" ? "&" : "?";
      url.search += new URLSearchParams([["pageNo", page.toString()]]);

      return url.toString();
    }

    #getCount(): number {
      return this.getAttribute("count") ? parseInt(this.getAttribute("count")!) : 0;
    }

    #getPage(): number {
      return this.getAttribute("page") ? parseInt(this.getAttribute("page")!) : 1;
    }

    #getUrl(): string {
      return this.getAttribute("url")!;
    }
  }

  window.customElements.define("woltlab-core-pagination", WoltlabCorePaginationElement);
}
