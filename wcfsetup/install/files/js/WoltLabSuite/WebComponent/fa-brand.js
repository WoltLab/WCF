"use strict";
(() => {
    const HeightMap = new Map([
        [16, 14],
        [24, 18],
        [32, 28],
        [48, 42],
        [64, 56],
        [96, 84],
        [128, 112],
        [144, 130],
    ]);
    class FaBrand extends HTMLElement {
        connectedCallback() {
            this.validate();
            const root = this.prepareRoot();
            const slot = document.createElement("slot");
            slot.name = "svg";
            root.append(slot);
        }
        validate() {
            if (this.size === 0) {
                throw new TypeError("Must provide an icon size.");
            }
            else if (!HeightMap.has(this.size)) {
                throw new TypeError("Must provide a valid icon size.");
            }
        }
        prepareRoot() {
            const root = this.attachShadow({ mode: "open" });
            const iconHeight = HeightMap.get(this.size);
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
        get size() {
            const size = this.getAttribute("size");
            if (size === null) {
                return 0;
            }
            return parseInt(size);
        }
    }
    window.customElements.define("fa-brand", FaBrand);
})();
