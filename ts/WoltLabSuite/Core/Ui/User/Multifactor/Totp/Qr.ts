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

  const canvas = container.querySelector("canvas");
  QrCreator.render(
    {
      text: `otpauth://totp/${encodeURIComponent(label)}?secret=${encodeURIComponent(secret.textContent!)}${
        issuer ? `&issuer=${encodeURIComponent(issuer)}` : ""
      }`,
      size: canvas && canvas.clientWidth ? canvas.clientWidth : 200,
    },
    canvas || container,
  );
}

export default render;

export function renderAll(): void {
  document.querySelectorAll(".totpSecretContainer").forEach((el: HTMLElement) => render(el));
}
