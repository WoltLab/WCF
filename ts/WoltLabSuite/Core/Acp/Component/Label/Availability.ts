/**
 * Recursively marks child items as available for the label group.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

function observeListItem(listItem: HTMLLIElement): void {
  const checkbox = listItem.querySelector('input[type="checkbox"]') as HTMLInputElement;
  checkbox.addEventListener("change", () => {
    if (checkbox.checked) {
      const depth = parseInt(listItem.dataset.depth!);

      let nextItem = listItem.nextElementSibling as HTMLElement | null;
      while (nextItem !== null) {
        const isChild = parseInt(nextItem.dataset.depth!) > depth;
        if (!isChild) {
          break;
        }

        nextItem.querySelector<HTMLInputElement>('input[type="checkbox"]')!.checked = true;

        nextItem = nextItem.nextElementSibling as HTMLElement | null;
      }
    }
  });
}

export function setup(): void {
  const listItems = Array.from(document.querySelectorAll<HTMLLIElement>("#connect .structuredList li"));
  if (listItems.length === 0) {
    return;
  }

  for (const listItem of listItems) {
    observeListItem(listItem);
  }
}
