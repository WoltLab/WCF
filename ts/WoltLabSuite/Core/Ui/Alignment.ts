/**
 * Utility class to align elements relatively to another.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Core from "../Core";
import * as DomTraverse from "../Dom/Traverse";
import DomUtil from "../Dom/Util";
import * as Language from "../Language";
import * as Environment from "../Environment";

type HorizontalAlignment = "center" | "left" | "right";
type VerticalAlignment = "bottom" | "top";
type Offset = number | "auto";

interface HorizontalResult {
  align: HorizontalAlignment;
  left: Offset;
  result: boolean;
  right: Offset;
}

interface VerticalResult {
  align: VerticalAlignment;
  bottom: Offset;
  result: boolean;
  top: Offset;
}

const enum PointerClass {
  Bottom = 0,
  Right = 1,
}

interface ElementDimensions {
  height: number;
  width: number;
}

interface ElementOffset {
  left: number;
  top: number;
}

/**
 * Calculates top/bottom position and verifies if the element would be still within the page's boundaries.
 */
function tryAlignmentVertical(
  alignment: VerticalAlignment,
  elDimensions: ElementDimensions,
  refDimensions: ElementDimensions,
  refOffsets: ElementOffset,
  windowHeight: number,
  verticalOffset: number,
  isFixedPositioning: boolean,
): VerticalResult {
  let bottom: Offset = "auto";
  let top: Offset = "auto";
  let result = true;
  let pageHeaderOffset = 50;

  const pageHeaderPanel = document.getElementById("pageHeaderPanel");
  if (pageHeaderPanel !== null) {
    const position = window.getComputedStyle(pageHeaderPanel).position;
    if (position === "fixed" || position === "static") {
      pageHeaderOffset = pageHeaderPanel.offsetHeight;
    } else {
      pageHeaderOffset = 0;
    }
  }

  if (isFixedPositioning) {
    if (alignment === "top") {
      const bottomBoundary = refOffsets.top - verticalOffset;
      bottom = windowHeight - bottomBoundary;
      if (bottomBoundary - elDimensions.height < pageHeaderOffset) {
        result = false;
      }
    } else {
      top = refOffsets.top + refDimensions.height + verticalOffset;
      if (top + elDimensions.height > windowHeight) {
        result = false;
      }
    }
  } else {
    const offsetTop = refOffsets.top + window.scrollY;

    if (alignment === "top") {
      const { clientHeight } = document.body;

      bottom = clientHeight - offsetTop + verticalOffset;
      if (clientHeight - (bottom + elDimensions.height) < window.scrollY + pageHeaderOffset) {
        result = false;
      }
    } else {
      top = offsetTop + refDimensions.height + verticalOffset;
      if (top + elDimensions.height - window.scrollY > windowHeight) {
        result = false;
      }
    }
  }

  return {
    align: alignment,
    bottom: bottom,
    top: top,
    result: result,
  };
}

/**
 * Calculates left/right position and verifies if the element would be still within the page's boundaries.
 */
function tryAlignmentHorizontal(
  alignment: HorizontalAlignment,
  elDimensions: ElementDimensions,
  refDimensions: ElementDimensions,
  refOffsets: ElementOffset,
  windowWidth: number,
): HorizontalResult {
  let left: Offset = "auto";
  let right: Offset = "auto";
  let result = true;

  if (alignment === "left") {
    left = refOffsets.left;

    if (left + elDimensions.width > windowWidth) {
      result = false;
    }
  } else if (alignment === "right") {
    if (refOffsets.left + refDimensions.width < elDimensions.width) {
      result = false;
    } else {
      right = windowWidth - (refOffsets.left + refDimensions.width);

      if (right < 0) {
        result = false;
      }
    }
  } else {
    left = refOffsets.left + refDimensions.width / 2 - elDimensions.width / 2;
    left = ~~left;

    if (left < 0 || left + elDimensions.width > windowWidth) {
      result = false;
    }
  }

  return {
    align: alignment,
    left: left,
    right: right,
    result: result,
  };
}

/**
 * Sets the alignment for target element relatively to the reference element.
 */
export function set(element: HTMLElement, referenceElement: HTMLElement, options?: AlignmentOptions): void {
  options = Core.extend(
    {
      // offset to reference element
      verticalOffset: 0,
      // align the pointer element, expects .elementPointer as a direct child of given element
      pointer: false,
      // use static pointer positions, expects two items: class to move it to the bottom and the second to move it to the right
      pointerClassNames: [],
      // alternate element used to calculate dimensions
      refDimensionsElement: null,
      // preferred alignment, possible values: left/right/center and top/bottom
      horizontal: "left",
      vertical: "bottom",
      // allow flipping over axis, possible values: both, horizontal, vertical and none
      allowFlip: "both",
    },
    options || {},
  ) as AlignmentOptions;

  if (!Array.isArray(options.pointerClassNames) || options.pointerClassNames.length !== (options.pointer ? 1 : 2)) {
    options.pointerClassNames = [];
  }
  if (["left", "right", "center"].indexOf(options.horizontal!) === -1) {
    options.horizontal = "left";
  }
  if (options.vertical !== "bottom") {
    options.vertical = "top";
  }
  if (["both", "horizontal", "vertical", "none"].indexOf(options.allowFlip!) === -1) {
    options.allowFlip = "both";
  }

  let savedDisplayValue: string | undefined = undefined;
  if (window.getComputedStyle(element).display === "none") {
    savedDisplayValue = element.style.getPropertyValue("display");

    element.style.setProperty("display", "block");
  }

  // Place the element in the upper left corner to prevent calculation issues due to possible scrollbars.
  DomUtil.setStyles(element, {
    bottom: "auto !important",
    left: "0 !important",
    right: "auto !important",
    top: "0 !important",
    visibility: "hidden !important",
  });

  const elDimensions = DomUtil.outerDimensions(element);
  const refDimensions = DomUtil.outerDimensions(
    options.refDimensionsElement instanceof HTMLElement ? options.refDimensionsElement : referenceElement,
  );
  const refOffsets = referenceElement.getBoundingClientRect();
  const windowHeight = window.innerHeight;
  const windowWidth = document.body.clientWidth;

  let horizontal: HorizontalResult | null = null;
  let alignCenter = false;
  if (options.horizontal === "center") {
    alignCenter = true;
    horizontal = tryAlignmentHorizontal(options.horizontal, elDimensions, refDimensions, refOffsets, windowWidth);
    if (!horizontal.result) {
      if (options.allowFlip === "both" || options.allowFlip === "horizontal") {
        options.horizontal = "left";
      } else {
        horizontal.result = true;
      }
    }
  }

  // in rtl languages we simply swap the value for 'horizontal'
  if (Language.get("wcf.global.pageDirection") === "rtl") {
    options.horizontal = options.horizontal === "left" ? "right" : "left";
  }

  if (horizontal === null || !horizontal.result) {
    const horizontalCenter = horizontal;
    horizontal = tryAlignmentHorizontal(options.horizontal!, elDimensions, refDimensions, refOffsets, windowWidth);
    if (!horizontal.result && (options.allowFlip === "both" || options.allowFlip === "horizontal")) {
      const horizontalFlipped = tryAlignmentHorizontal(
        options.horizontal === "left" ? "right" : "left",
        elDimensions,
        refDimensions,
        refOffsets,
        windowWidth,
      );
      // only use these results if it fits into the boundaries, otherwise both directions exceed and we honor the demanded direction
      if (horizontalFlipped.result) {
        horizontal = horizontalFlipped;
      } else if (alignCenter) {
        horizontal = horizontalCenter;
      } else {
        // The element fits neither to the left nor the right, but the center
        // position is not requested either. This is especially an issue on mobile
        // devices where the element might exceed the window boundary if we are
        // stubborn about the alignment.
        //
        // The last thing we can try is to check if the element fits inside the
        // viewport and then align it at the left / right boundary, whichever
        // is closest to the boundary of the reference element.
        if (elDimensions.width === windowWidth) {
          horizontal = {
            align: "left",
            left: 0,
            result: true,
            right: 0,
          };
        } else if (elDimensions.width < windowWidth) {
          const distanceToRightBoundary = windowWidth - (refOffsets.left + refDimensions.width);
          const preferLeft = refOffsets.left <= distanceToRightBoundary;

          horizontal = {
            align: preferLeft ? "left" : "right",
            left: preferLeft ? 0 : "auto",
            result: true,
            right: preferLeft ? "auto" : 0,
          };
        }
      }
    }
  }

  const isFixedPositioning = window.getComputedStyle(element).position === "fixed";

  const left = horizontal!.left;
  const right = horizontal!.right;
  let vertical = tryAlignmentVertical(
    options.vertical,
    elDimensions,
    refDimensions,
    refOffsets,
    windowHeight,
    options.verticalOffset!,
    isFixedPositioning,
  );
  if (!vertical.result && (options.allowFlip === "both" || options.allowFlip === "vertical")) {
    const verticalFlipped = tryAlignmentVertical(
      options.vertical === "top" ? "bottom" : "top",
      elDimensions,
      refDimensions,
      refOffsets,
      windowHeight,
      options.verticalOffset!,
      isFixedPositioning,
    );
    // only use these results if it fits into the boundaries, otherwise both directions exceed and we honor the demanded direction
    if (verticalFlipped.result) {
      vertical = verticalFlipped;
    } else if (Environment.platform() !== "desktop") {
      // The element fits neither to the top nor the bottom. This is
      // especially an issue on mobile devices where the element might
      // exceed the window boundary if we are stubborn about the alignment.
      //
      // The last thing we can try is to check if the element fits inside the
      // viewport and then align it at the top / bottom boundary, whichever
      // is closest to the boundary of the reference element.
      if (elDimensions.height === windowHeight) {
        vertical = {
          align: "top",
          bottom: 0,
          result: true,
          top: 0,
        };
      } else if (elDimensions.height < windowHeight) {
        const distanceToBottomBoundary = windowHeight - (refOffsets.top + refDimensions.height);
        const preferTop = refOffsets.top <= distanceToBottomBoundary;

        vertical = {
          align: preferTop ? "top" : "bottom",
          bottom: preferTop ? 0 : "auto",
          result: true,
          top: preferTop ? "auto" : 0,
        };
      }
    }
  }

  const bottom = vertical.bottom;
  const top = vertical.top;

  // set pointer position
  if (options.pointer) {
    const pointers = DomTraverse.childrenByClass(element, "elementPointer");
    const pointer = pointers[0] || null;
    if (pointer === null) {
      throw new Error("Expected the .elementPointer element to be a direct children.");
    }

    if (horizontal!.align === "center") {
      pointer.classList.add("center");
      pointer.classList.remove("left", "right");
    } else {
      pointer.classList.add(horizontal!.align);
      pointer.classList.remove("center");
      pointer.classList.remove(horizontal!.align === "left" ? "right" : "left");
    }

    if (vertical.align === "top") {
      pointer.classList.add("flipVertical");
    } else {
      pointer.classList.remove("flipVertical");
    }
  } else if (options.pointerClassNames.length === 2) {
    element.classList[top === "auto" ? "add" : "remove"](options.pointerClassNames[PointerClass.Bottom]);
    element.classList[left === "auto" ? "add" : "remove"](options.pointerClassNames[PointerClass.Right]);
  }

  DomUtil.setStyles(element, {
    bottom: bottom === "auto" ? bottom : Math.round(bottom).toString() + "px",
    left: left === "auto" ? left : Math.ceil(left).toString() + "px",
    right: right === "auto" ? right : Math.floor(right).toString() + "px",
    top: top === "auto" ? top : Math.round(top).toString() + "px",
  });

  DomUtil.show(element);
  element.style.removeProperty("visibility");

  if (savedDisplayValue !== undefined) {
    if (savedDisplayValue === "") {
      element.style.removeProperty("display");
    } else {
      element.style.setProperty("display", savedDisplayValue);
    }
  }
}

export type AllowFlip = "both" | "horizontal" | "none" | "vertical";

export interface AlignmentOptions {
  // offset to reference element
  verticalOffset?: number;
  // align the pointer element, expects .elementPointer as a direct child of given element
  pointer?: boolean;
  // use static pointer positions, expects two items: class to move it to the bottom and the second to move it to the right
  pointerClassNames?: string[];
  // alternate element used to calculate dimensions
  refDimensionsElement?: HTMLElement | null;
  // preferred alignment, possible values: left/right/center and top/bottom
  horizontal?: HorizontalAlignment;
  vertical?: VerticalAlignment;
  // allow flipping over axis, possible values: both, horizontal, vertical and none
  allowFlip?: AllowFlip;
}
