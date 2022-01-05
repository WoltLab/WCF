/**
 * Common interface and data types for containers that
 * span the entire content area.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/Provider
 * @woltlabExcludeBundle all
 */

export type MenuItemDepth = 0 | 1 | 2;

export type MenuItem = {
  active: boolean;
  children: MenuItem[];
  counter: number;
  depth: MenuItemDepth;
  link?: string;
  title: string;
};

export interface PageMenuMainProvider {
  getMenuItems(container: HTMLElement): MenuItem[];
}
