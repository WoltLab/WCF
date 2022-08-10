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
        constructor() {
            super(...arguments);
            this.root = undefined;
            this.svgStyle = document.createElement("style");
        }
        connectedCallback() {
            this.validate();
            const root = this.getRoot();
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
        getRoot() {
            if (this.root === undefined) {
                this.root = this.attachShadow({ mode: "open" });
                this.updateRenderSize();
                this.root.append(this.svgStyle);
            }
            return this.root;
        }
        updateRenderSize() {
            const renderSize = HeightMap.get(this.size);
            this.svgStyle.textContent = `
        ::slotted(svg) {
          fill: currentColor;
          height: ${renderSize}px;
          shape-rendering: geometricprecision;
        }
      `;
        }
        get size() {
            const size = this.getAttribute("size");
            if (size === null) {
                return 0;
            }
            return parseInt(size);
        }
        set size(size) {
            if (!HeightMap.has(size)) {
                throw new Error(`Refused to set the invalid icon size '${size}'.`);
            }
            this.setAttribute("size", size.toString());
            this.updateRenderSize();
        }
    }
    window.customElements.define("fa-brand", FaBrand);
})();
