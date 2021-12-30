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
