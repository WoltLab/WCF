import UserMenuView from "../View";

export type UserMenuButton = {
  icon: string;
  link?: string;
  name: string;
  title: string;
};

export type UserMenuFooter = {
  link: string;
  title: string;
};

export interface UserMenuProvider {
  getData(): Promise<UserMenuData[]>;

  getFooter(): UserMenuFooter | null;

  getMenuButtons(): UserMenuButton[];

  getPanelButton(): HTMLElement;

  getTitle(): string;

  getView(): UserMenuView;

  markAllAsRead(): Promise<void>;

  onButtonClick(name: string): void;
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
