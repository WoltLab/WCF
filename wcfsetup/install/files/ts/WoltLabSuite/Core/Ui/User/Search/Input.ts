/**
 * Provides suggestions for users, optionally supporting groups.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Search/Input
 * @see  module:WoltLabSuite/Core/Ui/Search/Input
 */

import * as Core from "../../../Core";
import { SearchInputOptions } from "../../Search/Data";
import UiSearchInput from "../../Search/Input";

class UiUserSearchInput extends UiSearchInput {
  constructor(element: HTMLInputElement, options: UserSearchInputOptions) {
    const includeUserGroups = Core.isPlainObject(options) && options.includeUserGroups === true;

    options = Core.extend(
      {
        ajax: {
          className: "wcf\\data\\user\\UserAction",
          parameters: {
            data: {
              includeUserGroups: includeUserGroups ? 1 : 0,
            },
          },
        },
      },
      options
    );

    super(element, options);
  }

  protected createListItem(item: UserListItemData): HTMLLIElement {
    const listItem = super.createListItem(item);
    listItem.dataset.type = item.type;

    const box = document.createElement("div");
    box.className = "box16";
    box.innerHTML = item.type === "group" ? '<span class="icon icon16 fa-users"></span>' : item.icon;
    box.appendChild(listItem.children[0]);
    listItem.appendChild(box);

    return listItem;
  }
}

export = UiUserSearchInput;

// https://stackoverflow.com/a/50677584/782822
// This is a dirty hack, because the ListItemData cannot be exported for compatibility reasons.
type FirstArgument<T> = T extends (arg1: infer U, ...args: any[]) => any ? U : never;

interface UserListItemData extends FirstArgument<UiSearchInput["createListItem"]> {
  type: "user" | "group";
  icon: string;
}

interface UserSearchInputOptions extends SearchInputOptions {
  includeUserGroups?: boolean;
}
