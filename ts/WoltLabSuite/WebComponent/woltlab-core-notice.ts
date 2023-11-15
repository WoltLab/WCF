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
    connectedCallback() {
      const shadow = this.attachShadow({ mode: "open" });

      const icon = document.createElement("fa-icon");
      icon.size = 24;
      icon.setIcon(this.icon, true);
      icon.slot = "icon";
      this.append(icon);

      const style = document.createElement("style");
      style.textContent = `
        :host {
          display: flex;
          gap: 5px;
          align-items: center;
        }

        .content {
          flex: 1 auto;
        }
      `;
      const contentWrapper = document.createElement("div");
      contentWrapper.classList.add("content");
      const contentSlot = document.createElement("slot");
      contentWrapper.append(contentSlot);

      const iconSlot = document.createElement("slot");
      iconSlot.name = "icon";

      shadow.append(style, iconSlot, contentWrapper);

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
