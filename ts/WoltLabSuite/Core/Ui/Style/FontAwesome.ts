/**
 * Provides a selection dialog for FontAwesome icons with filter capabilities.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { DialogCallbackObject, DialogCallbackSetup } from "../Dialog/Data";
import * as Language from "../../Language";
import UiDialog from "../Dialog";
import UiItemListFilter from "../ItemList/Filter";

type CallbackSelect = (icon: string, forceSolid: boolean) => void;

class UiStyleFontAwesome implements DialogCallbackObject {
  private callback?: CallbackSelect = undefined;
  private iconList?: HTMLElement = undefined;
  private itemListFilter?: UiItemListFilter = undefined;

  open(callback: CallbackSelect): void {
    this.callback = callback;

    UiDialog.open(this);
  }

  /**
   * Selects an icon, notifies the callback and closes the dialog.
   */
  protected click(event: MouseEvent): void {
    event.preventDefault();

    const target = event.target as HTMLElement;
    const item = target.closest("li") as HTMLLIElement;
    const icon = item.querySelector("fa-icon")!;

    UiDialog.close(this);

    this.callback!(icon.name, icon.solid);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "fontAwesomeSelection",
      options: {
        onSetup: () => {
          this.iconList = document.getElementById("fontAwesomeIcons") as HTMLElement;

          const icons: string[] = [];
          window.getFontAwesome6Metadata().forEach(([, hasRegular], name) => {
            if (hasRegular) {
              icons.push(`<li><fa-icon size="48" name="${name}" solid></fa-icon><small>${name}</small></li>`);
            }

            icons.push(`<li><fa-icon size="48" name="${name}"></fa-icon><small>${name}</small></li>`);
          });

          // build icons
          this.iconList.innerHTML = icons.join("");

          this.iconList.addEventListener("click", (ev) => this.click(ev));

          this.itemListFilter = new UiItemListFilter("fontAwesomeIcons", {
            callbackPrepareItem: (item) => {
              const small = item.querySelector("small") as HTMLElement;
              const text = small.textContent!.trim();

              return {
                item,
                span: small,
                text,
              };
            },
            enableVisibilityFilter: false,
            filterPosition: "top",
          });
        },
        onShow: () => {
          this.itemListFilter!.reset();
        },
        title: Language.get("wcf.global.fontAwesome.selectIcon"),
      },
      source: '<ul class="fontAwesomeIcons" id="fontAwesomeIcons"></ul>',
    };
  }
}

let uiStyleFontAwesome: UiStyleFontAwesome;

/**
 * Sets the list of available icons, must be invoked prior to any call
 * to the `open()` method.
 */
export function setup(): void {
  if (!uiStyleFontAwesome) {
    uiStyleFontAwesome = new UiStyleFontAwesome();
  }
}

/**
 * Shows the FontAwesome selection dialog, supplied callback will be
 * invoked with the selection icon's name as the only argument.
 */
export function open(callback: CallbackSelect): void {
  if (!uiStyleFontAwesome) {
    throw new Error(
      "Missing icon data, please include the template before calling this method using `{include file='shared_fontAwesomeJavaScript'}`.",
    );
  }

  uiStyleFontAwesome.open(callback);
}
