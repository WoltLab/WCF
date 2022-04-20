export interface RedactorEditor {
  uuid: string;
  $editor: JQuery;
  $element: JQuery;

  opts: {
    [key: string]: any;
  };

  buffer: {
    set(): void;
  };
  button: {
    addCallback(button: JQuery, callback: () => void): void;
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
    toolbar(): JQuery;
  };
  focus: {
    end(): void;
  };
  insert: {
    html(html: string): void;
    text(text: string): void;
  };
  selection: {
    block(): HTMLElement | false;
    restore(): void;
    save(): void;
  };
  utils: {
    isEmpty(html?: string): boolean;
  };

  WoltLabAutosave: {
    reset(): void;
  };
  WoltLabCaret: {
    endOfEditor(): void;
    paragraphAfterBlock(quote: HTMLElement): void;
  };
  WoltLabEvent: {
    register(event: string, callback: (data: WoltLabEventData) => void): void;
  };
  WoltLabReply: {
    showEditor(skipFocus?: boolean): void;
  };
  WoltLabSource: {
    isActive(): boolean;
  };
}

export interface WoltLabEventData {
  cancel: boolean;
  event: Event;
  redactor: RedactorEditor;
}
