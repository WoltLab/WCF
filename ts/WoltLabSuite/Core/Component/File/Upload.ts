import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";

type PreflightResponse = {
  endpoints: string[];
};

async function upload(element: WoltlabCoreFileUploadElement, file: File): Promise<void> {
  const fileHash = await getSha256Hash(await file.arrayBuffer());

  const response = (await prepareRequest(element.dataset.endpoint!)
    .post({
      filename: file.name,
      fileSize: file.size,
      fileHash,
    })
    .fetchAsJson()) as PreflightResponse;
  const { endpoints } = response;

  const chunkSize = Math.ceil(file.size / endpoints.length);

  for (let i = 0, length = endpoints.length; i < length; i++) {
    const start = i * chunkSize;
    const end = start + chunkSize;
    const chunk = file.slice(start, end);

    const endpoint = new URL(endpoints[i]);

    const checksum = await getSha256Hash(await chunk.arrayBuffer());
    endpoint.searchParams.append("checksum", checksum);

    await prepareRequest(endpoint.toString()).post(chunk).fetchAsResponse();
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

    const file = new File(["a".repeat(4_000_001)], "test.txt");
    void upload(element, file);
  });
}
