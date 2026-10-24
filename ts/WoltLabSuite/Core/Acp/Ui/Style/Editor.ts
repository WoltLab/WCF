/**
 * Provides the style editor.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Core from "../../../Core";
import * as ColorUtil from "../../../ColorUtil";
import DomUtil from "../../../Dom/Util";
import * as EventHandler from "../../../Event/Handler";
import * as UiScreen from "../../../Ui/Screen";

const _stylePreviewRegions = new Map<string, HTMLElement[]>();
let _stylePreviewRegionMarker: HTMLElement;
const _stylePreviewWindow = document.getElementById("spWindow")!;

let _isVisible = true;
let _isSmartphone = false;
let _updateRegionMarker: () => void;

interface StyleEditorOptions {
  isTainted: boolean;
}

/**
 * Compares two colors regardless of their notation, e.g. `.8` and `0.8` for the alpha channel.
 */
function isSameColor(a: string, b: string): boolean {
  if (a === b) {
    return true;
  }

  if (!ColorUtil.isValidColor(a) || !ColorUtil.isValidColor(b)) {
    return false;
  }

  const colorA = ColorUtil.stringToRgba(a);
  const colorB = ColorUtil.stringToRgba(b);

  return colorA.r === colorB.r && colorA.g === colorB.g && colorA.b === colorB.b && colorA.a === colorB.a;
}

/**
 * Handles the switch between static and fluid layout.
 */
function handleLayoutWidth(): void {
  const useFluidLayout = document.getElementById("useFluidLayout") as HTMLInputElement;
  const fluidLayoutMinWidth = document.getElementById("fluidLayoutMinWidth") as HTMLInputElement;
  const fluidLayoutMaxWidth = document.getElementById("fluidLayoutMaxWidth") as HTMLInputElement;
  const fixedLayoutVariables = document.getElementById("fixedLayoutVariables") as HTMLDListElement;

  function change(): void {
    if (useFluidLayout.checked) {
      DomUtil.show(fluidLayoutMinWidth);
      DomUtil.show(fluidLayoutMaxWidth);
      DomUtil.hide(fixedLayoutVariables);
    } else {
      DomUtil.hide(fluidLayoutMinWidth);
      DomUtil.hide(fluidLayoutMaxWidth);
      DomUtil.show(fixedLayoutVariables);
    }
  }

  useFluidLayout.addEventListener("change", change);

  change();
}

/**
 * Handles SCSS input fields.
 */
function handleScss(isTainted: boolean): void {
  const individualScss = document.getElementById("individualScss")!;
  const overrideScss = document.getElementById("overrideScss")!;

  const refreshCodeMirror = (element: any): void => {
    element.codemirror.refresh();
    element.codemirror.setCursor(element.codemirror.getCursor(), undefined, { scroll: false });
  };

  if (isTainted) {
    EventHandler.add("com.woltlab.wcf.simpleTabMenu_styleTabMenuContainer", "select", () => {
      refreshCodeMirror(individualScss);
      refreshCodeMirror(overrideScss);
    });
  } else {
    EventHandler.add("com.woltlab.wcf.simpleTabMenu_advanced", "select", (data: { activeName: string }) => {
      if (data.activeName === "advanced-custom") {
        refreshCodeMirror(document.getElementById("individualScssCustom"));
        refreshCodeMirror(document.getElementById("overrideScssCustom"));
      } else if (data.activeName === "advanced-original") {
        refreshCodeMirror(individualScss);
        refreshCodeMirror(overrideScss);
      }
    });
  }
}

/**
 * Mirrors the selected style options in the preview, e.g. `data-page-header-layout`.
 */
function handleStyleOptions(): void {
  document.querySelectorAll<HTMLInputElement>('.styleOptionList input[type="radio"]').forEach((input) => {
    input.addEventListener("change", () => {
      _stylePreviewWindow.dataset[input.name] = input.value;
    });
  });
}

function initVisualEditor(): void {
  _stylePreviewWindow.querySelectorAll("[data-region]").forEach((region: HTMLElement) => {
    const name = region.dataset.region!;
    const regions = _stylePreviewRegions.get(name);
    if (regions === undefined) {
      _stylePreviewRegions.set(name, [region]);
    } else {
      regions.push(region);
    }
  });

  _stylePreviewRegionMarker = document.createElement("div");
  _stylePreviewRegionMarker.id = "stylePreviewRegionMarker";
  _stylePreviewRegionMarker.innerHTML = '<div id="stylePreviewRegionMarkerBottom"></div>';
  DomUtil.hide(_stylePreviewRegionMarker);
  document.getElementById("colors")!.appendChild(_stylePreviewRegionMarker);

  const container = document.getElementById("spSidebar")!;
  const select = document.getElementById("spCategories") as HTMLSelectElement;
  let lastValue = select.value;

  _updateRegionMarker = (): void => {
    if (_isSmartphone) {
      return;
    }

    if (lastValue === "none") {
      DomUtil.hide(_stylePreviewRegionMarker);
      return;
    }

    // Some regions appear once per page header layout, only one of them is visible.
    const region = _stylePreviewRegions.get(lastValue)!.find((element) => element.getClientRects().length > 0);
    if (region === undefined) {
      DomUtil.hide(_stylePreviewRegionMarker);
      return;
    }

    const rect = region.getBoundingClientRect();

    let top = rect.top + (window.scrollY || window.pageYOffset);

    DomUtil.setStyles(_stylePreviewRegionMarker, {
      height: `${region.clientHeight + 20}px`,
      left: `${rect.left + document.body.scrollLeft - 10}px`,
      top: `${top - 10}px`,
      width: `${region.clientWidth + 20}px`,
    });

    DomUtil.show(_stylePreviewRegionMarker);

    top = DomUtil.offset(region).top;
    // `+ 80` = account for sticky header + selection markers (20px)
    const firstVisiblePixel = (window.pageYOffset || window.scrollY) + 80;
    if (firstVisiblePixel > top) {
      window.scrollTo(0, Math.max(top - 80, 0));
    } else {
      const lastVisiblePixel = window.innerHeight + (window.pageYOffset || window.scrollY);
      if (lastVisiblePixel < top) {
        window.scrollTo(0, top);
      } else {
        const bottom = top + region.offsetHeight + 20;
        if (lastVisiblePixel < bottom) {
          window.scrollBy(0, bottom - top);
        }
      }
    }
  };

  const callbackChange = () => {
    let element = container.querySelector(`.spSidebarBox[data-category="${lastValue}"]`) as HTMLElement;
    DomUtil.hide(element);

    lastValue = select.value;
    element = container.querySelector(`.spSidebarBox[data-category="${lastValue}"]`) as HTMLElement;
    DomUtil.show(element);

    // set region marker
    _updateRegionMarker();
  };
  select.addEventListener("change", callbackChange);

  // apply CSS rules
  const style = document.createElement("style");
  style.appendChild(document.createTextNode(""));
  style.dataset.createdBy = "WoltLab/Acp/Ui/Style/Editor";
  document.head.appendChild(style);

  const spWindow = document.getElementById("spWindow")!;
  const wrapper = document.getElementById("spVariablesWrapper")!;
  wrapper.querySelectorAll(".styleVariableColor").forEach((colorField: HTMLElement) => {
    const variableName = colorField.dataset.store!.replace(/_value$/, "");
    const input = document.getElementById(colorField.dataset.store!) as HTMLInputElement;
    const restoreButton = wrapper.querySelector<HTMLButtonElement>(
      `.spColorRestore[data-store="${colorField.dataset.store!}"]`,
    );

    const updateRestoreButton = () => {
      if (restoreButton !== null) {
        restoreButton.hidden = isSameColor(input.value, restoreButton.dataset.authoredColor!);
      }
    };

    restoreButton?.addEventListener("click", () => {
      const authoredColor = restoreButton.dataset.authoredColor!;

      input.value = authoredColor;
      colorField.dataset.color = authoredColor;
      colorField.style.setProperty("background-color", authoredColor);
    });

    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === "style") {
          spWindow.style.setProperty(`--${variableName}`, colorField.style.getPropertyValue("background-color"));
          updateRestoreButton();
        }
      });
    });

    observer.observe(colorField, {
      attributes: true,
    });

    spWindow.style.setProperty(`--${variableName}`, colorField.style.getPropertyValue("background-color"));
    updateRestoreButton();
  });

  // category selection by clicking on the area
  const buttonToggleColorPalette = document.querySelector(".jsButtonToggleColorPalette") as HTMLAnchorElement;
  const buttonSelectCategoryByClick = document.querySelector(".jsButtonSelectCategoryByClick") as HTMLAnchorElement;

  function toggleSelectionMode(): void {
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

  _stylePreviewWindow.querySelectorAll("[data-region]").forEach((region: HTMLElement) => {
    region.addEventListener("click", (event) => {
      if (!_stylePreviewWindow.classList.contains("spShowRegions")) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      toggleSelectionMode();

      select.value = region.dataset.region!;

      // Programmatically trigger the change event handler, rather than dispatching an event,
      // because Firefox fails to execute the event if it has previously been disabled.
      // See https://bugzilla.mozilla.org/show_bug.cgi?id=1426856
      callbackChange();
    });
  });

  // toggle view
  const spSelectCategory = document.getElementById("spSelectCategory") as HTMLSelectElement;
  buttonToggleColorPalette.addEventListener("click", (event) => {
    event.preventDefault();

    buttonSelectCategoryByClick.classList.toggle("disabled");
    DomUtil.toggle(spSelectCategory);
    buttonToggleColorPalette.classList.toggle("active");
    _stylePreviewWindow.classList.toggle("spColorPalette");
    _stylePreviewRegionMarker.classList.toggle("forceHide");
    select.disabled = !select.disabled;
  });
}

/**
 * Sets up dynamic style options.
 */
export function setup(options: StyleEditorOptions): void {
  handleLayoutWidth();
  handleScss(options.isTainted);

  initVisualEditor();
  handleStyleOptions();

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

  function callbackRegionMarker(): void {
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

export function hideVisualEditor(): void {
  DomUtil.hide(_stylePreviewWindow);
  document.getElementById("spVariablesWrapper")!.style.removeProperty("transform");
  DomUtil.hide(document.getElementById("stylePreviewRegionMarker")!);

  _isSmartphone = true;
}

export function showVisualEditor(): void {
  DomUtil.show(_stylePreviewWindow);

  window.setTimeout(() => {
    Core.triggerEvent(document.getElementById("spCategories")!, "change");
  }, 100);

  _isSmartphone = false;
}
