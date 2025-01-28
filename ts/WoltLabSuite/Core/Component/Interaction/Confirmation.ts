/**
 * Represents a confirmation type.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";

export enum ConfirmationType {
  None = "None",
  SoftDelete = "SoftDelete",
  SoftDeleteWithReason = "SoftDeleteWithReason",
  Restore = "Restore",
  Delete = "Delete",
  Custom = "Custom",
}

type ResultConfirmationWithReason = {
  result: boolean;
  reason?: string;
};

export async function handleConfirmation(
  objectName: string,
  confirmationType: ConfirmationType,
  customMessage: string = "",
): Promise<ResultConfirmationWithReason> {
  if (confirmationType == ConfirmationType.SoftDelete) {
    return await confirmationFactory().softDelete(objectName);
  }

  if (confirmationType == ConfirmationType.SoftDeleteWithReason) {
    return await confirmationFactory().softDelete(objectName, true);
  }

  if (confirmationType == ConfirmationType.Restore) {
    return {
      result: await confirmationFactory().restore(objectName ? objectName : undefined),
    };
  }

  if (confirmationType == ConfirmationType.Delete) {
    return {
      result: await confirmationFactory().delete(objectName ? objectName : undefined),
    };
  }

  if (confirmationType == ConfirmationType.Custom) {
    return {
      result: await confirmationFactory().custom(customMessage).withoutMessage(),
    };
  }

  return {
    result: true,
  };
}
