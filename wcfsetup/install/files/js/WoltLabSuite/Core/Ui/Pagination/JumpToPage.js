/**
 * Handles the "jump to page" function in paginations.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "../../Component/Dialog", "../../Helper/Selector", "../../Language"], function (require, exports, Dialog_1, Selector_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function jumpToPage(element) {
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
          aria-label="${(0, Language_1.getPhrase)("wcf.page.jumpTo.pageNo")}"
          required
        >
        <small>${(0, Language_1.getPhrase)("wcf.page.jumpTo.description", { pages: element.count })}</small>
      </dd>
    </dl>
  `;
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(html).asPrompt();
        const input = dialog.content.querySelector('input[type="number"]');
        dialog.addEventListener("primary", () => {
            element.jumpToPage(parseInt(input.value));
        });
        dialog.show((0, Language_1.getPhrase)("wcf.page.jumpTo"));
        input.select();
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("woltlab-core-pagination", (element) => {
            element.addEventListener("jumpToPage", () => {
                jumpToPage(element);
            });
        });
    }
});
