import { Fancybox } from "@fancyapps/ui";

const LOCALES = ["cs", "de", "en", "es", "fr", "it", "lv", "pl", "sk"];

export function setup() {
  void getLocalization().then((l10n) => {
    Fancybox.bind("[data-fancybox]", {
      l10n: l10n,
    });
  });
}

export async function getLocalization(): Promise<Record<string, string>> {
  let locale = document.documentElement.lang;

  if (!LOCALES.includes(locale)) {
    locale = "en";
  }

  return (await import(`@fancyapps/ui/l10n/${locale}`))[locale];
}
