export interface RedactorEditor {
  $editor: JQuery;
  $element: JQuery;

  opts: {
    [key: string]: any;
  };

  buffer: {
    set(): void;
  };
  button: {
    toggle(event: MouseEvent | object, btnName: string, type: string, callback: string, args?: object): void;
  };
  caret: {
    after(node: Node): void;
    end(node: Node): void;
  };
  clean: {
    onSync(html: string): string;
  };
  code: {
    get(): string;
    set(html: string): void;
    start(html: string): void;
  };
  core: {
    box(): JQuery;
    editor(): JQuery;
    element(): JQuery;
    textarea(): JQuery;
  };
  focus: {
    end(): void;
  };
  insert: {
    text(text: string): void;
  };
  selection: {
    block(): HTMLElement | false;
    restore(): void;
    save(): void;
  };
  utils: {
    isEmpty(html: string): boolean;
  };
}
