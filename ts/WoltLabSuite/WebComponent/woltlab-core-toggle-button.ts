/**
 * `<woltlab-core-toggle-button>` creates a toggle button.
 * Usage: `<woltlab-core-toggle-button name="foo" checked></woltlab-core-toggle-button>`
 *
 * The element is form-associated and submits its `value` (defaults to `1`)
 * when checked, behaving like a regular checkbox.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

{
  class WoltlabCoreToggleButtonElement extends HTMLElement {
    static formAssociated = true;

    static get observedAttributes() {
      return ["checked", "required"];
    }

    readonly #internals: ElementInternals;
    #defaultChecked: boolean | undefined = undefined;

    constructor() {
      super();

      this.#internals = this.attachInternals();

      const shadow = this.attachShadow({ mode: "open" });
      const style = document.createElement("style");
      style.textContent = `
        :host {
          display: inline-flex;
          vertical-align: middle;
        }

        :host(:disabled) {
          opacity: 0.54;
        }

        [part="track"] {
          position: relative;
          border-radius: 14px;
          width: 40px;
          height: 24px;
          cursor: pointer;
          background-color: var(--wcfToggleButtonBackground);
          transition: 0.4s;
        }

        :host(:disabled) [part="track"] {
          cursor: default;
        }

        :host([checked]) [part="track"] {
          background-color: var(--wcfToggleButtonBackgroundActive);
        }

        [part="slider"] {
          background-color: var(--wcfToggleButtonSliderBackground);
          border-radius: 50%;
          position: absolute;
          top: 2px;
          bottom: 2px;
          left: 0;
          align-items: center;
          display: flex;
          transition: 0.4s;
          transform: translateX(2px);
        }

        :host([checked]) [part="slider"] {
          background-color: var(--wcfToggleButtonSliderBackgroundActive);
          transform: translateX(18px);
        }

        ::slotted(fa-icon) {
          color: var(--wcfToggleButtonBackground);
        }

        :host([checked]) ::slotted(fa-icon) {
          color: var(--wcfToggleButtonBackgroundActive);
        }
      `;

      const track = document.createElement("div");
      track.setAttribute("part", "track");
      const slider = document.createElement("span");
      slider.setAttribute("part", "slider");
      track.append(slider);
      const iconSlot = document.createElement("slot");
      iconSlot.name = "icon";
      slider.append(iconSlot);
      shadow.append(style, track);

      // Clicks on an associated `<label>` are forwarded to the host element.
      this.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();

        if (!this.matches(":disabled")) {
          this.toggle();
        }
      });
      this.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();

          if (!this.matches(":disabled")) {
            this.toggle();
          }
        }
      });
    }

    connectedCallback() {
      if (this.#defaultChecked === undefined) {
        this.#defaultChecked = this.checked;
      }

      this.innerHTML = "";

      this.#renderIcon();

      this.setAttribute("role", "switch");
      this.setAttribute("aria-checked", this.checked.toString());
      this.#updateDisabledState(this.matches(":disabled"));
      this.#updateFormValue();
    }

    formDisabledCallback(disabled: boolean): void {
      this.#updateDisabledState(disabled);
    }

    formResetCallback(): void {
      this.checked = this.#defaultChecked === true;
    }

    #renderIcon(): void {
      const icon = document.createElement("fa-icon");
      icon.setIcon(this.checked ? "check" : "xmark");
      icon.slot = "icon";
      this.append(icon);
    }

    #updateDisabledState(disabled: boolean): void {
      if (disabled) {
        this.removeAttribute("tabindex");
        this.setAttribute("aria-disabled", "true");
      } else {
        this.setAttribute("tabindex", "0");
        this.removeAttribute("aria-disabled");
      }
    }

    #updateFormValue(): void {
      this.#internals.setFormValue(this.checked ? this.value : null);

      if (this.required && !this.checked) {
        this.#internals.setValidity(
          { valueMissing: true },
          window.WoltLabLanguage.getPhrase("wcf.global.form.error.empty"),
        );
      } else {
        this.#internals.setValidity({});
      }
    }

    get checked(): boolean {
      return this.hasAttribute("checked");
    }

    set checked(value: boolean) {
      this.toggleAttribute("checked", value);
      this.querySelector("fa-icon")?.setIcon(value ? "check" : "xmark");
    }

    get value(): string {
      return this.getAttribute("value") ?? "1";
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

    toggle(): void {
      this.checked = !this.checked;
      this.dispatchEvent(new Event("input", { bubbles: true }));
    }

    attributeChangedCallback(name: string) {
      switch (name) {
        case "checked":
          this.setAttribute("aria-checked", this.checked.toString());
          this.#updateFormValue();
          this.dispatchEvent(
            new CustomEvent("change", {
              detail: {
                checked: this.checked,
              },
            }),
          );
          break;

        case "required":
          this.#updateFormValue();
          break;
      }
    }
  }

  window.customElements.define("woltlab-core-toggle-button", WoltlabCoreToggleButtonElement);
}
