export class WoltlabCoreMenuSeparatorElement extends HTMLElement {
  connectedCallback() {
    this.setAttribute("role", "separator");
  }
}

export default WoltlabCoreMenuSeparatorElement;

window.customElements.define("woltlab-core-menu-separator", WoltlabCoreMenuSeparatorElement);
