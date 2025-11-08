/**
 * Provides a selection dialog for FontAwesome icons with filter capabilities.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import UiItemListFilter from "../ItemList/Filter";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";

type CallbackSelect = (icon: string, forceSolid: boolean) => void;
type IconData = { icon: string; forceSolid: boolean };

function createIconList(): HTMLElement {
  const ul = document.createElement("ul");
  ul.classList.add("fontAwesome__icons");
  ul.id = "fontAwesomeIcons";

  const icons: string[] = [];
  window.getFontAwesome7Metadata().forEach(([, hasRegular], name) => {
    if (hasRegular) {
      icons.push(
        `<li><button type="button" class="fontAwesome__icon"><fa-icon size="48" name="${name}" solid></fa-icon><small class="fontAwesome__icon__name">${name}</small></button></li>`,
      );
    }

    icons.push(
      `<li><button type="button" class="fontAwesome__icon"><fa-icon size="48" name="${name}"></fa-icon><small class="fontAwesome__icon__name">${name}</small></button></li>`,
    );
  });

  ul.innerHTML = icons.join("");

  return ul;
}

let content: HTMLElement | undefined = undefined;
function getContent(): HTMLElement {
  if (content === undefined) {
    const iconList = createIconList();
    iconList.addEventListener("click", (event) => {
      event.preventDefault();

      const { target } = event;
      if (!(target instanceof HTMLButtonElement)) {
        return;
      }

      const icon = target.querySelector("fa-icon")!;
      const selectedEvent = new CustomEvent<IconData>("font-awesome:selected", {
        bubbles: true,
        detail: {
          icon: icon.name,
          forceSolid: icon.solid,
        },
      });
      iconList.dispatchEvent(selectedEvent);
    });

    content = document.createElement("div");
    content.id = "fontAwesomeSelection";
    content.append(iconList);
  }

  return content;
}

let itemListFilter: UiItemListFilter | undefined = undefined;
function setupListeners(): void {
  if (itemListFilter === undefined) {
    itemListFilter = new UiItemListFilter("fontAwesomeIcons", {
      callbackPrepareItem: (item) => {
        const small = item.querySelector("small") as HTMLElement;
        const text = small.textContent.trim();

        return {
          item,
          span: small,
          text,
        };
      },
      enableVisibilityFilter: false,
      filterPosition: "top",
    });
  } else {
    itemListFilter.reset();
  }
}

/**
 * Shows the FontAwesome selection dialog, supplied callback will be
 * invoked with the selection icon's name as the only argument.
 */
export function open(callback: CallbackSelect): void {
  const dialog = dialogFactory().fromElement(getContent()).asConfirmation();
  dialog.addEventListener(
    "font-awesome:selected",
    (event: CustomEvent<IconData>) => {
      dialog.close();

      callback(event.detail.icon, event.detail.forceSolid);
    },
    { once: true },
  );

  dialog.show(getPhrase("wcf.global.fontAwesome.selectIcon"));

  setupListeners();
}
