/**
 * Provides the style editor.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Ajax from "../../../Ajax";
import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import * as EventHandler from "../../../Event/Handler";
import * as UiScreen from "../../../Ui/Screen";
import { setup as setupDarkMode } from "./DarkMode";

const _stylePreviewRegions = new Map<string, HTMLElement>();
let _stylePreviewRegionMarker: HTMLElement;
const _stylePreviewWindow = document.getElementById("spWindow")!;

let _isVisible = true;
let _isSmartphone = false;
let _updateRegionMarker: () => void;

interface StyleEditorOptions {
  isTainted: boolean;
  styleId: number;
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
    element.codemirror.setCursor(element.codemirror.getCursor());
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

function handleProtection(styleId: number): void {
  const button = document.getElementById("styleDisableProtectionSubmit") as HTMLButtonElement;
  const checkbox = document.getElementById("styleDisableProtectionConfirm") as HTMLInputElement;

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

function initVisualEditor(): void {
  _stylePreviewWindow.querySelectorAll("[data-region]").forEach((region: HTMLElement) => {
    _stylePreviewRegions.set(region.dataset.region!, region);
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

    const region = _stylePreviewRegions.get(lastValue)!;
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
  setupDarkMode();

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
