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

  for (let i = 0; i < chunks; i++) {
    const start = i * chunkSize;
    const end = start + chunkSize;
    const chunk = file.slice(start, end);

    await prepareRequest(endpoints[i]).post(chunk).fetchAsResponse();
  }
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-file-upload", (element) => {
    element.addEventListener("upload", (event: CustomEvent<File>) => {
      void upload(element, event.detail);
    });
  });
}
