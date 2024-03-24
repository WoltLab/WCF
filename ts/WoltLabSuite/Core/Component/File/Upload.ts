import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { upload as filesUpload } from "WoltLabSuite/Core/Api/Files/Upload";
import WoltlabCoreFileElement from "./woltlab-core-file";
import { Response as UploadChunkResponse, uploadChunk } from "WoltLabSuite/Core/Api/Files/Chunk/Chunk";
import { generateThumbnails } from "WoltLabSuite/Core/Api/Files/GenerateThumbnails";

export type ThumbnailsGenerated = {
  data: GenerateThumbnailsResponse;
  fileID: number;
};

type ThumbnailData = {
  identifier: string;
  link: string;
};

type GenerateThumbnailsResponse = ThumbnailData[];

async function upload(element: WoltlabCoreFileUploadElement, file: File): Promise<void> {
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
    return;
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

export function setup(): void {
  wheneverFirstSeen("woltlab-core-file-upload", (element) => {
    element.addEventListener("upload", (event: CustomEvent<File>) => {
      void upload(element, event.detail);
    });
  });
}
