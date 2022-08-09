(() => {
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
    connectedCallback() {
      this.validate();

      const root = this.attachShadow({ mode: "open" });
      const [codepoint] = window.getFontAwesome6IconMetadata(this.name)!;
      root.append(codepoint);
    }

    private validate(): void {
      if (this.size === 0) {
        throw new TypeError("Must provide an icon size.");
      } else if (!HeightMap.has(this.size)) {
        throw new TypeError("Must provide a valid icon size.");
      }

      if (this.name === "") {
        throw new TypeError("Must provide the name of the icon.");
      }

      const styles = window.getFontAwesome6IconMetadata(this.name);
      if (styles === undefined) {
        throw new TypeError(`The icon '${this.name}' is unknown or unsupported.`);
      }
    }

    setIcon(name: string, type: "regular" | "solid"): void {
      const metadata = window.getFontAwesome6IconMetadata(name);
      if (metadata === undefined) {
        throw new TypeError(`The icon '${name}' is unknown or unsupported.`);
      }

      const [codepoint, styles] = metadata;
      if (!styles.includes(type)) {
        throw new Error(`The icon '${name}' does not support the style '${type}'.`);
      }

      this.solid = type === "solid";
      this.regular = type === "regular";
      this.name = name;

      const root = this.shadowRoot!;
      root.childNodes[0]?.remove();

      root.append(codepoint);
    }

    get solid(): boolean {
      return this.hasAttribute("solid");
    }

    set solid(solid: boolean) {
      if (solid) {
        this.setAttribute("solid", "");
      } else {
        this.removeAttribute("solid");
      }
    }

    get regular(): boolean {
      return this.hasAttribute("regular");
    }

    set regular(regular: boolean) {
      if (regular) {
        this.setAttribute("regular", "");
      } else {
        this.removeAttribute("regular");
      }
    }

    get name(): string {
      return this.getAttribute("name") || "";
    }

    set name(name: string) {
      this.setAttribute("name", name);
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
})();
