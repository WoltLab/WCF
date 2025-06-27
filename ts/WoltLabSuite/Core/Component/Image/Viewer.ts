import { Fancybox, CarouselSlide, FancyboxInstance } from "@fancyapps/ui";
import { getPageOverlayContainer } from "WoltLabSuite/Core/Helper/PageOverlay";
import { getPhrase } from "WoltLabSuite/Core/Language";

setDefaultConfig();

export function setup() {
  Fancybox.bind("[data-fancybox]");
}

export function setupLegacy() {
  Fancybox.bind(".jsImageViewer", {
    groupAll: true,
  });
}

export function showFancybox(userSlides?: Array<CarouselSlide>): FancyboxInstance {
  return Fancybox.show(userSlides);
}

function setDefaultConfig(): void {
  const defaultConfig = Fancybox.getDefaults();
  defaultConfig.l10n = getLocalization();
  defaultConfig.parentEl = getPageOverlayContainer();
  defaultConfig.Carousel = {
    Video: {
      autoplay: false,
    },
  };
}

export function getLocalization(): Record<string, string> {
  return {
    IMAGE_ERROR: getPhrase("wcf.fancybox.image_error"),
    MOVE_UP: getPhrase("wcf.fancybox.move_up"),
    MOVE_DOWN: getPhrase("wcf.fancybox.move_down"),
    MOVE_LEFT: getPhrase("wcf.fancybox.move_left"),
    MOVE_RIGHT: getPhrase("wcf.fancybox.move_right"),
    ZOOM_IN: getPhrase("wcf.fancybox.zoom_in"),
    ZOOM_OUT: getPhrase("wcf.fancybox.zoom_out"),
    TOGGLE_FULL: getPhrase("wcf.fancybox.toggle_full"),
    TOGGLE_1TO1: getPhrase("wcf.fancybox.toggle_1to1"),
    ITERATE_ZOOM: getPhrase("wcf.fancybox.iterate_zoom"),
    ROTATE_CCW: getPhrase("wcf.fancybox.rotate_ccw"),
    ROTATE_CW: getPhrase("wcf.fancybox.rotate_cw"),
    FLIP_X: getPhrase("wcf.fancybox.flip_x"),
    FLIP_Y: getPhrase("wcf.fancybox.flip_y"),
    RESET: getPhrase("wcf.fancybox.reset"),
    ERROR: getPhrase("wcf.fancybox.error"),
    GOTO: getPhrase("wcf.fancybox.goto"),
    DOWNLOAD: getPhrase("wcf.fancybox.download"),
    TOGGLE_EXPAND: getPhrase("wcf.fancybox.toggle_expand"),
    TOGGLE_FULLSCREEN: getPhrase("wcf.fancybox.toggle_fullscreen"),
    TOGGLE_THUMBS: getPhrase("wcf.fancybox.toggle_thumbs"),
    TOGGLE_AUTOPLAY: getPhrase("wcf.fancybox.toggle_autoplay"),
    CLOSE: getPhrase("wcf.fancybox.close"),
    NEXT: getPhrase("wcf.fancybox.next"),
    PREV: getPhrase("wcf.fancybox.prev"),
    MODAL: getPhrase("wcf.fancybox.modal"),
    ELEMENT_NOT_FOUND: getPhrase("wcf.fancybox.element_not_found"),
    IFRAME_ERROR: getPhrase("wcf.fancybox.iframe_error"),
  };
}
