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
                if (this.name !== "") {
                    throw new TypeError("Cannot provide a name for brand icons.");
                }
            }
            else {
                if (this.name === "") {
                    throw new TypeError("Must provide the name of the icon.");
                }
                const styles = window.getFontAwesome6Styles(this.name);
                if (styles === undefined) {
                    throw new TypeError(`The icon '${this.name}' is unknown or unsupported.`);
                }
            }
        }
        prepareRoot() {
            const root = this.attachShadow({ mode: "open" });
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
        setIcon(name, type) {
            var _a;
            if (this.brand) {
                throw new Error("Cannot change the icon of a brand icon.");
            }
            const metadata = window.getFontAwesome6Styles(name);
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
            const root = this.shadowRoot;
            (_a = root.childNodes[0]) === null || _a === void 0 ? void 0 : _a.remove();
            root.append(codepoint);
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
        get regular() {
            return this.hasAttribute("regular");
        }
        set regular(regular) {
            if (regular) {
                this.setAttribute("regular", "");
            }
            else {
                this.removeAttribute("regular");
            }
        }
        get brand() {
            return this.hasAttribute("brand");
        }
        get name() {
            return this.getAttribute("name") || "";
        }
        set name(name) {
            this.setAttribute("name", name);
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
