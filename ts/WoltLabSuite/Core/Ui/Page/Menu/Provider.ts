/**
 * Page menus are placed above the content and their visibility
 * is controlled through buttons placed in the page header.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/Provider
 * @woltlabExcludeBundle all
 */

export interface PageMenuProvider {
  /**
   * Disables this menu when switching from the mobile to the desktop view.
   */
  disable(): void;

  /**
   * Enables the menu once the mobile view became active.
   */
  enable(): void;

  /**
   * Returns arbitrary HTML elements that are placed inside the overlay container.
   */
  getContent(): DocumentFragment;

  /**
   * Provides the button that is used to change the visibility of the overlay container.
   */
  getMenuButton(): HTMLElement;

  /**
   * Suspends the activity of the container and returns any borrowed HTML elements
   * to their previous position. It is primarily used to restore the UI in case the
   * mobile view becomes inactive.
   */
  sleep(): void;

  /**
   * Restores the view by moving any borrowed HTML elements back into the container.
   * This method is also responsible to refresh the UI in case the underlying data
   * has changed.
   */
  wakeup(): void;
}
