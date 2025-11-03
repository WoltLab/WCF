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
import { innerError } from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { createSHA256 } from "hash-wasm";
import { cropImage, CropperConfiguration } from "WoltLabSuite/Core/Component/Image/Cropper";
import { Exif, getExifBytesFromJpeg, getExifBytesFromWebP } from "WoltLabSuite/Core/Image/ExifUtil";

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

const BUFFER_SIZE = 10 * 1_024 * 1_024;

async function upload(
  element: WoltlabCoreFileUploadElement,
  file: File,
  fileHash: string,
  exifData: Exif | null,
): Promise<ResponseCompleted | undefined> {
  const objectType = element.dataset.objectType!;

  const fileElement = document.createElement("woltlab-core-file");
  fileElement.dataset.filename = file.name;
  fileElement.dataset.fileSize = file.size.toString();

  const event = new CustomEvent<WoltlabCoreFileElement>("uploadStart", { detail: fileElement });
  element.dispatchEvent(event);

  const response = await filesUpload(
    file.name,
    file.size,
    fileHash,
    objectType,
    element.dataset.context || "",
    exifData,
  );
  if (!response.ok) {
    const validationError = response.error.getValidationError();
    if (validationError === undefined) {
      fileElement.uploadFailed(response.error);

      throw new Error("Unexpected validation error", { cause: response.error });
    }

    fileElement.uploadFailed(response.error);
    return undefined;
  }

  const { identifier, numberOfChunks } = response.value;

  const chunkSize = Math.ceil(file.size / numberOfChunks);

  notifyChunkProgress(fileElement, 0, numberOfChunks);

  for (let i = 0; i < numberOfChunks; i++) {
    const start = i * chunkSize;
    const end = start + chunkSize;
    const chunk = file.slice(start, end);

    const checksum = await getSha256Hash(chunk);

    const response = await uploadChunk(identifier, i, checksum, chunk);
    if (!response.ok) {
      fileElement.uploadFailed(response.error);

      throw new Error("Unexpected validation error", { cause: response.error });
    }

    notifyChunkProgress(fileElement, i + 1, numberOfChunks);

    await chunkUploadCompleted(fileElement, response.value);

    if (response.value.completed) {
      return response.value;
    }
  }
}

function notifyChunkProgress(element: WoltlabCoreFileElement, currentChunk: number, numberOfChunks: number): void {
  // Suppress the progress bar for uploads that are processed in a single
  // request, because we cannot track the upload progress within a chunk.
  if (numberOfChunks === 1) {
    return;
  }

  const event = new CustomEvent<number>("uploadProgress", {
    detail: Math.floor((currentChunk / numberOfChunks) * 100),
  });
  element.dispatchEvent(event);
}

async function chunkUploadCompleted(fileElement: WoltlabCoreFileElement, result: UploadChunkResponse): Promise<void> {
  if (!result.completed) {
    return;
  }

  fileElement.uploadCompleted(result.fileID, result.mimeType, result.link, result.data, result.generateThumbnails);

  if (result.generateThumbnails) {
    const { filename, fileSize, mimeType, thumbnails } = (await generateThumbnails(result.fileID)).unwrap();
    fileElement.setThumbnails(thumbnails);
    fileElement.updateFileData(filename, fileSize, mimeType);
  }
}

async function getSha256Hash(data: Blob): Promise<string> {
  const sha256 = await createSHA256();
  sha256.init();

  let offset = 0;

  while (offset < data.size) {
    const chunk = data.slice(offset, offset + BUFFER_SIZE);
    const buffer = await chunk.arrayBuffer();
    sha256.update(new Uint8Array(buffer));
    offset += BUFFER_SIZE;
  }

  return sha256.digest("hex");
}

export function clearPreviousErrors(element: WoltlabCoreFileUploadElement): void {
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
  const { image } = await resizer.loadFile(file);

  const maxHeight = resizeConfiguration.maxHeight === -1 ? image.height : resizeConfiguration.maxHeight;
  let maxWidth = resizeConfiguration.maxWidth === -1 ? image.width : resizeConfiguration.maxWidth;
  if (window.devicePixelRatio >= 2) {
    const actualWidth = window.screen.width * window.devicePixelRatio;
    const actualHeight = window.screen.height * window.devicePixelRatio;

    // If the dimensions are equal then this is a screenshot from a HiDPI
    // device, thus we downscale this to the “natural” dimensions.
    if (actualWidth === image.width && actualHeight === image.height) {
      maxWidth = Math.min(maxWidth, window.screen.width);
    }
  }

  const canvas = await resizer.resize(image, maxWidth, maxHeight, resizeConfiguration.quality, false, timeout);
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
      image: canvas,
    },
    file.name,
    fileType,
    resizeConfiguration.quality,
  );

  return resizedFile;
}

function validateFileLimit(element: WoltlabCoreFileUploadElement, count: number): boolean {
  const maximumCount = element.maximumCount;
  if (maximumCount === -1) {
    return true;
  }

  const files = Array.from(element.parentElement!.querySelectorAll("woltlab-core-file"));
  const numberOfUploadedFiles = files.filter((file) => !file.isFailedUpload()).length;
  if (numberOfUploadedFiles + count <= maximumCount) {
    return true;
  }

  reportError(element, null, getPhrase("wcf.upload.error.maximumCountReached", { maximumCount }));

  return false;
}

function validateFileSize(element: WoltlabCoreFileUploadElement, file: File): boolean {
  if (file.size === 0) {
    reportError(element, file, getPhrase("wcf.upload.error.emptyFile", { filename: file.name }));

    return false;
  }

  let isImage = false;
  switch (file.type) {
    case "image/gif":
    case "image/jpeg":
    case "image/png":
    case "image/webp":
      isImage = true;
      break;
  }

  // Skip the file size validation for images, they can potentially be resized.
  if (isImage) {
    return true;
  }

  const maximumSize = element.maximumSize;
  if (maximumSize === -1) {
    return true;
  }

  if (file.size <= maximumSize) {
    return true;
  }

  reportError(element, file, getPhrase("wcf.upload.error.fileSizeTooLarge", { filename: file.name }));

  return false;
}

function validateFileExtension(element: WoltlabCoreFileUploadElement, file: File): boolean {
  const fileExtensions = (element.dataset.fileExtensions || "*").toLowerCase().split(",");
  for (const fileExtension of fileExtensions) {
    if (fileExtension === "*") {
      return true;
    } else if (file.name.toLowerCase().endsWith(fileExtension)) {
      return true;
    }
  }

  reportError(element, file, getPhrase("wcf.upload.error.fileExtensionNotPermitted", { filename: file.name }));

  return false;
}

function reportError(element: WoltlabCoreFileUploadElement, file: File | null, message: string): void {
  const event = new CustomEvent<{ file: File | null; message: string }>("upload:error", {
    cancelable: true,
    detail: {
      file,
      message,
    },
  });
  element.dispatchEvent(event);

  if (event.defaultPrevented) {
    return;
  }

  innerError(element, message);
}

async function getExifBytes(file: File): Promise<Exif | null> {
  if (file.type === "image/jpeg") {
    try {
      const bytes = await getExifBytesFromJpeg(file);

      // ExifUtil returns the entire section but we only need the app data.
      // Removing the first 10 bytes drops the 0xFF 0xE1 marker followed by two
      // bytes for the length and then 6 bytes for the "Exif\x00\x00" header.
      return bytes.slice(10);
    } catch {
      return null;
    }
  } else if (file.type === "image/webp") {
    try {
      return await getExifBytesFromWebP(file);
    } catch {
      return null;
    }
  }

  return null;
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-file-upload", (element: WoltlabCoreFileUploadElement) => {
    element.addEventListener("upload:files", (event: CustomEvent<{ files: File[] }>) => {
      const { files } = event.detail;

      clearPreviousErrors(element);

      if (!validateFileLimit(element, files.length)) {
        return;
      }

      for (const file of files) {
        if (!validateFileExtension(element, file)) {
          return;
        } else if (!validateFileSize(element, file)) {
          return;
        }
      }

      element.markAsBusy();

      const exifData = new Map<File, Exif | null>();

      let processImage: (file: File) => Promise<File>;
      if (element.dataset.cropperConfiguration) {
        const cropperConfiguration = JSON.parse(element.dataset.cropperConfiguration) as CropperConfiguration;

        processImage = async (file) => {
          exifData.set(file, await getExifBytes(file));

          try {
            return await cropImage(element, file, cropperConfiguration);
          } catch (e) {
            element.dispatchEvent(new CustomEvent("cancel"));

            throw e;
          }
        };
      } else {
        processImage = async (file) => {
          exifData.set(file, await getExifBytes(file));

          return resizeImage(element, file);
        };
      }

      // Resize all files in parallel but keep the original order. This ensures
      // that files are uploaded in the same order that they were provided by
      // the browser.
      void Promise.allSettled(files.map((file) => processImage(file)))
        .then(async (results) => {
          const validFiles: File[] = [];
          for (let i = 0, length = results.length; i < length; i++) {
            const result = results[i];

            if (result.status === "fulfilled") {
              validFiles.push(result.value);
            } else if (result.reason !== undefined) {
              let message: string;
              if (result.reason instanceof Error) {
                message = result.reason.message;
              } else if (typeof result.reason === "string") {
                message = result.reason;
              } else {
                message = getPhrase("wcf.upload.error.damagedImageFile", { filename: files[i].name });
              }

              reportError(element, files[i], message);
            }
          }

          const checksums = await Promise.allSettled(validFiles.map((file) => getSha256Hash(file)));

          for (let i = 0, length = checksums.length; i < length; i++) {
            const result = checksums[i];

            if (result.status === "fulfilled") {
              const exif = exifData.get(validFiles[i]) || exifData.get(files[i]) || null;
              void upload(element, validFiles[i], result.value, exif);
            } else {
              throw new Error(result.reason);
            }
          }
        })
        .finally(() => {
          element.markAsReady();
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

      if (!validateFileLimit(element, 1)) {
        promiseReject!();

        return;
      }

      if (!validateFileExtension(element, file)) {
        promiseReject!();

        return;
      }

      let exifData: Exif | null;
      void getExifBytes(file)
        .then((exif) => {
          exifData = exif;
        })
        .then(() => resizeImage(element, file))
        .then(async (resizeFile) => {
          try {
            const checksum = await getSha256Hash(resizeFile);
            const data = await upload(element, resizeFile, checksum, exifData);
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
