/**
 * Provides an item list for users and groups.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/ItemList/User
 */

import { CallbackChange, CallbackSetupValues, CallbackSyncShadow, ElementData, ItemData } from "../ItemList";
import * as UiItemList from "../ItemList";

interface ItemListUserOptions {
  callbackChange?: CallbackChange;
  callbackSetupValues?: CallbackSetupValues;
  csvPerType?: boolean;
  excludedSearchValues?: string[];
  includeUserGroups?: boolean;
  maxItems?: number;
  restrictUserGroupIDs?: number[];
}

interface UserElementData extends ElementData {
  _shadowGroups?: HTMLInputElement;
}

function syncShadow(data: UserElementData): ReturnType<CallbackSyncShadow> {
  const values = getValues(data.element.id);

  const users: string[] = [];
  const groups: number[] = [];
  values.forEach((value) => {
    if (value.type && value.type === "group") {
      groups.push(value.objectId);
    } else {
      users.push(value.value);
    }
  });

  const shadowElement = data.shadow!;
  shadowElement.value = users.join(",");
  if (!data._shadowGroups) {
    data._shadowGroups = document.createElement("input");
    data._shadowGroups.type = "hidden";
    data._shadowGroups.name = `${shadowElement.name}GroupIDs`;
    shadowElement.insertAdjacentElement("beforebegin", data._shadowGroups);
  }
  data._shadowGroups.value = groups.join(",");

  return values;
}

/**
 * Initializes user suggestion support for an element.
 *
 * @param  {string}  elementId  input element id
 * @param  {object}  options    option list
 */
export function init(elementId: string, options: ItemListUserOptions): void {
  UiItemList.init(elementId, [], {
    ajax: {
      className: "wcf\\data\\user\\UserAction",
      parameters: {
        data: {
          includeUserGroups: options.includeUserGroups ? ~~options.includeUserGroups : 0,
          restrictUserGroupIDs: Array.isArray(options.restrictUserGroupIDs) ? options.restrictUserGroupIDs : [],
        },
      },
    },
    callbackChange: typeof options.callbackChange === "function" ? options.callbackChange : null,
    callbackSyncShadow: options.csvPerType ? syncShadow : null,
    callbackSetupValues: typeof options.callbackSetupValues === "function" ? options.callbackSetupValues : null,
    excludedSearchValues: Array.isArray(options.excludedSearchValues) ? options.excludedSearchValues : [],
    isCSV: true,
    maxItems: options.maxItems ? ~~options.maxItems : -1,
    restricted: true,
  });
}

/**
 * @see  WoltLabSuite/Core/Ui/ItemList::getValues()
 */
export function getValues(elementId: string): ItemData[] {
  return UiItemList.getValues(elementId);
}
