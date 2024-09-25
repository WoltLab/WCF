/**
 * The `dialogFactory()` offers a consistent way to
 * create modal dialogs. Dialogs can be used to inform
 * the user of an important message, to prompt them to
 * make a decision (see `confirmationFactory()`) or to
 * ask them to fill out a form.
 *
 * Dialogs interrupt a user’s flow on a page and thus
 * should only be used sparingly. Please refer to the
 * docs at https://docs.woltlab.com/ to learn more
 * about the different dialog types and how to best
 * use them.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "./Dialog/Setup", "../Element/woltlab-core-dialog", "../Element/woltlab-core-dialog-control"], function (require, exports, Setup_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.dialogFactory = dialogFactory;
    function dialogFactory() {
        return new Setup_1.DialogSetup();
    }
});
