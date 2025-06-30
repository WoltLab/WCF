/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { Fancybox, CarouselSlide, FancyboxInstance } from "@fancyapps/ui";
import { getPageOverlayContainer } from "WoltLabSuite/Core/Helper/PageOverlay";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { ConsentPlugin } from "./Fancybox/ConsentPlugin";

setDefaultConfig();

export function setup() {
  Fancybox.bind(
    '[data-fancybox]:is([data-type="image"],[data-type="youtube"],[data-type="vimeo"],[data-type="video"])',
  );
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
  if (!defaultConfig.plugins) {
    defaultConfig.plugins = {};
  }
  defaultConfig.plugins.consent = () => {
    return new ConsentPlugin();
  };
}

export function getLocalization(): Record<string, string> {
  return {
    IMAGE_ERROR: getPhrase("wcf.fancybox.imageError"),
    MOVE_UP: getPhrase("wcf.fancybox.moveUp"),
    MOVE_DOWN: getPhrase("wcf.fancybox.moveDown"),
    MOVE_LEFT: getPhrase("wcf.fancybox.moveLeft"),
    MOVE_RIGHT: getPhrase("wcf.fancybox.moveRight"),
    ZOOM_IN: getPhrase("wcf.fancybox.zoomIn"),
    ZOOM_OUT: getPhrase("wcf.fancybox.zoomOut"),
    TOGGLE_FULL: getPhrase("wcf.fancybox.toggleFull"),
    TOGGLE_1TO1: getPhrase("wcf.fancybox.toggle1to1"),
    ITERATE_ZOOM: getPhrase("wcf.fancybox.iterateZoom"),
    ROTATE_CCW: getPhrase("wcf.fancybox.rotateCcw"),
    ROTATE_CW: getPhrase("wcf.fancybox.rotateCw"),
    FLIP_X: getPhrase("wcf.fancybox.flipX"),
    FLIP_Y: getPhrase("wcf.fancybox.flipY"),
    RESET: getPhrase("wcf.fancybox.reset"),
    ERROR: getPhrase("wcf.fancybox.error"),
    GOTO: getPhrase("wcf.fancybox.goto"),
    DOWNLOAD: getPhrase("wcf.fancybox.download"),
    TOGGLE_EXPAND: getPhrase("wcf.fancybox.toggleExpand"),
    TOGGLE_FULLSCREEN: getPhrase("wcf.fancybox.toggleFullscreen"),
    TOGGLE_THUMBS: getPhrase("wcf.fancybox.toggleThumbs"),
    TOGGLE_AUTOPLAY: getPhrase("wcf.fancybox.toggleAutoplay"),
    CLOSE: getPhrase("wcf.fancybox.close"),
    NEXT: getPhrase("wcf.fancybox.next"),
    PREV: getPhrase("wcf.fancybox.prev"),
    MODAL: getPhrase("wcf.fancybox.modal"),
    ELEMENT_NOT_FOUND: getPhrase("wcf.fancybox.elementNotFound"),
    IFRAME_ERROR: getPhrase("wcf.fancybox.iframeError"),
  };
}
