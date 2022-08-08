const Sizes = [16, 24, 32, 48, 64, 96, 128, 144];
const HeightMap = new Map<number, number>([
  [16, 14],
  [24, 18],
  [32, 28],
  [48, 42],
  [64, 56],
  [96, 84],
  [128, 112],
  [144, 130],
]);

class FaIcon extends HTMLElement {
  constructor() {
    performance.mark("icon-init-start");
    super();

    performance.mark("icon-init-end");
    performance.measure("iconInit", "icon-init-start", "icon-init-end");
  }

  connectedCallback() {
    performance.mark("icon-start");

    this.validate();

    const root = this.prepareRoot();
    if (this.brand) {
      const slot = document.createElement("slot");
      slot.name = "svg";
      root.append(slot);
    } else {
      root.append("\uf0f3");
    }

    performance.mark("icon-end");
    performance.measure("iconRendered", "icon-start", "icon-end");
  }

  private validate(): void {
    if (this.size === 0) {
      throw new TypeError("Must provide an icon size.");
    } else if (!Sizes.includes(this.size)) {
      throw new TypeError("Must provide a valid icon size.");
    }

    if (this.brand) {
      if (this.name !== null) {
        throw new TypeError("Cannot provide a name for brand icons.");
      }
    } else {
      if (this.name === null) {
        throw new TypeError("Must provide the name of the icon.");
      }
    }
  }

  private prepareRoot(): ShadowRoot {
    const size = this.size;
    const iconHeight = HeightMap.get(size)!;

    const root = this.attachShadow({ mode: "closed" });
    const style = document.createElement("style");
    style.textContent = `
      ::slotted(svg) {
        fill: currentColor;
        height: ${iconHeight}px;
        shape-rendering: geometricprecision;
      }
    `;
    root.append(style);

    return root;
  }

  get brand(): boolean {
    return this.hasAttribute("brand");
  }

  get name(): string | null {
    return this.getAttribute("name");
  }

  get size(): number {
    const size = this.getAttribute("size");
    if (size === null) {
      return 0;
    }

    return parseInt(size);
  }
}

window.customElements.define("fa-icon", FaIcon);
