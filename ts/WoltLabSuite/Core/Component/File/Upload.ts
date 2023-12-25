import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";

async function upload(element: WoltlabCoreFileUploadElement, file: File): Promise<void> {
  const chunkSize = 2_000_000;
  const chunks = Math.ceil(file.size / chunkSize);

  for (let i = 0; i < chunks; i++) {
    const chunk = file.slice(i * chunkSize, i * chunkSize + chunkSize + 1);

    const response = await prepareRequest(element.dataset.endpoint!).post(chunk).fetchAsResponse();
    console.log(response);
  }
}

export function setup(): void {
  wheneverFirstSeen("woltlab-core-file-upload", (element) => {
    element.addEventListener("upload", (event: CustomEvent<File>) => {
      void upload(element, event.detail);
    });
  });
}
