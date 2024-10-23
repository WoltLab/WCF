import { Fancybox } from "@fancyapps/ui";

const LOCALS = ["cs", "de", "en", "es", "fr", "it", "ja", "lv", "pl", "sk"];

export function setup() {
  void getLocalization().then((l10n) => {
    Fancybox.bind("[data-fancybox]", {
      l10n: l10n,
    });
    Fancybox.bind('[data-fancybox="attachments"]', {
      l10n: l10n,
    });
  });
}

export async function getLocalization(): Promise<Record<string, string>> {
  let local = document.documentElement.lang;

  if (!LOCALS.includes(local)) {
    local = "en";
  }

  return (await import(`@fancyapps/ui/l10n/${local}`))[local];
}
