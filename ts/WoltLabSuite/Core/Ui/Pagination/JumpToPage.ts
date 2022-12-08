/**
 * Handles the "jump to page" function in paginations.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Pagination/JumpToPage
 * @since 6.0
 */

import { dialogFactory } from "../../Component/Dialog";
import { wheneverFirstSeen } from "../../Helper/Selector";
import { getPhrase } from "../../Language";

function jumpToPage(element: WoltlabCorePaginationElement): void {
  const html = `
    <dl>
      <dt></dt>
      <dd>
        <input
          class="tiny"
          type="number"
          min="1"
          max="${element.count}"
          value="${element.count}"
          aria-label="${getPhrase("wcf.page.jumpTo.pageNo")}"
          placeholder="${getPhrase("wcf.page.jumpTo.pageNo")}"
          required
        >
        <small>${getPhrase("wcf.page.jumpTo.description", { pages: element.count })}</small>
      </dd>
    </dl>
  `;
  const dialog = dialogFactory().fromHtml(html).asPrompt();
  const input = dialog.content.querySelector('input[type="number"]') as HTMLInputElement;
  dialog.addEventListener("primary", () => {
    element.jumpToPage(parseInt(input.value));
  });

  dialog.show(getPhrase("wcf.page.jumpTo"));
  input.select();
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-pagination", (element: WoltlabCorePaginationElement) => {
    element.addEventListener("jumpToPage", () => {
      jumpToPage(element);
    });
  });
}
