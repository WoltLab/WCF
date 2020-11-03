export interface RedactorEditor {
  buffer: {
    set: () => void;
  };
  insert: {
    text: (text: string) => void;
  };
}
