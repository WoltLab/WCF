/**
 * Represents a confirmation type.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Confirmation"], function (require, exports, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConfirmationType = void 0;
    exports.handleConfirmation = handleConfirmation;
    var ConfirmationType;
    (function (ConfirmationType) {
        ConfirmationType["None"] = "None";
        ConfirmationType["SoftDelete"] = "SoftDelete";
        ConfirmationType["SoftDeleteWithReason"] = "SoftDeleteWithReason";
        ConfirmationType["Restore"] = "Restore";
        ConfirmationType["Delete"] = "Delete";
        ConfirmationType["Custom"] = "Custom";
    })(ConfirmationType || (exports.ConfirmationType = ConfirmationType = {}));
    async function handleConfirmation(objectName, confirmationType, customMessage = "") {
        if (confirmationType == ConfirmationType.SoftDelete) {
            return await (0, Confirmation_1.confirmationFactory)().softDelete(objectName);
        }
        if (confirmationType == ConfirmationType.SoftDeleteWithReason) {
            return await (0, Confirmation_1.confirmationFactory)().softDelete(objectName, true);
        }
        if (confirmationType == ConfirmationType.Restore) {
            return {
                result: await (0, Confirmation_1.confirmationFactory)().restore(objectName ? objectName : undefined),
            };
        }
        if (confirmationType == ConfirmationType.Delete) {
            return {
                result: await (0, Confirmation_1.confirmationFactory)().delete(objectName ? objectName : undefined),
            };
        }
        if (confirmationType == ConfirmationType.Custom) {
            return {
                result: await (0, Confirmation_1.confirmationFactory)().custom(customMessage).withoutMessage(),
            };
        }
        return {
            result: true,
        };
    }
});
