export interface PageMenuProvider {
  getContent(): DocumentFragment;

  getMenuButton(): HTMLElement;
}
