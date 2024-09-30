/**
 * `<woltlab-core-notice>` creates user notices.
 * Usage: `<woltlab-core-notice type="info">Hello World!</woltlab-core-notice>`
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

{
  enum Type {
    Info = "info",
    Success = "success",
    Warning = "warning",
    Error = "error",
  }

  class WoltlabCoreNoticeElement extends HTMLElement {
    readonly #iconElement: FaIcon;
    readonly #contentWrapper: HTMLElement;

    constructor() {
      super();

      const shadow = this.attachShadow({ mode: "open" });

      this.#iconElement = this.querySelector<FaIcon>("fa-icon") ?? document.createElement("fa-icon");
      this.#iconElement.size = 24;
      this.#iconElement.slot = "icon";

      const style = document.createElement("style");
      style.textContent = `
        :host {
          align-items: center;
          display: grid;
          gap: 5px;
          grid-template-columns: max-content auto;
        }
      `;
      this.#contentWrapper = document.createElement("div");
      this.#contentWrapper.classList.add("content");
      const contentSlot = document.createElement("slot");
      this.#contentWrapper.append(contentSlot);

      const iconSlot = document.createElement("slot");
      iconSlot.name = "icon";

      shadow.append(style, iconSlot, this.#contentWrapper);
    }

    connectedCallback() {
      if (!this.contains(this.#iconElement)) {
        this.append(this.#iconElement);
      }

      this.#updateType();
    }

    #updateType(): void {
      void window.customElements.whenDefined("fa-icon").then(() => {
        this.#iconElement.setIcon(this.icon, true);
      });
      this.#contentWrapper.setAttribute("role", this.type === Type.Error ? "alert" : "status");
      this.classList.remove(...Object.values(Type));
      this.classList.add(this.type);
    }

    get type(): Type {
      if (!this.hasAttribute("type")) {
        throw new Error("missing attribute 'type'");
      }
      const type = this.getAttribute("type")!;
      if (!Object.values(Type).includes(type as Type)) {
        throw new Error(`invalid value '${type}' for attribute 'type' given`);
      }

      return type as Type;
    }

    set type(type: Type) {
      this.setAttribute("type", type);

      if (this.#iconElement === undefined) {
        return;
      }

      this.#updateType();
    }

    get icon(): string {
      switch (this.type) {
        case Type.Success:
          return "circle-check";
        case Type.Warning:
          return "triangle-exclamation";
        case Type.Error:
          return "circle-exclamation";
        case Type.Info:
          return "circle-info";
      }
    }
  }

  window.customElements.define("woltlab-core-notice", WoltlabCoreNoticeElement);
}
