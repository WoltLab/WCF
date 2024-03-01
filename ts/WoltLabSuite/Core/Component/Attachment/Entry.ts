export class AttachmentEntry {
  #attachmentId: number;
  readonly #name: string;

  constructor(attachmentId: number, name: string) {
    this.#attachmentId = attachmentId;
    this.#name = name;
  }
}

export default AttachmentEntry;
