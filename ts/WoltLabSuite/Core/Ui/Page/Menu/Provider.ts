export interface PageMenuProvider {
  disable(): void;

  enable(): void;

  getContent(): DocumentFragment;

  getMenuButton(): HTMLElement;

  sleep(): void;

  wakeup(): void;
}
