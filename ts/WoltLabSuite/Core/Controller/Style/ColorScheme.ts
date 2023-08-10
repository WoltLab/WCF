/**
 * Dynamically updates the color scheme to match the system preference.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

export function setup(): void {
  const themeColor = document.querySelector<HTMLMetaElement>('meta[name="theme-color"]')!;
  themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");

  window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (event) => {
    document.documentElement.dataset.colorScheme = event.matches ? "dark" : "light";
    themeColor.content = window.getComputedStyle(document.body).getPropertyValue("--wcfPageThemeColor");
  });
}
