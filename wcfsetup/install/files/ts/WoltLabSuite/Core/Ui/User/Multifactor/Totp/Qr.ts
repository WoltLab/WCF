import QrCreator from "qr-creator";

export function render(container: HTMLElement): void {
  const secret: HTMLElement | null = container.querySelector(".totpSecret");
  if (!secret) {
    return;
  }

  const accountName = secret.dataset.accountname;
  if (!accountName) {
    return;
  }

  const issuer = secret.dataset.issuer;
  const label = (issuer ? `${issuer}:` : "") + accountName;

  QrCreator.render(
    {
      text: `otpauth://totp/${encodeURIComponent(label)}?secret=${encodeURIComponent(secret.textContent!)}${
        issuer ? `&issuer=${encodeURIComponent(issuer)}` : ""
      }`,
    },
    container,
  );
}

export default render;

export function renderAll(): void {
  document.querySelectorAll(".totpSecretContainer").forEach((el: HTMLElement) => render(el));
}
