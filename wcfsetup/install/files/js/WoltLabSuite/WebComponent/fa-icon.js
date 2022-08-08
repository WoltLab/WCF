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
    class FaIcon extends HTMLElement {
        connectedCallback() {
            this.validate();
            const root = this.prepareRoot();
            if (this.brand) {
                const slot = document.createElement("slot");
                slot.name = "svg";
                root.append(slot);
            }
            else {
                const [codepoint] = window.getFontAwesome6Styles(this.name);
                root.append(codepoint);
                // TODO: Add style
            }
        }
        validate() {
            if (this.size === 0) {
                throw new TypeError("Must provide an icon size.");
            }
            else if (!HeightMap.has(this.size)) {
                throw new TypeError("Must provide a valid icon size.");
            }
            if (this.brand) {
                if (this.name !== null) {
                    throw new TypeError("Cannot provide a name for brand icons.");
                }
            }
            else {
                if (this.name === null) {
                    throw new TypeError("Must provide the name of the icon.");
                }
                const styles = window.getFontAwesome6Styles(this.name);
                if (styles === undefined) {
                    throw new TypeError(`The icon '${name}' is unknown or unsupported.`);
                }
            }
        }
        prepareRoot() {
            const root = this.attachShadow({ mode: "closed" });
            if (this.brand) {
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
            }
            return root;
        }
        get brand() {
            return this.hasAttribute("brand");
        }
        get name() {
            return this.getAttribute("name");
        }
        get size() {
            const size = this.getAttribute("size");
            if (size === null) {
                return 0;
            }
            return parseInt(size);
        }
    }
    window.customElements.define("fa-icon", FaIcon);
})();
