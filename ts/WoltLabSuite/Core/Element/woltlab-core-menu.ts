export class WoltlabCoreMenuElement extends HTMLElement {
  connectedCallback() {
    this.setAttribute("role", "menu");

    this.label = this.getAttribute("label")!;
  }

  get label(): string {
    return this.getAttribute("label")!;
  }

  set label(label: string) {
    this.setAttribute("label", label);
    this.setAttribute("aria-label", label);
  }
}

export default WoltlabCoreMenuElement;

window.customElements.define("woltlab-core-menu", WoltlabCoreMenuElement);
