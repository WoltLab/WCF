/**
 * Handles the change of the show order of elements.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getObject } from "../Api/GetObject";
import { postObject } from "../Api/PostObject";
import { promiseMutex } from "../Helper/PromiseMutex";
import { getPhrase } from "../Language";
import { dialogFactory } from "./Dialog";
import Sortable from "sortablejs";
import { showDefaultSuccessSnackbar } from "./Snackbar";

type Item = {
  id: number;
  label: string;
};

async function showDialog(endpoint: string): Promise<void> {
  const items = await getItems(endpoint);

  const dialog = dialogFactory().fromHtml(getHtml(items)).asPrompt();
  dialog.show(getPhrase("wcf.global.changeShowOrder"));

  const sortable = new Sortable(dialog.content.querySelector(".sortableList")!, {
    direction: "vertical",
    animation: 150,
    fallbackOnBody: true,
    dataIdAttr: "data-object-id",
    draggable: "li",
    handle: ".sortableList__handle",
  });

  dialog.addEventListener("primary", () => {
    void saveItems(endpoint, sortable.toArray().map(Number)).then(() => {
      showDefaultSuccessSnackbar().addEventListener("snackbar:close", () => {
        window.location.reload();
      });
    });
  });
}

async function getItems(endpoint: string): Promise<Item[]> {
  return (await getObject<Item[]>(`${window.WSC_RPC_API_URL}${endpoint}`)).unwrap();
}

async function saveItems(endpoint: string, values: number[]): Promise<void> {
  await postObject(`${window.WSC_RPC_API_URL}${endpoint}`, { values });
}

function getHtml(items: Item[]): string {
  const list = document.createElement("ol");
  list.classList.add("sortableList");

  items.forEach((item) => {
    const listItem = document.createElement("li");
    listItem.dataset.objectId = item.id.toString();
    listItem.textContent = item.label;

    const icon = document.createElement("fa-icon");
    icon.setIcon("grip-vertical");
    const handle = document.createElement("span");
    handle.append(icon);
    handle.classList.add("sortableList__handle");
    listItem.prepend(handle);

    list.append(listItem);
  });

  return list.outerHTML;
}

export function setup(button: HTMLElement, endpoint: string): void {
  button.addEventListener(
    "click",
    promiseMutex(() => showDialog(endpoint)),
  );
}
