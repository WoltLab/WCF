/**
 * Renders the QR code containing the TOTP secret.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Multifactor/Totp/Qr
 * @woltlabExcludeBundle  all
 */

import QrCreator from "qr-creator";
import * as Language from "../../../../Language";

export function render(container: HTMLElement): void {
  const secret: HTMLElement | null = container.querySelector(".totpSecret");
  if (!secret) {
    return;
  }

  const accountName = secret.dataset.accountname;
  if (!accountName) {
    return;
  }

  const readableIssuer = secret.dataset.issuer || "";
  const label = (readableIssuer !== "" ? `${readableIssuer}:` : "") + accountName;

  const canvas = container.querySelector("canvas");

  if (!canvas) {
    throw new Error("Missing <canvas>.");
  }

  QrCreator.render(
    {
      text: getUrl(readableIssuer, label, secret.textContent!),
      size: canvas.clientWidth,
    },
    canvas,
  );

  const a = document.createElement("a");
  a.href = getUrl(window.location.hostname, label, secret.textContent!);
  a.ariaLabel = Language.get("wcf.user.security.multifactor.com.woltlab.wcf.multifactor.totp.link");

  canvas.parentElement!.insertAdjacentElement("afterbegin", a);

  a.appendChild(canvas);
}

function getUrl(issuer: string, label: string, secret: string): string {
  return `otpauth://totp/${encodeURIComponent(label)}?secret=${encodeURIComponent(secret)}${
    issuer !== "" ? `&issuer=${encodeURIComponent(issuer)}` : ""
  }`;
}

export default render;

export function renderAll(): void {
  document.querySelectorAll(".totpSecretContainer").forEach((el: HTMLElement) => render(el));
}
