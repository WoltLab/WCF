/**
 * `<woltlab-core-label-picker>` provides a popover to select a single label
 * out of a label group. The element is form-associated and submits the id of
 * the selected label, `0` for no selection or `-1` for objects without any
 * label of this group (requires `invertible`).
 *
 * Setting `max` to a value greater than 1 allows the selection of up to `max`
 * labels. The value is then a comma-separated list of label ids and each
 * selected label is submitted as `name[]`.
 *
 * The labels are presented as a listbox inside a native popover. The popover
 * is positioned by `WoltLabSuite/Core/Component/LabelPicker`.
 *
 * Usage: `<woltlab-core-label-picker name="labelIDs[1]" labels="[[1,&quot;…&quot;]]" value="1"></woltlab-core-label-picker>`
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

{
  let pickerCount = 0;

  class WoltlabCoreLabelPickerElement extends HTMLElement {
    static formAssociated = true;

    readonly #internals: ElementInternals;
    #button: HTMLButtonElement | undefined = undefined;
    #defaultValue = "";
    #labels = new Map<number, string>();
    #listbox: HTMLElement | undefined = undefined;
    #options = new Map<number, HTMLElement>();
    #popover: HTMLElement | undefined = undefined;
    #typeahead = "";
    #typeaheadTimer: number | undefined = undefined;

    constructor() {
      super();

      this.#internals = this.attachInternals();
    }

    connectedCallback() {
      // Moving the element within the DOM must not build it again.
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

      const id = `labelPicker${++pickerCount}`;

      this.#popover = document.createElement("div");
      this.#popover.id = id;
      this.#popover.popover = "auto";
      this.#popover.classList.add("labelPicker__popover");
      this.#popover.addEventListener("toggle", (event) => {
        if (event.newState === "open") {
          this.#open();
        } else {
          this.#closed();
        }
      });

      this.#button = document.createElement("button");
      this.#button.type = "button";
      this.#button.classList.add("labelPicker__toggle");
      this.#button.disabled = this.matches(":disabled");
      this.#button.popoverTargetElement = this.#popover;
      this.#button.setAttribute("aria-haspopup", "listbox");
      this.#button.addEventListener("keydown", (event) => {
        if ((event.key === "ArrowDown" || event.key === "ArrowUp") && !this.#isOpen()) {
          event.preventDefault();

          this.#popover!.showPopover({ source: this.#button });
        }
      });

      this.#popover.append(...this.#buildContent(id));

      this.innerHTML = "";
      this.append(this.#button, this.#popover);

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

      if (disabled && this.#isOpen()) {
        this.#popover!.hidePopover();
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

    #buildContent(id: string): HTMLElement[] {
      this.#listbox = document.createElement("div");
      this.#listbox.classList.add("labelPicker__listbox");
      this.#listbox.setAttribute("role", "listbox");
      this.#listbox.addEventListener("keydown", (event) => this.#keydown(event));

      for (const [labelId, html] of this.#labels) {
        this.#listbox.append(this.#createOption(labelId, html));
      }

      if (this.multiple) {
        const limit = document.createElement("p");
        limit.id = `${id}Limit`;
        limit.classList.add("labelPicker__limit");
        limit.textContent = window.WoltLabLanguage.getPhrase("wcf.label.picker.maxSelection", { max: this.max });

        this.#listbox.setAttribute("aria-multiselectable", "true");
        this.#listbox.setAttribute("aria-describedby", limit.id);

        return [limit, this.#listbox];
      }

      // Selecting multiple labels deselects them individually instead.
      const options: HTMLElement[] = [];
      if (this.invertible) {
        options.push(this.#createOption(-1, this.#getBadge("wcf.label.withoutSelection")));
      }
      if (!this.required) {
        options.push(this.#createOption(0, this.#getBadge("wcf.label.none")));
      }

      if (options.length > 0) {
        options[0].classList.add("labelPicker__option--separated");
        this.#listbox.append(...options);
      }

      return [this.#listbox];
    }

    #createOption(labelId: number, html: string): HTMLElement {
      const icon = document.createElement("fa-icon");
      icon.classList.add("labelPicker__check");
      icon.setIcon("check");

      const label = document.createElement("span");
      label.classList.add("labelPicker__label");
      label.innerHTML = html;

      const option = document.createElement("div");
      option.classList.add("labelPicker__option");
      option.setAttribute("role", "option");
      option.tabIndex = -1;
      option.append(icon, label);
      option.addEventListener("click", () => this.#selectOption(labelId));
      this.#options.set(labelId, option);

      return option;
    }

    #isOpen(): boolean {
      return this.#popover?.matches(":popover-open") ?? false;
    }

    #open(): void {
      const label = this.#internals.labels[0]?.textContent?.trim() ?? "";
      if (label !== "") {
        this.#listbox!.setAttribute("aria-label", label);
      }

      const options = Array.from(this.#options.values());
      const option = options.find((option) => option.getAttribute("aria-selected") === "true") ?? options[0];
      option?.focus();
    }

    #closed(): void {
      this.#typeahead = "";
      window.clearTimeout(this.#typeaheadTimer);
    }

    #close(): void {
      this.#popover!.hidePopover();
      this.#button!.focus();
    }

    #keydown(event: KeyboardEvent): void {
      const options = Array.from(this.#options.values());
      const option = (event.target as HTMLElement).closest<HTMLElement>(".labelPicker__option");
      if (option === null) {
        return;
      }

      const index = options.indexOf(option);
      const labelId = Array.from(this.#options.keys())[index];

      switch (event.key) {
        case "ArrowDown":
          options[Math.min(index + 1, options.length - 1)].focus();
          break;

        case "ArrowUp":
          options[Math.max(index - 1, 0)].focus();
          break;

        case "Home":
          options[0].focus();
          break;

        case "End":
          options[options.length - 1].focus();
          break;

        case " ":
          this.#selectOption(labelId);
          break;

        case "Enter":
          if (this.multiple) {
            this.#close();
          } else {
            this.#selectOption(labelId);
          }
          break;

        case "Tab":
          this.#close();
          break;

        default:
          if (event.key.length !== 1 || event.altKey || event.ctrlKey || event.metaKey) {
            return;
          }

          this.#findByTypeahead(event.key, index)?.focus();
          break;
      }

      event.preventDefault();
    }

    #findByTypeahead(key: string, index: number): HTMLElement | undefined {
      this.#typeahead += key.toLowerCase();
      window.clearTimeout(this.#typeaheadTimer);
      this.#typeaheadTimer = window.setTimeout(() => {
        this.#typeahead = "";
      }, 500);

      // A single character cycles through the labels, more characters refine
      // the search starting at the current label.
      const options = Array.from(this.#options.values());
      const start = this.#typeahead.length === 1 ? index + 1 : index;
      const orderedOptions = [...options.slice(start), ...options.slice(0, start)];

      return orderedOptions.find((option) => option.textContent.trim().toLowerCase().startsWith(this.#typeahead));
    }

    #selectOption(labelId: number): void {
      if (this.multiple) {
        this.#toggleLabel(labelId);

        return;
      }

      if (this.value !== labelId) {
        this.value = labelId;
        this.#dispatchChange();
      }

      this.#close();
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

      for (const [labelId, option] of this.#options) {
        option.setAttribute("aria-selected", (labelId === value).toString());
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
      for (const [labelId, option] of this.#options) {
        const selected = values.includes(labelId);
        option.setAttribute("aria-selected", selected.toString());

        if (!selected && limitReached) {
          option.setAttribute("aria-disabled", "true");
        } else {
          option.removeAttribute("aria-disabled");
        }
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
