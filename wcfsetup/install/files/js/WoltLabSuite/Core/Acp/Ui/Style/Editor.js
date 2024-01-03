/**
 * Provides the style editor.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Dom/Util", "../../../Event/Handler", "../../../Ui/Screen", "./DarkMode"], function (require, exports, tslib_1, Ajax, Core, Util_1, EventHandler, UiScreen, DarkMode_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showVisualEditor = exports.hideVisualEditor = exports.setup = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiScreen = tslib_1.__importStar(UiScreen);
    const _stylePreviewRegions = new Map();
    let _stylePreviewRegionMarker;
    const _stylePreviewWindow = document.getElementById("spWindow");
    let _isVisible = true;
    let _isSmartphone = false;
    let _updateRegionMarker;
    /**
     * Handles the switch between static and fluid layout.
     */
    function handleLayoutWidth() {
        const useFluidLayout = document.getElementById("useFluidLayout");
        const fluidLayoutMinWidth = document.getElementById("fluidLayoutMinWidth");
        const fluidLayoutMaxWidth = document.getElementById("fluidLayoutMaxWidth");
        const fixedLayoutVariables = document.getElementById("fixedLayoutVariables");
        function change() {
            if (useFluidLayout.checked) {
                Util_1.default.show(fluidLayoutMinWidth);
                Util_1.default.show(fluidLayoutMaxWidth);
                Util_1.default.hide(fixedLayoutVariables);
            }
            else {
                Util_1.default.hide(fluidLayoutMinWidth);
                Util_1.default.hide(fluidLayoutMaxWidth);
                Util_1.default.show(fixedLayoutVariables);
            }
        }
        useFluidLayout.addEventListener("change", change);
        change();
    }
    /**
     * Handles SCSS input fields.
     */
    function handleScss(isTainted) {
        const individualScss = document.getElementById("individualScss");
        const overrideScss = document.getElementById("overrideScss");
        const refreshCodeMirror = (element) => {
            element.codemirror.refresh();
            element.codemirror.setCursor(element.codemirror.getCursor());
        };
        if (isTainted) {
            EventHandler.add("com.woltlab.wcf.simpleTabMenu_styleTabMenuContainer", "select", () => {
                refreshCodeMirror(individualScss);
                refreshCodeMirror(overrideScss);
            });
        }
        else {
            EventHandler.add("com.woltlab.wcf.simpleTabMenu_advanced", "select", (data) => {
                if (data.activeName === "advanced-custom") {
                    refreshCodeMirror(document.getElementById("individualScssCustom"));
                    refreshCodeMirror(document.getElementById("overrideScssCustom"));
                }
                else if (data.activeName === "advanced-original") {
                    refreshCodeMirror(individualScss);
                    refreshCodeMirror(overrideScss);
                }
            });
        }
    }
    function handleProtection(styleId) {
        const button = document.getElementById("styleDisableProtectionSubmit");
        const checkbox = document.getElementById("styleDisableProtectionConfirm");
        checkbox.addEventListener("change", () => {
            button.disabled = !checkbox.checked;
        });
        button.addEventListener("click", () => {
            Ajax.apiOnce({
                data: {
                    actionName: "markAsTainted",
                    className: "wcf\\data\\style\\StyleAction",
                    objectIDs: [styleId],
                },
                success: () => {
                    window.location.reload();
                },
            });
        });
    }
    function initVisualEditor() {
        _stylePreviewWindow.querySelectorAll("[data-region]").forEach((region) => {
            _stylePreviewRegions.set(region.dataset.region, region);
        });
        _stylePreviewRegionMarker = document.createElement("div");
        _stylePreviewRegionMarker.id = "stylePreviewRegionMarker";
        _stylePreviewRegionMarker.innerHTML = '<div id="stylePreviewRegionMarkerBottom"></div>';
        Util_1.default.hide(_stylePreviewRegionMarker);
        document.getElementById("colors").appendChild(_stylePreviewRegionMarker);
        const container = document.getElementById("spSidebar");
        const select = document.getElementById("spCategories");
        let lastValue = select.value;
        _updateRegionMarker = () => {
            if (_isSmartphone) {
                return;
            }
            if (lastValue === "none") {
                Util_1.default.hide(_stylePreviewRegionMarker);
                return;
            }
            const region = _stylePreviewRegions.get(lastValue);
            const rect = region.getBoundingClientRect();
            let top = rect.top + (window.scrollY || window.pageYOffset);
            Util_1.default.setStyles(_stylePreviewRegionMarker, {
                height: `${region.clientHeight + 20}px`,
                left: `${rect.left + document.body.scrollLeft - 10}px`,
                top: `${top - 10}px`,
                width: `${region.clientWidth + 20}px`,
            });
            Util_1.default.show(_stylePreviewRegionMarker);
            top = Util_1.default.offset(region).top;
            // `+ 80` = account for sticky header + selection markers (20px)
            const firstVisiblePixel = (window.pageYOffset || window.scrollY) + 80;
            if (firstVisiblePixel > top) {
                window.scrollTo(0, Math.max(top - 80, 0));
            }
            else {
                const lastVisiblePixel = window.innerHeight + (window.pageYOffset || window.scrollY);
                if (lastVisiblePixel < top) {
                    window.scrollTo(0, top);
                }
                else {
                    const bottom = top + region.offsetHeight + 20;
                    if (lastVisiblePixel < bottom) {
                        window.scrollBy(0, bottom - top);
                    }
                }
            }
        };
        const callbackChange = () => {
            let element = container.querySelector(`.spSidebarBox[data-category="${lastValue}"]`);
            Util_1.default.hide(element);
            lastValue = select.value;
            element = container.querySelector(`.spSidebarBox[data-category="${lastValue}"]`);
            Util_1.default.show(element);
            // set region marker
            _updateRegionMarker();
        };
        select.addEventListener("change", callbackChange);
        // apply CSS rules
        const style = document.createElement("style");
        style.appendChild(document.createTextNode(""));
        style.dataset.createdBy = "WoltLab/Acp/Ui/Style/Editor";
        document.head.appendChild(style);
        const spWindow = document.getElementById("spWindow");
        const wrapper = document.getElementById("spVariablesWrapper");
        wrapper.querySelectorAll(".styleVariableColor").forEach((colorField) => {
            const variableName = colorField.dataset.store.replace(/_value$/, "");
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === "style") {
                        spWindow.style.setProperty(`--${variableName}`, colorField.style.getPropertyValue("background-color"));
                    }
                });
            });
            observer.observe(colorField, {
                attributes: true,
            });
            spWindow.style.setProperty(`--${variableName}`, colorField.style.getPropertyValue("background-color"));
        });
        // category selection by clicking on the area
        const buttonToggleColorPalette = document.querySelector(".jsButtonToggleColorPalette");
        const buttonSelectCategoryByClick = document.querySelector(".jsButtonSelectCategoryByClick");
        function toggleSelectionMode() {
            buttonSelectCategoryByClick.classList.toggle("active");
            buttonToggleColorPalette.classList.toggle("disabled");
            _stylePreviewWindow.classList.toggle("spShowRegions");
            _stylePreviewRegionMarker.classList.toggle("forceHide");
            select.disabled = !select.disabled;
        }
        buttonSelectCategoryByClick.addEventListener("click", (event) => {
            event.preventDefault();
            toggleSelectionMode();
        });
        _stylePreviewWindow.querySelectorAll("[data-region]").forEach((region) => {
            region.addEventListener("click", (event) => {
                if (!_stylePreviewWindow.classList.contains("spShowRegions")) {
                    return;
                }
                event.preventDefault();
                event.stopPropagation();
                toggleSelectionMode();
                select.value = region.dataset.region;
                // Programmatically trigger the change event handler, rather than dispatching an event,
                // because Firefox fails to execute the event if it has previously been disabled.
                // See https://bugzilla.mozilla.org/show_bug.cgi?id=1426856
                callbackChange();
            });
        });
        // toggle view
        const spSelectCategory = document.getElementById("spSelectCategory");
        buttonToggleColorPalette.addEventListener("click", (event) => {
            event.preventDefault();
            buttonSelectCategoryByClick.classList.toggle("disabled");
            Util_1.default.toggle(spSelectCategory);
            buttonToggleColorPalette.classList.toggle("active");
            _stylePreviewWindow.classList.toggle("spColorPalette");
            _stylePreviewRegionMarker.classList.toggle("forceHide");
            select.disabled = !select.disabled;
        });
    }
    /**
     * Sets up dynamic style options.
     */
    function setup(options) {
        handleLayoutWidth();
        handleScss(options.isTainted);
        (0, DarkMode_1.setup)();
        if (!options.isTainted) {
            handleProtection(options.styleId);
        }
        initVisualEditor();
        UiScreen.on("screen-sm-down", {
            match() {
                hideVisualEditor();
            },
            unmatch() {
                showVisualEditor();
            },
            setup() {
                hideVisualEditor();
            },
        });
        function callbackRegionMarker() {
            if (_isVisible) {
                _updateRegionMarker();
            }
        }
        window.addEventListener("resize", callbackRegionMarker);
        EventHandler.add("com.woltlab.wcf.AcpMenu", "resize", callbackRegionMarker);
        EventHandler.add("com.woltlab.wcf.simpleTabMenu_styleTabMenuContainer", "select", function (data) {
            _isVisible = data.activeName === "colors";
            callbackRegionMarker();
        });
    }
    exports.setup = setup;
    function hideVisualEditor() {
        Util_1.default.hide(_stylePreviewWindow);
        document.getElementById("spVariablesWrapper").style.removeProperty("transform");
        Util_1.default.hide(document.getElementById("stylePreviewRegionMarker"));
        _isSmartphone = true;
    }
    exports.hideVisualEditor = hideVisualEditor;
    function showVisualEditor() {
        Util_1.default.show(_stylePreviewWindow);
        window.setTimeout(() => {
            Core.triggerEvent(document.getElementById("spCategories"), "change");
        }, 100);
        _isSmartphone = false;
    }
    exports.showVisualEditor = showVisualEditor;
});
