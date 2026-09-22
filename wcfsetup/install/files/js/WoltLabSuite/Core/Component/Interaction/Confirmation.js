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
    exports.parseAffectedObjects = parseAffectedObjects;
    exports.handleConfirmation = handleConfirmation;
    var ConfirmationType;
    (function (ConfirmationType) {
        ConfirmationType["None"] = "None";
        ConfirmationType["SoftDelete"] = "SoftDelete";
        ConfirmationType["SoftDeleteWithReason"] = "SoftDeleteWithReason";
        ConfirmationType["Restore"] = "Restore";
        ConfirmationType["Delete"] = "Delete";
        ConfirmationType["Disable"] = "Disable";
        ConfirmationType["Custom"] = "Custom";
    })(ConfirmationType || (exports.ConfirmationType = ConfirmationType = {}));
    /**
     * Parses the JSON encoded list of affected objects from a data attribute. Absent or
     * malformed values yield an empty list, the confirmation is then shown without them.
     */
    function parseAffectedObjects(value) {
        if (!value) {
            return [];
        }
        try {
            const result = JSON.parse(value);
            if (Array.isArray(result)) {
                return result.filter((item) => typeof item === "string");
            }
        }
        catch {
            // Ignore malformed values.
        }
        return [];
    }
    async function handleConfirmation(objectName, confirmationType, customMessage = "", affectedObjects = []) {
        if (confirmationType == ConfirmationType.SoftDelete) {
            return await (0, Confirmation_1.confirmationFactory)().softDelete(objectName ? objectName : undefined);
        }
        if (confirmationType == ConfirmationType.SoftDeleteWithReason) {
            return await (0, Confirmation_1.confirmationFactory)().softDelete(objectName ? objectName : undefined, true);
        }
        if (confirmationType == ConfirmationType.Restore) {
            return {
                result: await (0, Confirmation_1.confirmationFactory)().restore(objectName ? objectName : undefined),
            };
        }
        if (confirmationType == ConfirmationType.Delete) {
            return {
                result: await (0, Confirmation_1.confirmationFactory)().delete(objectName ? objectName : undefined, affectedObjects),
            };
        }
        if (confirmationType == ConfirmationType.Disable) {
            return {
                result: await (0, Confirmation_1.confirmationFactory)().disable(objectName ? objectName : undefined),
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
