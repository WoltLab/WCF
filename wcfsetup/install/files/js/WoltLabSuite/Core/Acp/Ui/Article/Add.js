/**
 * Provides the dialog overlay to add a new article.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Article/Add
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.openDialog = exports.init = void 0;
    Language = (0, tslib_1.__importStar)(Language);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
    class ArticleAdd {
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
                    title: Language.get("wcf.acp.article.add"),
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
    exports.init = init;
    /**
     * Opens the 'Add Article' dialog.
     */
    function openDialog() {
        articleAdd.openDialog();
    }
    exports.openDialog = openDialog;
});
