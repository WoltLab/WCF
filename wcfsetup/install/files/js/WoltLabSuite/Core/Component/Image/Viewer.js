var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
define(["require", "exports", "@fancyapps/ui"], function (require, exports, ui_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.createFancybox = createFancybox;
    exports.getLocalization = getLocalization;
    const LOCALES = ["cs", "de", "en", "es", "fr", "it", "lv", "pl", "sk"];
    function setup() {
        void getDefaultConfig().then((config) => {
            ui_1.Fancybox.bind("[data-fancybox]", config);
        });
    }
    async function createFancybox(userSlides) {
        return new ui_1.Fancybox(userSlides, await getDefaultConfig());
    }
    async function getDefaultConfig() {
        return {
            l10n: await getLocalization(),
            Html: {
                videoAutoplay: false,
            },
        };
    }
    async function getLocalization() {
        let locale = document.documentElement.lang;
        if (!LOCALES.includes(locale)) {
            locale = "en";
        }
        return (await new Promise((resolve_1, reject_1) => { require([`@fancyapps/ui/l10n/${locale}`], resolve_1, reject_1); }).then(__importStar))[locale];
    }
});
