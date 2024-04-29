import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { upload as filesUpload } from "WoltLabSuite/Core/Api/Files/Upload";
import WoltlabCoreFileElement from "./woltlab-core-file";
import {
  ResponseCompleted,
  Response as UploadChunkResponse,
  uploadChunk,
} from "WoltLabSuite/Core/Api/Files/Chunk/Chunk";
import { generateThumbnails } from "WoltLabSuite/Core/Api/Files/GenerateThumbnails";
import ImageResizer from "WoltLabSuite/Core/Image/Resizer";
import { AttachmentData } from "../Ckeditor/Attachment";

export type CkeditorDropEvent = {
  file: File;
  promise?: Promise<unknown>;
};

export type ThumbnailsGenerated = {
  data: GenerateThumbnailsResponse;
  fileID: number;
};

type ThumbnailData = {
  identifier: string;
  link: string;
};

type GenerateThumbnailsResponse = ThumbnailData[];

type ResizeConfiguration = {
  maxWidth: number;
  maxHeight: number;
  fileType: "image/jpeg" | "image/webp" | "keep";
  quality: number;
};

async function upload(element: WoltlabCoreFileUploadElement, file: File): Promise<ResponseCompleted | undefined> {
  const typeName = element.dataset.typeName!;

  const fileHash = await getSha256Hash(await file.arrayBuffer());

  const fileElement = document.createElement("woltlab-core-file");
  fileElement.dataset.filename = file.name;

  const event = new CustomEvent<WoltlabCoreFileElement>("uploadStart", { detail: fileElement });
  element.dispatchEvent(event);

  const response = await filesUpload(file.name, file.size, fileHash, typeName, element.dataset.context || "");
  if (!response.ok) {
    const validationError = response.error.getValidationError();
    if (validationError === undefined) {
      fileElement.uploadFailed();

      throw response.error;
    }

    console.log(validationError);
    return undefined;
  }

  const { identifier, numberOfChunks } = response.value;

  const chunkSize = Math.ceil(file.size / numberOfChunks);

  // TODO: Can we somehow report any meaningful upload progress?

  for (let i = 0; i < numberOfChunks; i++) {
    const start = i * chunkSize;
    const end = start + chunkSize;
    const chunk = file.slice(start, end);

    const checksum = await getSha256Hash(await chunk.arrayBuffer());

    const response = await uploadChunk(identifier, i, checksum, chunk);
    if (!response.ok) {
      fileElement.uploadFailed();

      throw response.error;
    }

    await chunkUploadCompleted(fileElement, response.value);

    if (response.value.completed) {
      return response.value;
    }
  }
}

async function chunkUploadCompleted(fileElement: WoltlabCoreFileElement, result: UploadChunkResponse): Promise<void> {
  if (!result.completed) {
    return;
  }

  fileElement.uploadCompleted(result.fileID, result.mimeType, result.link, result.data, result.generateThumbnails);

  if (result.generateThumbnails) {
    const response = await generateThumbnails(result.fileID);
    fileElement.setThumbnails(response.unwrap());
  }
}

async function getSha256Hash(data: BufferSource): Promise<string> {
  const buffer = await window.crypto.subtle.digest("SHA-256", data);

  return Array.from(new Uint8Array(buffer))
    .map((b) => b.toString(16).padStart(2, "0"))
    .join("");
}

function clearPreviousErrors(element: WoltlabCoreFileUploadElement): void {
  element.parentElement?.querySelectorAll(".innerError").forEach((x) => x.remove());
}

async function resizeImage(element: WoltlabCoreFileUploadElement, file: File): Promise<File> {
  switch (file.type) {
    case "image/jpeg":
    case "image/png":
    case "image/webp":
      // Potential candidate for a resize operation.
      break;

    default:
      // Not an image or an unsupported file type.
      return file;
  }

  const timeout = new Promise<File>((resolve) => {
    window.setTimeout(() => resolve(file), 10_000);
  });

  const resizeConfiguration = JSON.parse(element.dataset.resizeConfiguration!) as ResizeConfiguration;

  const resizer = new ImageResizer();
  const { image, exif } = await resizer.loadFile(file);

  const maxHeight = resizeConfiguration.maxHeight;
  let maxWidth = resizeConfiguration.maxWidth;
  if (window.devicePixelRatio >= 2) {
    const actualWidth = window.screen.width * window.devicePixelRatio;
    const actualHeight = window.screen.height * window.devicePixelRatio;

    // If the dimensions are equal then this is a screenshot from a HiDPI
    // device, thus we downscale this to the “natural” dimensions.
    if (actualWidth === image.width && actualHeight === image.height) {
      maxWidth = Math.min(maxWidth, window.screen.width);
    }
  }

  const canvas = await resizer.resize(image, maxWidth, maxHeight, resizeConfiguration.quality, true, timeout);
  if (canvas === undefined) {
    // The resize operation failed, timed out or there was no need to perform
    // any scaling whatsoever.
    return file;
  }

  let fileType: string = resizeConfiguration.fileType;
  if (fileType === "image/jpeg" || fileType === "image/webp") {
    fileType = "image/webp";
  } else {
    fileType = file.type;
  }

  const resizedFile = await resizer.saveFile(
    {
      exif,
      image: canvas,
    },
    file.name,
    fileType,
    resizeConfiguration.quality,
  );

  return resizedFile;
}

function validateFile(element: WoltlabCoreFileUploadElement, file: File): boolean {
  const fileExtensions = (element.dataset.fileExtensions || "*").split(",");
  for (const fileExtension of fileExtensions) {
    if (fileExtension === "*") {
      return true;
    } else if (file.name.endsWith(fileExtension)) {
      return true;
    }
  }

  // TODO: show an error message

  return false;
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-file-upload", (element: WoltlabCoreFileUploadElement) => {
    element.addEventListener("upload", (event: CustomEvent<File>) => {
      const file = event.detail;

      clearPreviousErrors(element);

      if (!validateFile(element, file)) {
        return;
      }

      void resizeImage(element, file).then((resizedFile) => {
        void upload(element, resizedFile);
      });
    });

    element.addEventListener("ckeditorDrop", (event: CustomEvent<CkeditorDropEvent>) => {
      const { file } = event.detail;

      let promiseResolve: (data: AttachmentData) => void;
      let promiseReject: () => void;
      event.detail.promise = new Promise<AttachmentData>((resolve, reject) => {
        promiseResolve = resolve;
        promiseReject = reject;
      });

      clearPreviousErrors(element);

      if (!validateFile(element, file)) {
        promiseReject!();

        return;
      }

      void resizeImage(element, file).then(async (resizeFile) => {
        try {
          const data = await upload(element, resizeFile);
          if (data === undefined || typeof data.data.attachmentID !== "number") {
            promiseReject();
          } else {
            const attachmentData: AttachmentData = {
              attachmentId: data.data.attachmentID,
              url: data.link,
            };

            promiseResolve(attachmentData);
          }
        } catch (e) {
          promiseReject();

          throw e;
        }
      });
    });
  });
}
