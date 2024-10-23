import { Fancybox } from "@fancyapps/ui";
import { userSlideType } from "@fancyapps/ui/types/Carousel/types";
import { OptionsType } from "@fancyapps/ui/types/Fancybox/options";

const LOCALES = ["cs", "de", "en", "es", "fr", "it", "lv", "pl", "sk"];

export function setup() {
  void getDefaultConfig().then((config) => {
    Fancybox.bind("[data-fancybox]", config);
  });
}

export async function createFancybox(userSlides?: Array<userSlideType>): Promise<Fancybox> {
  return new Fancybox(userSlides, await getDefaultConfig());
}

async function getDefaultConfig(): Promise<Partial<OptionsType>> {
  return {
    l10n: await getLocalization(),
    Html: {
      videoAutoplay: false,
    },
  };
}

export async function getLocalization(): Promise<Record<string, string>> {
  let locale = document.documentElement.lang;

  if (!LOCALES.includes(locale)) {
    locale = "en";
  }

  return (await import(`@fancyapps/ui/l10n/${locale}`))[locale];
}
