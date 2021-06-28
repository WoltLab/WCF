/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

export abstract class AbstractUserAction {
  protected readonly button: HTMLElement;
  protected readonly userDataElement: HTMLElement;
  protected readonly userId: number;

  public constructor(button: HTMLElement, userId: number, userDataElement: HTMLElement) {
    this.button = button;
    this.userId = userId;
    this.userDataElement = userDataElement;

    this.init();
  }

  protected abstract init(): void;
}

export default AbstractUserAction;
