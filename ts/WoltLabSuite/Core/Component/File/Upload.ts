import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { upload as filesUpload } from "WoltLabSuite/Core/Api/Files/Upload";
import WoltlabCoreFileElement from "./woltlab-core-file";
import {
  ResponseCompleted,
  Response as UploadChunkResponse,
  uploadChunk,
} from "WoltLabSuite/Core/Api/Files/Chunk/Chunk";
import { generateThumbnails } from "WoltLabSuite/Core/Api/Files/GenerateThumbnails";
import { abortUpload } from "WoltLabSuite/Core/Api/Files/AbortUpload";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";
import type { ApiResult } from "WoltLabSuite/Core/Api/Result";
import { getUploaderToken } from "WoltLabSuite/Core/Component/File/Helper";
import ImageResizer from "WoltLabSuite/Core/Image/Resizer";
import { AttachmentData } from "../Ckeditor/Attachment";
import { innerError } from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { createSHA256 } from "hash-wasm";
import { cropImage, CropperConfiguration } from "WoltLabSuite/Core/Component/Image/Cropper";
import {
  Exif,
  getExifBytesFromJpeg,
  getExifBytesFromWebP,
  getTiffFromJpegSegments,
} from "WoltLabSuite/Core/Image/ExifUtil";

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
  ignoreExifRotation: boolean,
  sourceFile: File,
): Promise<ResponseCompleted | undefined> {
  const fileElement = document.createElement("woltlab-core-file");
  fileElement.dataset.filename = file.name;
  fileElement.dataset.fileSize = file.size.toString();
  fileElement.setSourceFile(sourceFile);

  const event = new CustomEvent<WoltlabCoreFileElement>("uploadStart", { detail: fileElement });
  element.dispatchEvent(event);

  try {
    return await transferFile(element, fileElement, file, fileHash, exifData, ignoreExifRotation);
  } catch (e) {
    // Settles the element for failures without a response from the server,
    // such as connection errors, which are reported by the global handler.
    if (fileElement.abortSignal.aborted) {
      fileElement.uploadAborted();
    } else {
      fileElement.uploadFailed();
    }

    throw e;
  }
}

async function transferFile(
  element: WoltlabCoreFileUploadElement,
  fileElement: WoltlabCoreFileElement,
  file: File,
  fileHash: string,
  exifData: Exif | null,
  ignoreExifRotation: boolean,
): Promise<ResponseCompleted | undefined> {
  const signal = fileElement.abortSignal;

  // This request is never aborted, the server could create the temporary
  // upload without the client ever learning about its identifier.
  const response = await filesUpload(
    file.name,
    file.size,
    fileHash,
    element.dataset.objectType!,
    element.dataset.context || "",
    exifData,
    ignoreExifRotation,
  );
  if (!response.ok) {
    if (signal.aborted) {
      fileElement.uploadAborted();
      return undefined;
    }

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

    let checksum: string;
    try {
      checksum = await getSha256Hash(chunk);
    } catch (e) {
      await discardFailedUpload(identifier);

      throw e;
    }

    if (signal.aborted) {
      await discardUpload(fileElement, identifier);
      return undefined;
    }

    // The final chunk completes the upload. Aborting its request could leave
    // a file behind that the client never learns about.
    const isFinalChunk = i === numberOfChunks - 1;

    let response: ApiResult<UploadChunkResponse>;
    try {
      response = await uploadChunk(identifier, i, checksum, chunk, isFinalChunk ? undefined : signal);
    } catch (e) {
      if (signal.aborted) {
        await discardUpload(fileElement, identifier);
        return undefined;
      }

      // If the request of the final chunk reached the server, the upload may
      // have been completed, which makes this a no-op.
      await discardFailedUpload(identifier);

      throw e;
    }

    if (!response.ok) {
      // The server deletes the temporary upload before it validates the
      // complete file, a rejection leaves nothing behind to discard.
      const isRejected = response.error.getValidationError()?.param === "validation";

      if (signal.aborted) {
        if (isRejected) {
          fileElement.uploadAborted();
        } else {
          await discardUpload(fileElement, identifier);
        }

        return undefined;
      }

      if (isRejected) {
        fileElement.uploadFailed(response.error);
        return undefined;
      }

      await discardFailedUpload(identifier);
      fileElement.uploadFailed(response.error);

      throw new Error("Unexpected validation error", { cause: response.error });
    }

    notifyChunkProgress(fileElement, i + 1, numberOfChunks);

    if (response.value.completed) {
      return completeUpload(fileElement, response.value, getUploaderToken(element));
    }
  }

  if (signal.aborted) {
    await discardUpload(fileElement, identifier);
    return undefined;
  }

  await discardFailedUpload(identifier);

  throw new Error("The server did not complete the upload after the final chunk.");
}

async function discardUpload(fileElement: WoltlabCoreFileElement, identifier: string): Promise<void> {
  try {
    // An unknown identifier is not an error, there is nothing left to discard.
    await abortUpload(identifier);
  } finally {
    fileElement.uploadAborted();
  }
}

/**
 * Discards the temporary upload after a chunk failed, because the upload
 * cannot be resumed. This is best effort, a temporary upload that is left
 * behind is pruned after 24 hours.
 */
async function discardFailedUpload(identifier: string): Promise<void> {
  try {
    await abortUpload(identifier);
  } catch {
    // The caller reports the original error, which is most likely the cause.
  }
}

/**
 * Deletes the file of an upload that has been aborted after the final chunk
 * was sent. Returns false if the file has to be kept.
 */
async function discardFile(fileId: number, uploaderToken: string | undefined): Promise<boolean> {
  try {
    return (await deleteFile(fileId, uploaderToken)).ok;
  } catch {
    return false;
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

async function completeUpload(
  fileElement: WoltlabCoreFileElement,
  result: ResponseCompleted,
  uploaderToken: string | undefined,
): Promise<ResponseCompleted | undefined> {
  const signal = fileElement.abortSignal;

  if (signal.aborted && (await discardFile(result.fileID, uploaderToken))) {
    fileElement.uploadAborted();
    return undefined;
  }

  fileElement.uploadCompleted(result.fileID, result.mimeType, result.link, result.data, result.generateThumbnails);

  if (!result.generateThumbnails) {
    return result;
  }

  // This request is never aborted, because deleting the file while its
  // thumbnails are being generated would race with it.
  let response: Awaited<ReturnType<typeof generateThumbnails>>;
  try {
    response = await generateThumbnails(result.fileID, uploaderToken);
  } catch (e) {
    if (signal.aborted && (await discardFile(result.fileID, uploaderToken))) {
      fileElement.uploadAborted();
      return undefined;
    }

    fileElement.thumbnailGenerationFailed();

    throw e;
  }

  if (signal.aborted && (await discardFile(result.fileID, uploaderToken))) {
    fileElement.uploadAborted();
    return undefined;
  }

  if (!response.ok) {
    fileElement.thumbnailGenerationFailed();
  }

  const { filename, fileSize, mimeType, thumbnails } = response.unwrap();
  fileElement.setThumbnails(thumbnails);
  fileElement.updateFileData(filename, fileSize, mimeType);

  return result;
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

  let canvas: HTMLCanvasElement | undefined;
  try {
    canvas = await resizer.resize(image, maxWidth, maxHeight, resizeConfiguration.quality, false, timeout);
  } catch (e) {
    // The browser can read the dimensions of some damaged images but fails to decode them.
    if (e instanceof DOMException) {
      throw new Error(getPhrase("wcf.upload.error.damagedImageFile", { filename: file.name }), { cause: e });
    }

    throw e;
  }

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

function getProcessingErrorMessage(file: File, reason: unknown): string {
  if (reason instanceof Error) {
    return reason.message;
  } else if (typeof reason === "string") {
    return reason;
  }

  return getPhrase("wcf.upload.error.damagedImageFile", { filename: file.name });
}

async function getExifBytes(file: File): Promise<Exif | null> {
  if (file.type === "image/jpeg") {
    try {
      return getTiffFromJpegSegments(await getExifBytesFromJpeg(file));
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
      clearPreviousErrors(element);

      if (!validateFileLimit(element, event.detail.files.length)) {
        return;
      }

      const files = event.detail.files.filter((file) => {
        return validateFileExtension(element, file) && validateFileSize(element, file);
      });
      if (files.length === 0) {
        return;
      }

      element.markAsBusy();

      const exifData = new Map<File, Exif | null>();
      const ignoreExifRotation = new Set<File>();

      let processImage: (file: File) => Promise<File>;
      if (element.dataset.cropperConfiguration) {
        const cropperConfiguration = JSON.parse(element.dataset.cropperConfiguration) as CropperConfiguration;

        processImage = async (file) => {
          exifData.set(file, await getExifBytes(file));

          try {
            const croppedFile = await cropImage(element, file, cropperConfiguration);
            const croppedExifData = await getExifBytes(croppedFile);
            if (exifData.has(file) && (croppedExifData === null || croppedExifData.length === 0)) {
              // Cropping the image will implicitly normalized the rotation of
              // the image but the EXIF data will still report a rotation.
              ignoreExifRotation.add(file);
            }

            return croppedFile;
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
          const validFilesToFiles: number[] = [];
          for (let i = 0, length = results.length; i < length; i++) {
            const result = results[i];

            if (result.status === "fulfilled") {
              validFiles.push(result.value);
              validFilesToFiles.push(i);
            } else if (result.reason !== undefined) {
              reportError(element, files[i], getProcessingErrorMessage(files[i], result.reason));
            }
          }

          const checksums = await Promise.allSettled(validFiles.map((file) => getSha256Hash(file)));

          for (let i = 0, length = checksums.length; i < length; i++) {
            const result = checksums[i];
            const fileIndex = validFilesToFiles[i];

            if (result.status === "fulfilled") {
              const exif = exifData.get(validFiles[i]) || exifData.get(files[fileIndex]) || null;
              void upload(
                element,
                validFiles[i],
                result.value,
                exif,
                ignoreExifRotation.has(files[fileIndex]),
                files[fileIndex],
              );
            } else {
              reportError(element, files[fileIndex], getPhrase("wcf.upload.error.uploadFailed"));

              console.error(result.reason);
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
        .then(() =>
          resizeImage(element, file).catch((reason: unknown) => {
            reportError(element, file, getProcessingErrorMessage(file, reason));

            return undefined;
          }),
        )
        .then(async (resizeFile) => {
          if (resizeFile === undefined) {
            promiseReject();

            return;
          }

          try {
            const checksum = await getSha256Hash(resizeFile);
            const data = await upload(element, resizeFile, checksum, exifData, false, file);
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
        })
        .catch((e) => {
          promiseReject();

          throw e;
        });
    });

    element.pipelineAttached();
  });
}
