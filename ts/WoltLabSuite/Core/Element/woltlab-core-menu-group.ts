export class WoltlabCoreMenuGroupElement extends HTMLElement {
  connectedCallback() {
    this.setAttribute("role", "group");

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

export default WoltlabCoreMenuGroupElement;

window.customElements.define("woltlab-core-menu-group", WoltlabCoreMenuGroupElement);
