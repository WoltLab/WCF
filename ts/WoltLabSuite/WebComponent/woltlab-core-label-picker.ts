/**
 * The `<woltlab-core-label-picker>` provides an interactive widget to select a
 * label out of a label group.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

{
  class WoltlabCoreLabelPickerElement extends HTMLElement {
    readonly #button: HTMLButtonElement;
    #formValue: HTMLInputElement | undefined;
    #labels = new Map<number, string>();

    constructor() {
      super();

      this.#button = document.createElement("button");
    }

    connectedCallback() {
      if (this.hasAttribute("labels")) {
        this.#labels = new Map(JSON.parse(this.getAttribute("labels")!));
        this.removeAttribute("labels");
      }

      if (this.#labels.size === 0) {
        throw new Error("Expected a non empty list of labels.");
      }

      const emptyLabel = this.#getHtmlForNoneLabel();

      this.#button.type = "button";
      this.#button.classList.add("dropdownToggle");
      this.#button.innerHTML = emptyLabel;
      this.#button.addEventListener("click", (event) => {
        event.preventDefault();

        const evt = new CustomEvent("showPicker");
        this.dispatchEvent(evt);
      });

      // Moving the ID to the button allows clicks on the label to be forwarded.
      this.#button.id = this.id;
      this.removeAttribute("id");

      this.append(this.#button);

      const scrollableDropdownMenu = document.createElement("ul");
      scrollableDropdownMenu.classList.add("scrollableDropdownMenu");
      for (const [labelId, html] of this.#labels) {
        scrollableDropdownMenu.append(this.#createLabelItem(labelId, html));
      }

      if (!this.required) {
        const divider = document.createElement("li");
        divider.classList.add("dropdownDivider");

        scrollableDropdownMenu.append(divider);

        if (this.invertible) {
          const invertSelection = this.#createLabelItem(-1, this.#getHtmlForInvertedSelection());
          scrollableDropdownMenu.append(invertSelection);
        }

        scrollableDropdownMenu.append(this.#createLabelItem(0, emptyLabel));
      }

      const dropdownMenu = document.createElement("ul");
      dropdownMenu.classList.add("dropdownMenu");
      dropdownMenu.append(scrollableDropdownMenu);

      this.append(dropdownMenu);

      this.classList.add("dropdown");

      if (this.closest("form") !== null) {
        if (this.#formValue === undefined) {
          this.#formValue = document.createElement("input");
          this.#formValue.type = "hidden";
          this.#formValue.name = `${this.name}[${this.dataset.groupId}]`;
          this.append(this.#formValue);
        }

        this.#formValue.value = (this.selected || 0).toString();
      } else {
        this.#formValue?.remove();
      }

      if (this.selected) {
        this.#updateValue(this.selected);
      }
    }

    #createLabelItem(labelId: number, html: string): HTMLLIElement {
      const button = document.createElement("button");
      button.type = "button";
      button.dataset.labelId = labelId.toString();
      button.innerHTML = html;
      button.addEventListener("click", () => {
        this.selected = labelId;
      });

      const listItem = document.createElement("li");
      listItem.append(button);

      return listItem;
    }

    set selected(selected: number) {
      this.setAttribute("selected", selected.toString());

      this.#updateValue(selected);
    }

    get selected(): number | undefined {
      const selected = parseInt(this.getAttribute("selected")!);
      if (Number.isNaN(selected)) {
        return undefined;
      }

      return selected;
    }

    set disabled(disabled: boolean) {
      if (disabled) {
        this.setAttribute("disabled", "");
      } else {
        this.removeAttribute("disabled");
      }

      this.#button.disabled = disabled;
      if (this.#formValue) {
        this.#formValue.disabled = disabled;
      }
    }

    get disabled(): boolean {
      return this.hasAttribute("disabled");
    }

    set required(required: boolean) {
      if (required) {
        this.setAttribute("required", "");
      } else {
        this.removeAttribute("required");
      }
    }

    get required(): boolean {
      return this.hasAttribute("required");
    }

    get invertible(): boolean {
      return this.hasAttribute("invertible");
    }

    get name(): string {
      return this.getAttribute("name")!;
    }

    #getHtmlForNoneLabel(): string {
      return `<span class="badge label">${window.WoltLabLanguage.getPhrase("wcf.label.none")}</span>`;
    }

    #getHtmlForInvertedSelection(): string {
      return `<span class="badge label">${window.WoltLabLanguage.getPhrase("wcf.label.withoutSelection")}</span>`;
    }

    #updateValue(labelId: number): void {
      let html = "";
      if (this.#labels.has(labelId)) {
        html = this.#labels.get(labelId)!;
      } else if (labelId === -1 && this.invertible) {
        html = this.#getHtmlForInvertedSelection();
      }

      this.#button.innerHTML = html || this.#getHtmlForNoneLabel();
      if (this.#formValue !== undefined) {
        this.#formValue.value = labelId.toString();
      }
    }
  }

  window.customElements.define("woltlab-core-label-picker", WoltlabCoreLabelPickerElement);
}
