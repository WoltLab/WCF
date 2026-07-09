/**
 * Provides the dialog overlay to add a new article.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    exports.openDialog = openDialog;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class ArticleAdd {
        link;
        constructor(link) {
            this.link = link;
            document.querySelectorAll(".jsButtonArticleAdd").forEach((button) => {
                button.addEventListener("click", (ev) => this.openDialog(ev));
            });
        }
        openDialog(event) {
            if (event instanceof Event) {
                event.preventDefault();
            }
            Dialog_1.default.open(this);
        }
        _dialogSetup() {
            return {
                id: "articleAddDialog",
                options: {
                    onSetup: (content) => {
                        const button = content.querySelector("button");
                        button.addEventListener("click", (event) => {
                            event.preventDefault();
                            const input = content.querySelector('input[name="isMultilingual"]:checked');
                            window.location.href = this.link.replace("{$isMultilingual}", input.value);
                        });
                    },
                    title: (0, Language_1.getPhrase)("wcf.acp.article.add"),
                },
            };
        }
    }
    let articleAdd;
    /**
     * Initializes the article add handler.
     */
    function init(link) {
        if (!articleAdd) {
            articleAdd = new ArticleAdd(link);
        }
    }
    /**
     * Opens the 'Add Article' dialog.
     */
    function openDialog() {
        articleAdd.openDialog();
    }
});
