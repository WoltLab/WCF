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

      const emptyLabel = `<span class="badge label">${window.WoltLabLanguage.getPhrase("wcf.label.none")}</span>`;

      this.#button.type = "button";
      this.#button.classList.add("dropdownToggle");
      this.#button.innerHTML = emptyLabel;
      this.#button.addEventListener("click", (event) => {
        event.preventDefault();

        const evt = new CustomEvent("showPicker");
        this.dispatchEvent(evt);
      });

      this.append(this.#button);

      const dropdownMenu = document.createElement("ul");
      dropdownMenu.classList.add("dropdownMenu");
      for (const [labelId, html] of this.#labels) {
        dropdownMenu.append(this.#createLabelItem(labelId, html));
      }

      if (!this.required) {
        const divider = document.createElement("li");
        divider.classList.add("dropdownDivider");

        dropdownMenu.append(divider, this.#createLabelItem(0, emptyLabel));
      }

      this.append(dropdownMenu);

      this.classList.add("dropdown");

      if (this.closest("form") !== null) {
        if (this.#formValue === undefined) {
          this.#formValue = document.createElement("input");
          this.#formValue.type = "hidden";
          this.#formValue.name = "labelIDs[]";
          this.append(this.#formValue);
        }

        this.#formValue.value = (this.selected || 0).toString();
      } else {
        this.#formValue?.remove();
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

      this.#button.innerHTML =
        this.#labels.get(selected) ||
        `<span class="badge label">${window.WoltLabLanguage.getPhrase("wcf.label.none")}</span>`;
      if (this.#formValue !== undefined) {
        this.#formValue.value = selected.toString();
      }
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

    get labels(): Map<number, string> {
      return new Map(this.#labels);
    }
  }

  window.customElements.define("woltlab-core-label-picker", WoltlabCoreLabelPickerElement);
}
