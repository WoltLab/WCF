/**
 * The `<woltlab-core-loading-indicator>` provides a ready-to-use
 * widget to indicate the loading status of a component to the user.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

type LoadingIndicatorIconSize = 24 | 48 | 96;

{
  const DefaultIconSize: LoadingIndicatorIconSize = 24;
  const ValidIconSizes = [24, 48, 96];

  class WoltlabCoreLoadingIndicatorElement extends HTMLElement {
    #icon?: FaIcon;
    #text?: HTMLElement;

    connectedCallback() {
      if (this.#icon === undefined) {
        this.#createDom();
      }
    }

    attributeChangedCallback(name: string, oldValue: string | null, newValue: string | null) {
      if (name === "size") {
        const newSize = parseInt(newValue || "");
        if (!ValidIconSizes.includes(newSize)) {
          let newValue = parseInt(oldValue || "");
          if (!ValidIconSizes.includes(newValue)) {
            newValue = DefaultIconSize;
          }

          this.setAttribute(name, newValue.toString());
        }
      }
    }

    #createDom(): void {
      this.classList.add("loading-indicator");

      if (!this.hasAttribute("size")) {
        this.setAttribute("size", DefaultIconSize.toString());
      }

      this.#icon = document.createElement("fa-icon");
      this.#icon.size = this.size;
      this.#icon.setIcon("spinner");

      this.#text = document.createElement("span");
      this.#text.classList.add("loading-indicator__text");
      this.#text.textContent = window.WoltLabLanguage.getPhrase("wcf.global.loading");
      this.#text.hidden = this.hideText;

      const container = document.createElement("div");
      container.classList.add("loading-indicator__wrapper");
      container.append(this.#icon, this.#text);

      this.append(container);
    }

    get size(): LoadingIndicatorIconSize {
      return parseInt(this.getAttribute("size")!) as LoadingIndicatorIconSize;
    }

    set size(size: LoadingIndicatorIconSize) {
      if (!ValidIconSizes.includes(size)) {
        throw new TypeError(`The size ${size} is unrecognized, permitted values are ${ValidIconSizes.join(", ")}.`);
      }

      this.setAttribute("size", size.toString());

      if (this.#icon) {
        this.#icon.size = size;
      }
    }

    get hideText(): boolean {
      return this.hasAttribute("hide-text");
    }

    set hideText(hideText: boolean) {
      if (hideText) {
        this.setAttribute("hide-text", "");
      } else {
        this.removeAttribute("hide-text");
      }

      if (this.#text) {
        this.#text.hidden = hideText;
      }
    }

    static get observedAttributes(): string[] {
      return ["size"];
    }
  }

  window.customElements.define("woltlab-core-loading-indicator", WoltlabCoreLoadingIndicatorElement);
}
