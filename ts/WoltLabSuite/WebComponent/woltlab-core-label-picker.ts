/**
 * `<woltlab-core-label-picker>` provides a dropdown to select a single label
 * out of a label group. The element is form-associated and submits the id of
 * the selected label, `0` for no selection or `-1` for objects without any
 * label of this group (requires `invertible`).
 *
 * Usage: `<woltlab-core-label-picker name="labelIDs[1]" labels="[[1,&quot;…&quot;]]" value="1"></woltlab-core-label-picker>`
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

{
  class WoltlabCoreLabelPickerElement extends HTMLElement {
    static formAssociated = true;

    readonly #internals: ElementInternals;
    #button: HTMLButtonElement | undefined = undefined;
    #defaultValue = 0;
    #items = new Map<number, HTMLLIElement>();
    #labels = new Map<number, string>();

    constructor() {
      super();

      this.#internals = this.attachInternals();
    }

    connectedCallback() {
      // The dropdown menu is moved into a global container, building the
      // element again when it is moved within the DOM would orphan it.
      if (this.#button !== undefined) {
        return;
      }

      if (this.hasAttribute("labels")) {
        this.#labels = new Map(JSON.parse(this.getAttribute("labels")!));
        this.removeAttribute("labels");
      }

      this.#defaultValue = this.value;

      this.innerHTML = "";
      this.classList.add("dropdown");

      this.#button = document.createElement("button");
      this.#button.type = "button";
      this.#button.classList.add("dropdownToggle");
      this.#button.disabled = this.matches(":disabled");

      this.append(this.#button, this.#buildMenu());

      // Clicks on an associated `<label>` are forwarded to the host element.
      this.addEventListener("click", (event) => {
        if (event.target === this) {
          this.#button!.focus();
        }
      });

      this.#render();
    }

    formDisabledCallback(disabled: boolean): void {
      if (this.#button !== undefined) {
        this.#button.disabled = disabled;
      }
    }

    formResetCallback(): void {
      this.setAttribute("value", this.#defaultValue.toString());
      this.#render();
    }

    formStateRestoreCallback(state: string | File | FormData | null): void {
      if (typeof state !== "string") {
        return;
      }

      // The restored state might reference a label that no longer exists.
      const value = parseInt(state);
      if (this.#isValidValue(value)) {
        this.setAttribute("value", value.toString());
        this.#render();
      }
    }

    #isValidValue(value: number): boolean {
      if (value === -1) {
        return this.invertible;
      }

      return value === 0 || this.#labels.has(value);
    }

    #buildMenu(): HTMLElement {
      const labelList = document.createElement("ul");
      labelList.classList.add("scrollableDropdownMenu");
      for (const [labelId, html] of this.#labels) {
        labelList.append(this.#createItem(labelId, html));
      }

      const dropdownMenu = document.createElement("div");
      dropdownMenu.classList.add("dropdownMenu");
      dropdownMenu.append(labelList);

      if (this.invertible || !this.required) {
        const divider = document.createElement("li");
        divider.classList.add("dropdownDivider");

        const optionList = document.createElement("ul");
        optionList.append(divider);

        if (this.invertible) {
          optionList.append(this.#createItem(-1, this.#getBadge("wcf.label.withoutSelection")));
        }

        if (!this.required) {
          optionList.append(this.#createItem(0, this.#getBadge("wcf.label.none")));
        }

        dropdownMenu.append(optionList);
      }

      return dropdownMenu;
    }

    #createItem(labelId: number, html: string): HTMLLIElement {
      const button = document.createElement("button");
      button.type = "button";
      button.innerHTML = html;
      button.addEventListener("click", () => {
        if (this.value === labelId) {
          return;
        }

        this.value = labelId;
        this.dispatchEvent(new Event("input", { bubbles: true }));
        this.dispatchEvent(new Event("change", { bubbles: true }));
      });

      const listItem = document.createElement("li");
      listItem.append(button);
      this.#items.set(labelId, listItem);

      return listItem;
    }

    #getBadge(phrase: string): string {
      const badge = document.createElement("span");
      badge.classList.add("badge", "label");
      badge.textContent = window.WoltLabLanguage.getPhrase(phrase);

      return badge.outerHTML;
    }

    #render(): void {
      const value = this.value;

      if (this.#button !== undefined) {
        if (value === -1) {
          this.#button.innerHTML = this.#getBadge("wcf.label.withoutSelection");
        } else {
          this.#button.innerHTML = this.#labels.get(value) ?? this.#getBadge("wcf.label.none");
        }
      }

      for (const [labelId, listItem] of this.#items) {
        listItem.classList.toggle("active", labelId === value);
      }

      this.#internals.setFormValue(value.toString());
      this.#updateValidity(value);
    }

    #updateValidity(value: number): void {
      if (this.required && value === 0) {
        this.#internals.setValidity(
          { valueMissing: true },
          window.WoltLabLanguage.getPhrase("wcf.global.form.error.empty"),
          this.#button,
        );
      } else {
        this.#internals.setValidity({});
      }
    }

    get value(): number {
      const value = parseInt(this.getAttribute("value") || "0");

      return Number.isNaN(value) ? 0 : value;
    }

    set value(value: number) {
      if (!this.#isValidValue(value)) {
        throw new Error(`There is no label with the id ${value}.`);
      }

      this.setAttribute("value", value.toString());
      this.#render();
    }

    get name(): string {
      return this.getAttribute("name") || "";
    }

    get form(): HTMLFormElement | null {
      return this.#internals.form;
    }

    get disabled(): boolean {
      return this.hasAttribute("disabled");
    }

    set disabled(disabled: boolean) {
      this.toggleAttribute("disabled", disabled);
    }

    get required(): boolean {
      return this.hasAttribute("required");
    }

    get invertible(): boolean {
      return this.hasAttribute("invertible");
    }
  }

  window.customElements.define("woltlab-core-label-picker", WoltlabCoreLabelPickerElement);
}
