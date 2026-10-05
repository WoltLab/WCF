/**
 * Switches the language through the language lists of the `system_pageHeader`, for guests and,
 * with the developer tools enabled, for members.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */

import { dboAction } from "../Ajax";
import User from "../User";

function switchLanguage(languageId: string, languageCode: string): void {
  // Multilingual content links its translations, the current page is used otherwise.
  const alternate = document.querySelector<HTMLLinkElement>(`link[hreflang="${CSS.escape(languageCode)}"]`);
  const url = new URL(alternate?.href ?? window.location.href);

  // `URLSearchParams` would encode the route in the query of non-rewritten URLs, e.g. `?thread/1-foo/`.
  const parameters = url.search
    .slice(1)
    .split("&")
    .filter((parameter) => parameter !== "" && !parameter.startsWith("l="));
  parameters.push(`l=${languageId}`);

  url.search = parameters.join("&");
  url.hash = window.location.hash;
  window.location.href = url.toString();
}

// Members can only switch the language through the developer tools.
async function switchLanguageDevtools(languageId: string, languageCode: string): Promise<void> {
  const currentLanguageCode = document.documentElement.lang;
  if (languageCode === currentLanguageCode) {
    window.location.reload();
    return;
  }

  const alternate = document.querySelector<HTMLLinkElement>(
    `link[rel="alternate"][hreflang="${CSS.escape(languageCode)}"]`,
  );
  if (alternate !== null && document.body.dataset.application === "wcf" && document.body.dataset.template === "cms") {
    // Pages like the landing page share one link for every language.
    const current = document.querySelector<HTMLLinkElement>(
      `link[rel="alternate"][hreflang="${CSS.escape(currentLanguageCode)}"]`,
    );
    if (current === null || current.href !== alternate.href) {
      window.location.href = alternate.href;
      return;
    }
  }

  await dboAction("devtoolsSetLanguage", "wcf\\data\\user\\UserAction")
    .payload({ languageID: parseInt(languageId, 10) })
    .dispatch();

  window.location.reload();
}

export function setup(): void {
  // The dropdown of the desktop bar moves its menu out of the header when it opens.
  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const element = event.target.closest<HTMLElement>("[data-switch-language]");
    if (element !== null) {
      event.preventDefault();

      if (User.userId) {
        void switchLanguageDevtools(element.dataset.switchLanguage!, element.dataset.languageCode!);
      } else {
        switchLanguage(element.dataset.switchLanguage!, element.dataset.languageCode!);
      }
    }
  });
}
