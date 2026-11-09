/**
 * `<woltlab-core-label-picker>` provides a dropdown to select a single label
 * out of a label group. The element is form-associated and submits the id of
 * the selected label, `0` for no selection or `-1` for objects without any
 * label of this group (requires `invertible`).
 *
 * Setting `max` to a value greater than 1 allows the selection of up to `max`
 * labels. The value is then a comma-separated list of label ids and each
 * selected label is submitted as `name[]`.
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
    #defaultValue = "";
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

      if (this.multiple && this.invertible) {
        throw new Error("A label picker cannot be invertible if more than one label can be selected.");
      }

      this.#defaultValue = this.getAttribute("value") || "";

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
      this.setAttribute("value", this.#defaultValue);
      this.#render();
    }

    formStateRestoreCallback(state: string | File | FormData | null): void {
      if (typeof state !== "string") {
        return;
      }

      // The restored state might reference a label that no longer exists.
      const values = this.#parseValues(state);
      if (values.every((value) => this.#isValidValue(value))) {
        this.setAttribute("value", values.join(","));
        this.#render();
      }
    }

    #isValidValue(value: number): boolean {
      if (value === -1) {
        return this.invertible;
      }

      return value === 0 || this.#labels.has(value);
    }

    #parseValues(value: string): number[] {
      return value
        .split(",")
        .map((labelId) => parseInt(labelId))
        .filter((labelId) => !Number.isNaN(labelId) && labelId !== 0);
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

      // Labels are deselected individually when selecting multiple labels.
      if (!this.multiple && (this.invertible || !this.required)) {
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

      const listItem = document.createElement("li");
      listItem.append(button);
      this.#items.set(labelId, listItem);

      if (!this.multiple) {
        button.addEventListener("click", () => {
          if (this.value === labelId) {
            return;
          }

          this.value = labelId;
          this.#dispatchChange();
        });

        return listItem;
      }

      listItem.setAttribute("role", "menuitemcheckbox");
      button.addEventListener("click", (event) => {
        // Keep the menu open to allow the selection of further labels.
        event.stopPropagation();

        this.#toggleLabel(labelId);
      });
      listItem.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          // Prevent the dropdown from closing and moving the focus to the toggle.
          event.preventDefault();
          event.stopPropagation();

          this.#toggleLabel(labelId);
        }
      });

      return listItem;
    }

    #toggleLabel(labelId: number): void {
      const values = this.values;
      const index = values.indexOf(labelId);
      if (index === -1) {
        if (values.length >= this.max) {
          return;
        }

        values.push(labelId);
      } else {
        values.splice(index, 1);
      }

      this.values = values;
      this.#dispatchChange();
    }

    #dispatchChange(): void {
      this.dispatchEvent(new Event("input", { bubbles: true }));
      this.dispatchEvent(new Event("change", { bubbles: true }));
    }

    #getBadge(phrase: string): string {
      const badge = document.createElement("span");
      badge.classList.add("badge", "label");
      badge.textContent = window.WoltLabLanguage.getPhrase(phrase);

      return badge.outerHTML;
    }

    #render(): void {
      if (this.multiple) {
        this.#renderMultiple();
      } else {
        this.#renderSingle();
      }
    }

    #renderSingle(): void {
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
      this.#updateValidity(value === 0);
    }

    #renderMultiple(): void {
      const values = this.values;

      if (this.#button !== undefined) {
        const html = values.map((labelId) => this.#labels.get(labelId)!).join(" ");
        this.#button.innerHTML = html || this.#getBadge("wcf.label.none");
      }

      const limitReached = values.length >= this.max;
      for (const [labelId, listItem] of this.#items) {
        const checked = values.includes(labelId);
        listItem.classList.toggle("active", checked);
        listItem.setAttribute("aria-checked", checked.toString());

        const disabled = !checked && limitReached;
        listItem.classList.toggle("disabled", disabled);
        listItem.setAttribute("aria-disabled", disabled.toString());
      }

      const formData = new FormData();
      for (const labelId of values) {
        formData.append(`${this.name}[]`, labelId.toString());
      }
      this.#internals.setFormValue(formData, values.join(","));
      this.#updateValidity(values.length === 0);
    }

    #updateValidity(isEmpty: boolean): void {
      if (this.required && isEmpty) {
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
      return this.#parseValues(this.getAttribute("value") || "")[0] ?? 0;
    }

    set value(value: number) {
      if (!this.#isValidValue(value)) {
        throw new Error(`There is no label with the id ${value}.`);
      }

      this.setAttribute("value", value.toString());
      this.#render();
    }

    get values(): number[] {
      const values = this.#parseValues(this.getAttribute("value") || "");

      return this.multiple ? values : values.slice(0, 1);
    }

    set values(values: number[]) {
      if (values.length > this.max) {
        throw new Error(`Cannot select more than ${this.max} labels.`);
      }

      if (new Set(values).size !== values.length) {
        throw new Error("The list of labels contains duplicates.");
      }

      for (const value of values) {
        if (value === 0 || !this.#isValidValue(value)) {
          throw new Error(`There is no label with the id ${value}.`);
        }
      }

      this.setAttribute("value", values.join(","));
      this.#render();
    }

    get max(): number {
      const max = parseInt(this.getAttribute("max") || "1");

      return Number.isNaN(max) || max < 1 ? 1 : max;
    }

    get multiple(): boolean {
      return this.max > 1;
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
