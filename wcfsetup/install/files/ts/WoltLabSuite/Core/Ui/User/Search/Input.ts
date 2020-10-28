/**
 * Provides suggestions for users, optionally supporting groups.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Search/Input
 * @see  module:WoltLabSuite/Core/Ui/Search/Input
 */

import * as Core from '../../../Core';
import UiSearchInput from '../../Search/Input';

class UiUserSearchInput extends UiSearchInput {
  constructor(element, options) {
    const includeUserGroups = (Core.isPlainObject(options) && options.includeUserGroups === true);

    options = Core.extend({
      ajax: {
        className: 'wcf\\data\\user\\UserAction',
        parameters: {
          data: {
            includeUserGroups: (includeUserGroups ? 1 : 0),
          },
        },
      },
    }, options);

    super(element, options);
  }

  protected createListItem(item): HTMLLIElement {
    const listItem = super.createListItem(item);
    listItem.dataset.type = item.type;

    const box = document.createElement('div');
    box.className = 'box16';
    box.innerHTML = (item.type === 'group') ? '<span class="icon icon16 fa-users"></span>' : item.icon;
    box.appendChild(listItem.children[0]);
    listItem.appendChild(box);

    return listItem;
  }
}

export = UiUserSearchInput
