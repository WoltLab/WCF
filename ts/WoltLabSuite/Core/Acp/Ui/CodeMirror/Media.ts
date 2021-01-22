import { Media, MediaInsertType } from "../../../Media/Data";
import MediaManagerEditor from "../../../Media/Manager/Editor";
import * as Core from "../../../Core";

class AcpUiCodeMirrorMedia {
  protected readonly element: HTMLElement;

  constructor(elementId: string) {
    this.element = document.getElementById(elementId) as HTMLElement;

    const button = document.getElementById(`codemirror-${elementId}-media`)!;
    button.classList.add(button.id);

    new MediaManagerEditor({
      buttonClass: button.id,
      callbackInsert: (media, insertType, thumbnailSize) => this.insert(media, insertType, thumbnailSize),
    });
  }

  protected insert(mediaList: Map<number, Media>, insertType: MediaInsertType, thumbnailSize: string): void {
    switch (insertType) {
      case MediaInsertType.Separate: {
        const content = Array.from(mediaList.values())
          .map((item) => `{{ media="${item.mediaID}" size="${thumbnailSize}" }}`)
          .join("");

        (this.element as any).codemirror.replaceSelection(content);
      }
    }
  }
}

Core.enableLegacyInheritance(AcpUiCodeMirrorMedia);

export = AcpUiCodeMirrorMedia;
