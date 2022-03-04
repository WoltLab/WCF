/**
 * Provides the program logic for the import mapping reset.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/DataImport/MappingReset
 * @woltlabExcludeBundle all
 */

import * as Ajax from "../../../Ajax";
import * as Core from "../../../Core";
import * as UiConfirmation from "../../../Ui/Confirmation";

export function setup(): void {
  const link = document.getElementById("deleteMapping")!;

  link.addEventListener("click", (event) => {
    event.preventDefault();
    UiConfirmation.show({
      confirm() {
        Ajax.apiOnce({
          data: {
            actionName: "resetMapping",
            className: "wcf\\system\\importer\\ImportHandler",
          },
          success() {
            window.location.reload();
          },
          url: "index.php?ajax-invoke&t=" + Core.getXsrfToken(),
        });
      },
      message: link.dataset.confirmMessage!,
    });
  });
}
