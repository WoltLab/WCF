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
  Disable = "Disable",
  Custom = "Custom",
}

type ResultConfirmationWithReason = {
  result: boolean;
  reason?: string;
};

/**
 * Parses the JSON encoded list of affected objects from a data attribute. Absent or
 * malformed values yield an empty list, the confirmation is then shown without them.
 */
export function parseAffectedObjects(value: string | undefined): string[] {
  if (!value) {
    return [];
  }

  try {
    const result: unknown = JSON.parse(value);
    if (Array.isArray(result)) {
      return result.filter((item): item is string => typeof item === "string");
    }
  } catch {
    // Ignore malformed values.
  }

  return [];
}

export async function handleConfirmation(
  objectName: string,
  confirmationType: ConfirmationType,
  customMessage: string = "",
  affectedObjects: string[] = [],
): Promise<ResultConfirmationWithReason> {
  if (confirmationType == ConfirmationType.SoftDelete) {
    return await confirmationFactory().softDelete(objectName ? objectName : undefined);
  }

  if (confirmationType == ConfirmationType.SoftDeleteWithReason) {
    return await confirmationFactory().softDelete(objectName ? objectName : undefined, true);
  }

  if (confirmationType == ConfirmationType.Restore) {
    return {
      result: await confirmationFactory().restore(objectName ? objectName : undefined),
    };
  }

  if (confirmationType == ConfirmationType.Delete) {
    return {
      result: await confirmationFactory().delete(objectName ? objectName : undefined, affectedObjects),
    };
  }

  if (confirmationType == ConfirmationType.Disable) {
    return {
      result: await confirmationFactory().disable(objectName ? objectName : undefined),
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
