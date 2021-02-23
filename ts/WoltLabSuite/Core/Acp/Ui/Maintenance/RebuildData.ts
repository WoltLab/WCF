import Worker from "../Worker";
import * as Language from "../../../Language";

const workers = new Map<HTMLElement, number>();

export function register(button: HTMLElement): void {
  if (!button.dataset.className) {
    throw new Error(`Missing 'data-class-name' attribute.`);
  }

  workers.set(button, parseInt(button.dataset.nicevalue!, 10));

  button.addEventListener("click", function (event) {
    event.preventDefault();

    void runWorker(button);
  });
}

export async function runAllWorkers(): Promise<void> {
  const sorted = Array.from(workers)
    .sort(([, a], [, b]) => {
      return a - b;
    })
    .map(([el]) => el);

  let i = 1;
  for (const worker of sorted) {
    await runWorker(worker, `${worker.textContent!} (${i++} / ${sorted.length})`);
  }
}

async function runWorker(button: HTMLElement, dialogTitle: string = button.textContent!): Promise<void> {
  return new Promise<void>((resolve, reject) => {
    new Worker({
      dialogId: "cache",
      dialogTitle,
      className: button.dataset.className,
      callbackAbort() {
        reject();
      },
      callbackSuccess() {
        let span = button.nextElementSibling;
        if (span && span.nodeName === "SPAN") {
          span.remove();
        }

        span = document.createElement("span");
        span.innerHTML = `<span class="icon icon16 fa-check green"></span> ${Language.get("wcf.acp.worker.success")}`;
        button.parentNode!.insertBefore(span, button.nextElementSibling);
        resolve();
      },
    });
  });
}
