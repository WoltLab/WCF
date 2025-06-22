import { Fancybox, CarouselSlide, FancyboxInstance } from "@fancyapps/ui";
import { getPageOverlayContainer } from "WoltLabSuite/Core/Helper/PageOverlay";

const LOCALES = { de: "de_DE", en: "en_EN" };

void setDefaultConfig();

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

async function setDefaultConfig(): Promise<void> {
  const defaultConfig = Fancybox.getDefaults();
  defaultConfig.l10n = await getLocalization();
  defaultConfig.parentEl = getPageOverlayContainer();
  defaultConfig.Carousel = {
    Video: {
      autoplay: false,
    },
  };
}

export async function getLocalization(): Promise<Record<string, string>> {
  let locale = document.documentElement.lang;

  if (!Object.prototype.hasOwnProperty.call(LOCALES, locale)) {
    locale = "en";
  }

  const code = LOCALES[locale];

  return (await import(`@fancyapps/ui/l10n/${code}`))[code];
}
