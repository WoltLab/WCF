"use strict";
(() => {
    let isFA6Free;
    function isFontAwesome6Free() {
        if (isFA6Free === undefined) {
            isFA6Free = true;
            const iconFont = window.getComputedStyle(document.documentElement).getPropertyValue("--fa-font-family");
            if (iconFont === "Font Awesome 6 Pro") {
                isFA6Free = false;
            }
        }
        return isFA6Free;
    }
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
    class FaIcon extends HTMLElement {
        connectedCallback() {
            this.validate();
            this.setIcon(this.name, this.solid);
            this.setAttribute("aria-hidden", "true");
        }
        validate() {
            if (this.size === 0) {
                throw new TypeError("Must provide an icon size.");
            }
            else if (!HeightMap.has(this.size)) {
                throw new TypeError("Must provide a valid icon size.");
            }
            if (this.name === "") {
                throw new TypeError("Must provide the name of the icon.");
            }
            else if (!this.isValidIconName(this.name)) {
                throw new TypeError(`The icon '${this.name}' is unknown or unsupported.`);
            }
        }
        setIcon(name, isSolid) {
            if (!this.isValidIconName(name)) {
                throw new TypeError(`The icon '${name}' is unknown or unsupported.`);
            }
            if (!this.isValidIconStyle(name, isSolid)) {
                throw new Error(`The icon '${name}' only supports the 'solid' style.`);
            }
            this.solid = isSolid;
            this.name = name;
            this.updateIcon();
        }
        isValidIconName(name) {
            return name !== null && window.getFontAwesome6IconMetadata(name) !== undefined;
        }
        isValidIconStyle(name, isSolid) {
            if (!isSolid && isFontAwesome6Free()) {
                const [, hasRegularVariant] = window.getFontAwesome6IconMetadata(name);
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
        getShadowRoot() {
            if (this.shadowRoot === null) {
                return this.attachShadow({ mode: "open" });
            }
            return this.shadowRoot;
        }
        updateIcon() {
            var _a;
            const root = this.getShadowRoot();
            (_a = root.childNodes[0]) === null || _a === void 0 ? void 0 : _a.remove();
            if (this.name === "spinner") {
                root.append(this.createSpinner());
            }
            else {
                const [codepoint] = window.getFontAwesome6IconMetadata(this.name);
                root.append(codepoint);
            }
        }
        createSpinner() {
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
        get solid() {
            return this.hasAttribute("solid");
        }
        set solid(solid) {
            if (solid) {
                this.setAttribute("solid", "");
            }
            else {
                this.removeAttribute("solid");
            }
        }
        get name() {
            return this.getAttribute("name") || "";
        }
        set name(name) {
            if (!this.isValidIconName(name)) {
                throw new Error(`Refused to set the unknown icon name '${name}'.`);
            }
            this.setAttribute("name", name);
            this.updateIcon();
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
        }
    }
    window.customElements.define("fa-icon", FaIcon);
})();
