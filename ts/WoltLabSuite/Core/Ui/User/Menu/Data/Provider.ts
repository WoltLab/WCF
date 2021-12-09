/**
 * Interface and data types for user menus.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/Data/Provider
 * @woltlabExcludeBundle all
 */

import UserMenuView from "../View";

export type UserMenuButton = {
  icon: string;
  link: string;
  name: string;
  title: string;
};

export type UserMenuFooter = {
  link: string;
  title: string;
};

export interface UserMenuProvider {
  getData(): Promise<UserMenuData[]>;

  getEmptyViewMessage(): string;

  getFooter(): UserMenuFooter | null;

  getIdentifier(): string;

  getMenuButtons(): UserMenuButton[];

  getPanelButton(): HTMLElement;

  getTitle(): string;

  getView(): UserMenuView;

  isStale(): boolean;

  markAsRead(objectId: number): Promise<void>;

  markAllAsRead(): Promise<void>;
}

export type UserMenuData = {
  content: string;
  image: string;
  isUnread: boolean;
  link: string;
  objectId: number;
  time: number;
  usernames: string[];
};
