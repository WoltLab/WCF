export interface RedactorEditor {
  $editor: JQuery;

  buffer: {
    set: () => void;
  };
  clean: {
    onSync: (html: string) => string;
  };
  code: {
    get: () => string;
    start: (html: string) => void;
  };
  core: {
    box: () => JQuery;
    editor: () => JQuery;
    element: () => JQuery;
    textarea: () => JQuery;
  };
  insert: {
    text: (text: string) => void;
  };
  utils: {
    isEmpty: (html: string) => boolean;
  };
}
