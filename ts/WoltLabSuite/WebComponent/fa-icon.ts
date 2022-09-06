(() => {
  let isFA6Free: boolean;
  function isFontAwesome6Free(): boolean {
    if (isFA6Free === undefined) {
      isFA6Free = true;

      const iconFont = window.getComputedStyle(document.documentElement).getPropertyValue("--fa-font-family");
      if (iconFont === "Font Awesome 6 Pro") {
        isFA6Free = false;
      }
    }

    return isFA6Free;
  }

  type IconSize = number;
  type RenderSize = number;
  const HeightMap = new Map<IconSize, RenderSize>([
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
      if (!this.hasAttribute("size")) {
        this.setAttribute("size", "16");
      }

      this.validate();

      this.setIcon(this.name, this.solid);

      this.setAttribute("aria-hidden", "true");
    }

    private validate(): void {
      if (!HeightMap.has(this.size)) {
        throw new TypeError("Must provide a valid icon size.");
      }

      if (this.name === "") {
        throw new TypeError("Must provide the name of the icon.");
      } else if (!this.isValidIconName(this.name)) {
        throw new TypeError(`The icon '${this.name}' is unknown or unsupported.`);
      }
    }

    setIcon(name: string, forceSolid = false): void {
      if (!this.isValidIconName(name)) {
        throw new TypeError(`The icon '${name}' is unknown or unsupported.`);
      }

      if (!forceSolid && !this.hasNonSolidStyle(name)) {
        forceSolid = true;
      }

      // Avoid rendering the icon again if this is a no-op.
      if (name === this.name && forceSolid === this.solid) {
        // This method is called from `connectedCallback` too, check for
        // the existence of the shadow root and only return early for
        // requests on runtime.
        if (this.shadowRoot !== null) {
          return;
        }
      }

      if (forceSolid) {
        this.setAttribute("solid", "");
      } else {
        this.removeAttribute("solid");
      }

      this.setAttribute("name", name);

      this.updateIcon();
    }

    private isValidIconName(name: string | null): boolean {
      return name !== null && window.getFontAwesome6IconMetadata(name) !== undefined;
    }

    private hasNonSolidStyle(name: string): boolean {
      if (isFontAwesome6Free()) {
        const [, hasRegularVariant] = window.getFontAwesome6IconMetadata(name)!;
        if (!hasRegularVariant) {
          // Font Awesome 6 Free only includes solid icons with the
          // the exception to some special icons that use the weight
          // to differentiate two related icons. One such example is
          // the `bell` icon that comes in `solid` and `regular` flavor.
          return false;
        }
      }

      return true;
    }

    private getShadowRoot(): ShadowRoot {
      if (this.shadowRoot === null) {
        return this.attachShadow({ mode: "open" });
      }

      return this.shadowRoot;
    }

    private updateIcon(): void {
      const root = this.getShadowRoot();
      root.childNodes[0]?.remove();

      if (this.name === "spinner") {
        root.append(this.createSpinner());
      } else {
        const [codepoint] = window.getFontAwesome6IconMetadata(this.name)!;
        root.append(codepoint);
      }
    }

    private createSpinner(): HTMLElement {
      // Based upon the work of Fabio Ottaviani
      // https://codepen.io/supah/pen/BjYLdW
      const container = document.createElement("div");
      container.innerHTML = `
        <svg class="spinner" viewBox="0 0 50 50">
          <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
        </svg>
      `;

      const style = document.createElement("style");
      style.textContent = `
        div,
        svg {
          height: var(--font-size);
          width: var(--font-size);
        }

        .spinner {
          animation: rotate 2s linear infinite;
        }
          
        .path {
          animation: dash 1.5s ease-in-out infinite;
          stroke: currentColor;
          stroke-linecap: round;
        }

        @keyframes rotate {
          100% {
            transform: rotate(360deg);
          }
        }

        @keyframes dash {
          0% {
            stroke-dasharray: 1, 150;
            stroke-dashoffset: 0;
          }
          50% {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: -35;
          }
          100% {
            stroke-dasharray: 90, 150;
            stroke-dashoffset: -124;
          }
        }
      `;

      container.append(style);

      return container;
    }

    get solid(): boolean {
      return this.hasAttribute("solid");
    }

    get name(): string {
      return this.getAttribute("name") || "";
    }

    get size(): IconSize {
      const size = this.getAttribute("size");
      if (size === null) {
        return 0;
      }

      return parseInt(size);
    }

    set size(size: number) {
      if (!HeightMap.has(size)) {
        throw new Error(`Refused to set the invalid icon size '${size}'.`);
      }

      this.setAttribute("size", size.toString());
    }
  }

  window.customElements.define("fa-icon", FaIcon);
})();
