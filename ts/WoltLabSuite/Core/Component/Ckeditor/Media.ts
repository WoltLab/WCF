import { listenToCkeditor } from "./Event";

type UploadResult = {
  [key: string]: unknown;
  urls: {
    default: string;
  };
};

type MediaData = {
  mediaId: number;
  mediaSize: string;
  url: string;
};

export type MediaDragAndDropEventData = {
  abortController?: AbortController;
  file: File;
  promise?: Promise<MediaData>;
};

function uploadMedia(element: HTMLElement, file: File, abortController?: AbortController): Promise<UploadResult> {
  const data: MediaDragAndDropEventData = { abortController, file };

  element.dispatchEvent(
    new CustomEvent<MediaDragAndDropEventData>("ckeditor5:drop", {
      detail: data,
    }),
  );

  // The media system works differently compared to the
  // attachments, because uploading a file will offer
  // the user to insert the content in different formats.
  //
  // Rejecting the upload promise will cause CKEditor to
  // stop caring about the file so that we regain control.
  return Promise.reject();
}

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).configuration(({ configuration, features }) => {
    if (features.attachment || !features.media) {
      return;
    }

    // TODO: The typings do not include our custom plugins yet.
    (configuration as any).woltlabUpload = {
      uploadImage: (file: File, abortController: AbortController) => uploadMedia(element, file, abortController),
      uploadOther: (file: File) => uploadMedia(element, file),
    };

    listenToCkeditor(element).ready((ckeditor) => {
      void import("../../Media/Manager/Editor").then(({ MediaManagerEditor }) => {
        new MediaManagerEditor({
          ckeditor,
        });
      });
    });
  });
}
