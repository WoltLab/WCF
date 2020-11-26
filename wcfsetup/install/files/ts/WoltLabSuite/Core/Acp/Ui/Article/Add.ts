/**
 * Provides the dialog overlay to add a new article.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Article/Add
 */

import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";

class ArticleAdd implements DialogCallbackObject {
  constructor(private readonly link: string) {
    document.querySelectorAll(".jsButtonArticleAdd").forEach((button: HTMLElement) => {
      button.addEventListener("click", (ev) => this.openDialog(ev));
    });
  }

  openDialog(event?: MouseEvent): void {
    if (event instanceof Event) {
      event.preventDefault();
    }

    UiDialog.open(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "articleAddDialog",
      options: {
        onSetup: (content) => {
          const button = content.querySelector("button") as HTMLElement;
          button.addEventListener("click", (event) => {
            event.preventDefault();

            const input = content.querySelector('input[name="isMultilingual"]:checked') as HTMLInputElement;

            window.location.href = this.link.replace("{$isMultilingual}", input.value);
          });
        },
        title: Language.get("wcf.acp.article.add"),
      },
    };
  }
}

let articleAdd: ArticleAdd;

/**
 * Initializes the article add handler.
 */
export function init(link: string): void {
  if (!articleAdd) {
    articleAdd = new ArticleAdd(link);
  }
}

/**
 * Opens the 'Add Article' dialog.
 */
export function openDialog(): void {
  articleAdd.openDialog();
}
