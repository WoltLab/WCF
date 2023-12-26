import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";

type PreflightResponse = {
  endpoints: string[];
};

async function upload(element: WoltlabCoreFileUploadElement, file: File): Promise<void> {
  const response = (await prepareRequest(element.dataset.endpoint!)
    .post({
      filename: file.name,
      filesize: file.size,
    })
    .fetchAsJson()) as PreflightResponse;
  const { endpoints } = response;

  const chunkSize = 2_000_000;
  const chunks = Math.ceil(file.size / chunkSize);

  const arrayBufferToHex = (buffer: ArrayBuffer): string => {
    return Array.from(new Uint8Array(buffer))
      .map((b) => b.toString(16).padStart(2, "0"))
      .join("");
  };

  const hash = await window.crypto.subtle.digest("SHA-256", await file.arrayBuffer());
  console.log("checksum for the entire file is:", arrayBufferToHex(hash));

  const data: Blob[] = [];
  for (let i = 0; i < chunks; i++) {
    const start = i * chunkSize;
    const end = start + chunkSize;
    const chunk = file.slice(start, end);
    data.push(chunk);

    console.log("Uploading", start, "to", end, " (total: " + chunk.size + " of " + file.size + ")");

    await prepareRequest(endpoints[i]).post(chunk).fetchAsResponse();
  }

  const uploadedChunks = new Blob(data);
  const uploadedHash = await window.crypto.subtle.digest("SHA-256", await uploadedChunks.arrayBuffer());
  console.log("checksum for the entire file is:", arrayBufferToHex(uploadedHash));
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
