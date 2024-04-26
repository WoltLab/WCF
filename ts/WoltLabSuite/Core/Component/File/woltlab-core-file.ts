const enum State {
  Initial,
  Uploading,
  GeneratingThumbnails,
  Ready,
  Failed,
}

export type ThumbnailData = {
  identifier: string;
  link: string;
};

export class Thumbnail {
  readonly #identifier: string;
  readonly #link: string;

  constructor(identifier: string, link: string) {
    this.#identifier = identifier;
    this.#link = link;
  }

  get identifier(): string {
    return this.#identifier;
  }

  get link(): string {
    return this.#link;
  }
}

export class WoltlabCoreFileElement extends HTMLElement {
  #data: Record<string, unknown> | undefined = undefined;
  #filename: string = "";
  #fileId: number | undefined = undefined;
  #link: string | undefined = undefined;
  #mimeType: string | undefined = undefined;
  #state: State = State.Initial;
  readonly #thumbnails: Thumbnail[] = [];

  #readyReject!: () => void;
  #readyResolve!: () => void;
  readonly #readyPromise: Promise<void>;

  constructor() {
    super();

    this.#readyPromise = new Promise((resolve, reject) => {
      this.#readyResolve = resolve;
      this.#readyReject = reject;
    });
  }

  connectedCallback() {
    let wasAlreadyReady = false;
    if (this.#state === State.Initial) {
      wasAlreadyReady = this.#initializeState();
    }

    this.#rebuildElement();

    if (wasAlreadyReady) {
      this.#readyResolve();
    }
  }

  #initializeState(): boolean {
    // Files that exist at page load have a valid file id, otherwise a new
    // file element can only be the result of an upload attempt.
    if (this.#fileId === undefined) {
      this.#filename = this.dataset.filename || "bogus.bin";
      delete this.dataset.filename;

      this.#mimeType = this.dataset.mimeType || "application/octet-stream";
      delete this.dataset.mimeType;

      const fileId = parseInt(this.getAttribute("file-id") || "0");
      if (fileId) {
        this.#fileId = fileId;
      } else {
        this.#state = State.Uploading;

        return false;
      }
    }

    // Initialize the list of thumbnails from the data attribute.
    if (this.dataset.thumbnails) {
      const thumbnails = JSON.parse(this.dataset.thumbnails) as ThumbnailData[];
      for (const thumbnail of thumbnails) {
        this.#thumbnails.push(new Thumbnail(thumbnail.identifier, thumbnail.link));
      }
    }

    if (this.dataset.metaData) {
      this.#data = JSON.parse(this.dataset.metaData);
    }

    this.#state = State.Ready;

    return true;
  }

  #rebuildElement(): void {
    switch (this.#state) {
      case State.Uploading:
        this.#replaceWithIcon("spinner");
        break;

      case State.GeneratingThumbnails:
        this.#replaceWithIcon("spinner");
        break;

      case State.Ready:
        if (this.previewUrl) {
          this.#replaceWithImage(this.previewUrl);
        } else {
          const iconName = this.iconName || "file";
          this.#replaceWithIcon(iconName);
        }
        break;

      case State.Failed:
        this.#replaceWithIcon("times");
        break;

      default:
        throw new Error("Unreachable", {
          cause: {
            state: this.#state,
          },
        });
    }
  }

  #replaceWithImage(src: string): void {
    let img = this.querySelector("img");

    if (img === null) {
      this.innerHTML = "";

      img = document.createElement("img");
      img.alt = "";
      this.append(img);
    }

    img.src = src;

    if (this.unbounded) {
      img.removeAttribute("height");
      img.removeAttribute("width");
    } else {
      img.height = 64;
      img.width = 64;
    }
  }

  #replaceWithIcon(iconName: string): FaIcon {
    let icon = this.querySelector("fa-icon");
    if (icon === null) {
      this.innerHTML = "";

      icon = document.createElement("fa-icon");
      icon.size = 64;
      icon.setIcon(iconName);
      this.append(icon);
    } else {
      icon.setIcon(iconName);
    }

    return icon;
  }

  get fileId(): number | undefined {
    return this.#fileId;
  }

  get iconName(): string | undefined {
    return this.dataset.iconName;
  }

  get previewUrl(): string | undefined {
    return this.dataset.previewUrl;
  }

  get unbounded(): boolean {
    return this.getAttribute("dimensions") === "unbounded";
  }

  set unbounded(unbounded: boolean) {
    if (unbounded) {
      this.setAttribute("dimensions", "unbounded");
    } else {
      this.removeAttribute("dimensions");
    }

    this.#rebuildElement();
  }

  get filename(): string | undefined {
    return this.#filename;
  }

  get mimeType(): string | undefined {
    return this.#mimeType;
  }

  get data(): Record<string, unknown> | undefined {
    return this.#data;
  }

  get link(): string | undefined {
    return this.#link;
  }

  isImage(): boolean {
    if (this.mimeType === undefined) {
      return false;
    }

    switch (this.mimeType) {
      case "image/gif":
      case "image/jpeg":
      case "image/png":
      case "image/webp":
        return true;

      default:
        return false;
    }
  }

  uploadFailed(): void {
    if (this.#state !== State.Uploading) {
      return;
    }

    this.#state = State.Failed;
    this.#rebuildElement();

    this.#readyReject();
  }

  uploadCompleted(
    fileId: number,
    mimeType: string,
    link: string,
    data: Record<string, unknown>,
    hasThumbnails: boolean,
  ): void {
    if (this.#state === State.Uploading) {
      this.#data = data;
      this.#fileId = fileId;
      this.#link = link;
      this.#mimeType = mimeType;
      this.setAttribute("file-id", fileId.toString());

      if (hasThumbnails) {
        this.#state = State.GeneratingThumbnails;
        this.#rebuildElement();
      } else {
        this.#state = State.Ready;
        this.#rebuildElement();

        this.#readyResolve();
      }
    }
  }

  setThumbnails(thumbnails: ThumbnailData[]): void {
    if (this.#state !== State.GeneratingThumbnails) {
      return;
    }

    for (const thumbnail of thumbnails) {
      this.#thumbnails.push(new Thumbnail(thumbnail.identifier, thumbnail.link));
    }

    this.#state = State.Ready;
    this.#rebuildElement();

    this.#readyResolve();
  }

  set thumbnail(thumbnail: Thumbnail) {
    if (!this.#thumbnails.includes(thumbnail)) {
      return;
    }

    this.#replaceWithImage(thumbnail.link);
  }

  get thumbnails(): Thumbnail[] {
    return [...this.#thumbnails];
  }

  get ready(): Promise<void> {
    return this.#readyPromise;
  }
}

export default WoltlabCoreFileElement;

window.customElements.define("woltlab-core-file", WoltlabCoreFileElement);
