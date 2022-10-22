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
 * @module WoltLabSuite/Core/Component/Dialog
 * @since 6.0
 */

import { DialogSetup } from "./Dialog/Setup";
import "../Element/woltlab-core-dialog";
import "../Element/woltlab-core-dialog-control";

export function dialogFactory(): DialogSetup {
  return new DialogSetup();
}
