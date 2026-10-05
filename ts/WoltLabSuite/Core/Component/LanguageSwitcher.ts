/**
 * Switches the language of guests through the language lists of the `system_pageHeader`.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */

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

export function setup(): void {
  // The dropdown of the desktop bar moves its menu out of the header when it opens.
  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const element = event.target.closest<HTMLElement>("[data-switch-language]");
    if (element !== null) {
      event.preventDefault();

      switchLanguage(element.dataset.switchLanguage!, element.dataset.languageCode!);
    }
  });
}
