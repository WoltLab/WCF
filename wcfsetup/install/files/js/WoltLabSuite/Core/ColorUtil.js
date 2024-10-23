/**
 * Helper functions to convert between different color formats.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.hslToRgb = hslToRgb;
    exports.hsvToRgb = hsvToRgb;
    exports.rgbToHsl = rgbToHsl;
    exports.rgbToHsv = rgbToHsv;
    exports.hexToRgb = hexToRgb;
    exports.rgbToHex = rgbToHex;
    exports.rgbaToHex = rgbaToHex;
    exports.rgbaToString = rgbaToString;
    exports.isValidColor = isValidColor;
    exports.stringToRgba = stringToRgba;
    /**
     * Converts a HSL color into RGB.
     *
     * @see https://www.rapidtables.com/convert/color/hsl-to-rgb.html
     */
    function hslToRgb(hue, saturation, lightness) {
        if (hue > 359) {
            throw new TypeError("Hue cannot be larger than 359°");
        }
        saturation /= 100;
        lightness /= 100;
        const C = (1 - Math.abs(2 * lightness - 1)) * saturation;
        const X = C * (1 - Math.abs(((hue / 60) % 2) - 1));
        const m = lightness - C / 2;
        const [R, G, B] = ((0 <= hue && hue < 60 && [C, X, 0]) ||
            (60 <= hue && hue < 120 && [X, C, 0]) ||
            (120 <= hue && hue < 180 && [0, C, X]) ||
            (180 <= hue && hue < 240 && [0, X, C]) ||
            (240 <= hue && hue < 300 && [X, 0, C]) ||
            (300 <= hue && hue < 360 && [C, 0, X]));
        return {
            r: Math.round((R + m) * 255),
            g: Math.round((G + m) * 255),
            b: Math.round((B + m) * 255),
        };
    }
    /**
     * Converts a HSV color into RGB.
     *
     * @see  https://secure.wikimedia.org/wikipedia/de/wiki/HSV-Farbraum#Transformation_von_RGB_und_HSV
     */
    function hsvToRgb(h, s, v) {
        const rgb = { r: 0, g: 0, b: 0 };
        const h2 = Math.floor(h / 60);
        const f = h / 60 - h2;
        s /= 100;
        v /= 100;
        const p = v * (1 - s);
        const q = v * (1 - s * f);
        const t = v * (1 - s * (1 - f));
        if (s == 0) {
            rgb.r = rgb.g = rgb.b = v;
        }
        else {
            switch (h2) {
                case 1:
                    rgb.r = q;
                    rgb.g = v;
                    rgb.b = p;
                    break;
                case 2:
                    rgb.r = p;
                    rgb.g = v;
                    rgb.b = t;
                    break;
                case 3:
                    rgb.r = p;
                    rgb.g = q;
                    rgb.b = v;
                    break;
                case 4:
                    rgb.r = t;
                    rgb.g = p;
                    rgb.b = v;
                    break;
                case 5:
                    rgb.r = v;
                    rgb.g = p;
                    rgb.b = q;
                    break;
                case 0:
                case 6:
                    rgb.r = v;
                    rgb.g = t;
                    rgb.b = p;
                    break;
            }
        }
        return {
            r: Math.round(rgb.r * 255),
            g: Math.round(rgb.g * 255),
            b: Math.round(rgb.b * 255),
        };
    }
    /**
     * Converts a RGB color into HSL.
     *
     * @see https://www.rapidtables.com/convert/color/rgb-to-hsl.html
     */
    function rgbToHsl(r, g, b) {
        let h, s;
        r /= 255;
        g /= 255;
        b /= 255;
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const diff = max - min;
        h = 0;
        if (max !== min) {
            switch (max) {
                case r:
                    h = 60 * ((g - b) / diff);
                    break;
                case g:
                    h = 60 * (2 + (b - r) / diff);
                    break;
                case b:
                    h = 60 * (4 + (r - g) / diff);
                    break;
            }
            if (h < 0) {
                h += 360;
            }
        }
        const l = (max + min) / 2;
        if (diff === 0) {
            s = 0;
        }
        else {
            s = diff / (1 - Math.abs(2 * l - 1));
        }
        return {
            h: Math.round(h),
            s: Math.round(s * 100),
            l: Math.round(l * 100),
        };
    }
    /**
     * Converts a RGB color into HSV.
     *
     * @see https://www.rapidtables.com/convert/color/rgb-to-hsv.html
     */
    function rgbToHsv(r, g, b) {
        let h, s;
        r /= 255;
        g /= 255;
        b /= 255;
        const max = Math.max(Math.max(r, g), b);
        const min = Math.min(Math.min(r, g), b);
        const diff = max - min;
        h = 0;
        if (max !== min) {
            switch (max) {
                case r:
                    h = 60 * ((g - b) / diff);
                    break;
                case g:
                    h = 60 * (2 + (b - r) / diff);
                    break;
                case b:
                    h = 60 * (4 + (r - g) / diff);
                    break;
            }
            if (h < 0) {
                h += 360;
            }
        }
        if (max === 0) {
            s = 0;
        }
        else {
            s = diff / max;
        }
        return {
            h: Math.round(h),
            s: Math.round(s * 100),
            v: Math.round(max * 100),
        };
    }
    /**
     * Converts HEX into RGB.
     */
    function hexToRgb(hex) {
        if (/^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(hex)) {
            // only convert #abc and #abcdef
            const parts = hex.split("");
            // drop the hashtag
            if (parts[0] === "#") {
                parts.shift();
            }
            // parse shorthand #xyz
            if (parts.length === 3) {
                return {
                    r: parseInt(parts[0] + "" + parts[0], 16),
                    g: parseInt(parts[1] + "" + parts[1], 16),
                    b: parseInt(parts[2] + "" + parts[2], 16),
                };
            }
            else {
                return {
                    r: parseInt(parts[0] + "" + parts[1], 16),
                    g: parseInt(parts[2] + "" + parts[3], 16),
                    b: parseInt(parts[4] + "" + parts[5], 16),
                };
            }
        }
        return Number.NaN;
    }
    /**
     * @since 5.5
     */
    function rgbComponentToHex(component) {
        if (component < 0 || component > 255) {
            throw new Error(`Invalid RGB component value '${component}' given.`);
        }
        return component.toString(16).padStart(2, "0").toUpperCase();
    }
    function rgbToHex(r, g, b) {
        if (g === undefined) {
            const match = /^rgba?\((\d+), ?(\d+), ?(\d+)(?:, ?[0-9.]+)?\)$/.exec(r.toString());
            if (match) {
                r = +match[1];
                g = +match[2];
                b = +match[3];
            }
            else {
                throw new Error("Invalid RGB data given.");
            }
        }
        return rgbComponentToHex(r) + rgbComponentToHex(g) + rgbComponentToHex(b);
    }
    /**
     * @since 5.5
     */
    function alphaToHex(alpha) {
        if (alpha < 0 || alpha > 1) {
            throw new Error(`Invalid alpha value '${alpha}' given.`);
        }
        return Math.round(alpha * 255)
            .toString(16)
            .padStart(2, "0")
            .toUpperCase();
    }
    function rgbaToHex(r, g, b, a) {
        if (g === undefined) {
            const rgba = r;
            return rgbToHex(rgba.r, rgba.g, rgba.b) + alphaToHex(rgba.a);
        }
        return rgbToHex(r, g, b) + alphaToHex(a);
    }
    /**
     * Returns the textual representation of a RGBA value.
     *
     * @since 5.5
     */
    function rgbaToString(rgba) {
        return `rgba(${rgba.r}, ${rgba.g}, ${rgba.b}, ${rgba.a})`;
    }
    /**
     * @since 5.5
     */
    function getColorChecker() {
        let colorChecker = document.getElementById("jsColorUtilColorChecker");
        if (colorChecker === null) {
            colorChecker = document.createElement("span");
            colorChecker.id = "jsColorUtilColorChecker";
            document.body.appendChild(colorChecker);
        }
        return colorChecker;
    }
    /**
     * Returns `true` if the given string is a valid CSS color argument.
     *
     * @since 5.5
     */
    function isValidColor(color) {
        const colorChecker = getColorChecker();
        // We let the browser handle the validation of the color by
        // 1. ensuring that the `color` style property of the test element is empty,
        // 2. setting the value of the `color` style property to the given value,
        // 3. checking that the value of the `color` style property is not empty afterwards.
        //    If the entered value is valid, the `color` style property will not empty (though it also
        //    does not have to match the entered value due to normalization by the browser)
        colorChecker.style.color = "";
        colorChecker.style.color = color;
        return colorChecker.style.color !== "";
    }
    /**
     * Converts the given CSS color value to an RGBA value.
     *
     * @since 5.5
     */
    function stringToRgba(color) {
        if (!isValidColor(color)) {
            throw new Error(`Given string '${color}' is no valid color.`);
        }
        const colorChecker = getColorChecker();
        colorChecker.style.color = color;
        const computedColor = window.getComputedStyle(colorChecker).color;
        const rgbMatch = /^rgb\((\d+), ?(\d+), ?(\d+)\)$/.exec(computedColor);
        if (rgbMatch) {
            return {
                r: +rgbMatch[1],
                g: +rgbMatch[2],
                b: +rgbMatch[3],
                a: 1,
            };
        }
        else {
            const rgbaMatch = /^rgba\((\d+), ?(\d+), ?(\d+), ?([0-9.]+)\)$/.exec(computedColor);
            if (rgbaMatch) {
                return {
                    r: +rgbaMatch[1],
                    g: +rgbaMatch[2],
                    b: +rgbaMatch[3],
                    a: +rgbaMatch[4],
                };
            }
        }
        throw new Error(`Cannot process color '${color}'.`);
    }
    // WCF.ColorPicker compatibility (color format conversion)
    window.__wcf_bc_colorUtil = {
        hexToRgb,
        hslToRgb,
        hsvToRgb,
        isValidColor,
        rgbaToHex,
        rgbaToString,
        rgbToHex,
        rgbToHsv,
        rgbToHsl,
        stringToRgba,
    };
});
