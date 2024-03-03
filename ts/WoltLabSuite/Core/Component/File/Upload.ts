import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { StatusNotOk } from "WoltLabSuite/Core/Ajax/Error";
import { isPlainObject } from "WoltLabSuite/Core/Core";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import WoltlabCoreFileElement from "./woltlab-core-file";

type PreflightResponse = {
  endpoints: string[];
};

type UploadResponse =
  | { completed: false }
  | ({
      completed: true;
    } & UploadCompleted);

export type UploadCompleted = {
  endpointThumbnails: string;
  fileID: number;
  typeName: string;
  mimeType: string;
  link: string;
  data: Record<string, unknown>;
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

async function upload(element: WoltlabCoreFileUploadElement, file: File): Promise<void> {
  const typeName = element.dataset.typeName!;

  const fileHash = await getSha256Hash(await file.arrayBuffer());

  const fileElement = document.createElement("woltlab-core-file");
  fileElement.dataset.filename = file.name;

  const event = new CustomEvent<WoltlabCoreFileElement>("uploadStart", { detail: fileElement });
  element.dispatchEvent(event);

  let response: PreflightResponse | undefined = undefined;
  try {
    response = (await prepareRequest(element.dataset.endpoint!)
      .post({
        filename: file.name,
        fileSize: file.size,
        fileHash,
        typeName,
        context: element.dataset.context,
      })
      .fetchAsJson()) as PreflightResponse;
  } catch (e) {
    if (e instanceof StatusNotOk) {
      const body = await e.response.clone().json();
      if (isPlainObject(body) && isPlainObject(body.error)) {
        console.log(body);
        return;
      } else {
        throw e;
      }
    } else {
      throw e;
    }
  } finally {
    if (response === undefined) {
      fileElement.uploadFailed();
    }
  }

  const { endpoints } = response;

  const chunkSize = Math.ceil(file.size / endpoints.length);

  // TODO: Can we somehow report any meaningful upload progress?

  for (let i = 0, length = endpoints.length; i < length; i++) {
    const start = i * chunkSize;
    const end = start + chunkSize;
    const chunk = file.slice(start, end);

    const endpoint = new URL(endpoints[i]);

    const checksum = await getSha256Hash(await chunk.arrayBuffer());
    endpoint.searchParams.append("checksum", checksum);

    let response: UploadResponse;
    try {
      response = (await prepareRequest(endpoint.toString()).post(chunk).fetchAsJson()) as UploadResponse;
    } catch (e) {
      // TODO: Handle errors
      console.error(e);

      fileElement.uploadFailed();
      throw e;
    }

    await chunkUploadCompleted(fileElement, response);
  }
}

async function chunkUploadCompleted(fileElement: WoltlabCoreFileElement, response: UploadResponse): Promise<void> {
  if (!response.completed) {
    return;
  }

  const hasThumbnails = response.endpointThumbnails !== "";
  fileElement.uploadCompleted(response.fileID, response.mimeType, response.link, response.data, hasThumbnails);

  if (hasThumbnails) {
    await generateThumbnails(fileElement, response.endpointThumbnails);
  }
}

async function generateThumbnails(fileElement: WoltlabCoreFileElement, endpoint: string): Promise<void> {
  let response: GenerateThumbnailsResponse;

  try {
    response = (await prepareRequest(endpoint).get().fetchAsJson()) as GenerateThumbnailsResponse;
  } catch (e) {
    // TODO: Handle errors
    console.error(e);
    throw e;
  }

  fileElement.setThumbnails(response);
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
