<?php

namespace wcf\system\user\multifactor;

use ParagonIE\ConstantTime\Hex;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\button\FormButton;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\dependency\IsNotClickedFormFieldDependency;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\user\multifactor\totp\CodeFormField;
use wcf\system\user\multifactor\totp\DeviceNode;
use wcf\system\user\multifactor\totp\DevicesContainer;
use wcf\system\user\multifactor\totp\NewDeviceContainer;
use wcf\system\user\multifactor\totp\SecretFormField;
use wcf\system\user\multifactor\totp\Totp;
use wcf\system\WCF;

/**
 * Implementation of the Time-based One-time Password Algorithm (RFC 6238).
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor
 * @since   5.4
 */
final class TotpMultifactorMethod implements IMultifactorMethod
{
    private const USER_ATTEMPTS_PER_TEN_MINUTES = 5;

    /**
     * Returns the number of devices the user set up.
     */
    public function getStatusText(Setup $setup): string
    {
        $sql = "SELECT  COUNT(*) AS count, MAX(useTime) AS lastUsed
                FROM    wcf" . WCF_N . "_user_multifactor_totp
                WHERE   setupID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$setup->getId()]);

        return WCF::getLanguage()->getDynamicVariable(
            'wcf.user.security.multifactor.totp.status',
            $statement->fetchArray()
        );
    }

    /**
     * @inheritDoc
     */
    public function createManagementForm(IFormDocument $form, ?Setup $setup, $returnData = null): void
    {
        if ($returnData !== null) {
            \assert(\is_array($returnData));
            \assert(
                isset($returnData['action'])
                && ($returnData['action'] === 'add' || $returnData['action'] === 'delete')
            );
            \assert(isset($returnData['deviceName']));
            $form->successMessage('wcf.user.security.multifactor.totp.success.' . $returnData['action'], [
                'deviceName' => $returnData['deviceName'],
            ]);
        }

        $form->addDefaultButton(false);
        $newDeviceContainer = NewDeviceContainer::create()
            ->label('wcf.user.security.multifactor.totp.newDevice')
            ->appendChildren([
                SecretFormField::create(),
                CodeFormField::create()
                    ->label('wcf.user.security.multifactor.totp.code')
                    ->description('wcf.user.security.multifactor.totp.code.description')
                    ->required()
                    ->addValidator(new FormFieldValidator('totpSecretValid', static function (CodeFormField $field) {
                        /** @var SecretFormField $secret */
                        $secret = $field->getDocument()->getNodeById('secret');
                        $totp = $secret->getTotp();

                        $minCounter = 0;
                        if (!$totp->validateTotpCode($field->getValue(), $minCounter, new \DateTime())) {
                            $field->value('');
                            $field->addValidationError(new FormFieldValidationError(
                                'invalidCode',
                                'wcf.user.security.multifactor.error.invalidCode'
                            ));
                        }
                        $field->minCounter($minCounter);
                    })),
                TextFormField::create('deviceName')
                    ->label('wcf.user.security.multifactor.totp.deviceName')
                    ->description('wcf.user.security.multifactor.totp.deviceName.description.setup')
                    ->placeholder('wcf.user.security.multifactor.totp.deviceName.placeholder')
                    ->maximumLength(200),
                FormButton::create('submitButton')
                    ->label('wcf.global.button.submit')
                    ->accessKey('s')
                    ->submit(true)
                    ->addClass('buttonPrimary'),
            ]);
        $form->appendChild($newDeviceContainer);

        // Note: The order of the two parts of the form is important. Pressing submit within an input
        // will implicitly press the first submit button. If this container comes first the submit
        // button will be a delete button.
        if ($setup) {
            $sql = "SELECT      deviceID, deviceName, createTime, useTime
                    FROM        wcf" . WCF_N . "_user_multifactor_totp
                    WHERE       setupID = ?
                    ORDER BY    createTime";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$setup->getId()]);
            $devicesContainer = DevicesContainer::create('devices')
                ->label('wcf.user.security.multifactor.totp.devices');
            $devices = $statement->fetchAll(\PDO::FETCH_ASSOC);

            $canBeDeleted = \count($devices) > 1;
            foreach ($devices as $row) {
                $device = DeviceNode::create('device-' . $row['deviceID'])
                    ->setData($row);

                if ($canBeDeleted) {
                    $button = ButtonFormField::create('device-delete-' . $row['deviceID'])
                        ->buttonLabel('wcf.global.button.delete')
                        ->objectProperty('delete')
                        ->value($row['deviceID']);
                    $device->appendChild($button);
                    $newDeviceContainer->addDependency(
                        IsNotClickedFormFieldDependency::create('device-delete-' . $row['deviceID'])
                            ->field($button)
                    );
                } else {
                    $button = new class extends FormButton {
                        protected $templateName = '__multifactorTotpDeviceNoDeleteButton';
                    };
                    $button->id('no-delete-' . $row['deviceID'])
                        ->label('wcf.global.button.delete');
                    $device->appendChild($button);
                }
                $devicesContainer->appendChild($device);
            }

            $form->appendChild($devicesContainer);
        }
    }

    /**
     * @inheritDoc
     */
    public function processManagementForm(IFormDocument $form, Setup $setup): array
    {
        $formData = $form->getData();

        \assert(
            (!empty($formData['data']) && empty($formData['delete']))
            || (empty($formData['data']) && !empty($formData['delete']))
        );

        if (!empty($formData['delete'])) {
            // Fetch deviceName for success message.
            $sql = "SELECT  deviceName
                    FROM    wcf" . WCF_N . "_user_multifactor_totp
                    WHERE   setupID = ?
                        AND deviceID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $setup->getId(),
                $formData['delete'],
            ]);
            $deviceName = $statement->fetchSingleColumn();

            // Remove the device.
            $sql = "DELETE FROM wcf" . WCF_N . "_user_multifactor_totp
                    WHERE       setupID = ?
                            AND deviceID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $setup->getId(),
                $formData['delete'],
            ]);

            // Check the contract that the last device may not be removed.
            $sql = "SELECT  COUNT(*)
                    FROM    wcf" . WCF_N . "_user_multifactor_totp
                    WHERE   setupID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $setup->getId(),
            ]);

            if (!$statement->fetchSingleColumn()) {
                throw new \LogicException('Unreachable');
            }

            return [
                'action' => 'delete',
                'deviceName' => $deviceName,
            ];
        } else {
            $deviceName = $formData['data']['deviceName'];
            if (!$deviceName) {
                $defaultName = WCF::getLanguage()
                    ->getDynamicVariable('wcf.user.security.multifactor.totp.deviceName.placeholder');

                $sql = "SELECT  deviceName
                        FROM    wcf" . WCF_N . "_user_multifactor_totp
                        WHERE   setupID = ?";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute([
                    $setup->getId(),
                ]);
                $deviceNames = $statement->fetchAll(\PDO::FETCH_COLUMN);

                for ($i = 1;; $i++) {
                    $deviceName = $defaultName . ($i > 1 ? " ({$i})" : '');
                    if (!\in_array($deviceName, $deviceNames)) {
                        break;
                    }
                }
            }

            $sql = "INSERT INTO wcf" . WCF_N . "_user_multifactor_totp
                                (setupID, deviceID, deviceName, secret, minCounter, createTime)
                    VALUES      (?, ?, ?, ?, ?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                $setup->getId(),
                Hex::encode(\random_bytes(16)),
                $deviceName,
                $formData['data']['secret'],
                $formData['data']['onetimecode']['minCounter'],
                \TIME_NOW,
            ]);

            return [
                'action' => 'add',
                'deviceName' => $deviceName,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticationForm(IFormDocument $form, Setup $setup): void
    {
        $form->markRequiredFields(false);

        $sql = "SELECT      *
                FROM        wcf" . WCF_N . "_user_multifactor_totp
                WHERE       setupID = ?
                ORDER BY    deviceName";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$setup->getId()]);
        $devices = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (\count($devices) > 1) {
            $deviceOptions = [];
            $mostRecentlyUsed = null;
            foreach ($devices as $device) {
                $deviceOptions[$device['deviceID']] = $device['deviceName'];

                if ($mostRecentlyUsed === null || $mostRecentlyUsed['useTime'] < $device['useTime']) {
                    $mostRecentlyUsed = $device;
                }
            }

            $form->appendChildren([
                RadioButtonFormField::create('device')
                    ->label('wcf.user.security.multifactor.totp.deviceName')
                    ->description('wcf.user.security.multifactor.totp.deviceName.description.auth')
                    ->objectProperty('deviceID')
                    ->options($deviceOptions)
                    ->value($mostRecentlyUsed['deviceID']),
            ]);
        } else {
            $form->appendChildren([
                HiddenFormField::create('device')
                    ->objectProperty('deviceID')
                    ->value($devices[0]['deviceID']),
            ]);
        }

        $form->appendChildren([
            CodeFormField::create()
                ->label('wcf.user.security.multifactor.totp.code')
                ->description('wcf.user.security.multifactor.totp.code.description')
                ->autoFocus()
                ->required()
                ->addValidator(new FormFieldValidator(
                    'code',
                    static function (CodeFormField $field) use ($devices, $setup) {
                        FloodControl::getInstance()->registerUserContent(
                            'com.woltlab.wcf.multifactor.totp',
                            $setup->getId()
                        );
                        $attempts = FloodControl::getInstance()->countUserContent(
                            'com.woltlab.wcf.multifactor.totp',
                            $setup->getId(),
                            new \DateInterval('PT10M')
                        );
                        if ($attempts['count'] > self::USER_ATTEMPTS_PER_TEN_MINUTES) {
                            $field->value('');
                            $field->addValidationError(new FormFieldValidationError(
                                'flood',
                                'wcf.user.security.multifactor.totp.error.flood',
                                $attempts
                            ));

                            return;
                        }

                        /** @var IFormField $deviceField */
                        $deviceField = $field->getDocument()->getNodeById('device');

                        $selectedDevice = null;
                        foreach ($devices as $device) {
                            if ($device['deviceID'] === $deviceField->getValue()) {
                                $selectedDevice = $device;
                            }
                        }
                        if ($selectedDevice === null) {
                            // The user sent an invalid value for the device selector.
                            $field->value('');
                            $field->addValidationError(new FormFieldValidationError(
                                'invalidCode',
                                'wcf.user.security.multifactor.error.invalidCode'
                            ));

                            return;
                        }

                        $totp = new Totp($selectedDevice['secret']);
                        $minCounter = $selectedDevice['minCounter'];
                        if (!$totp->validateTotpCode($field->getValue(), $minCounter, new \DateTime())) {
                            $field->value('');
                            $field->addValidationError(new FormFieldValidationError(
                                'invalidCode',
                                'wcf.user.security.multifactor.error.invalidCode'
                            ));
                        }
                        $field->minCounter($minCounter);
                    }
                )),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function processAuthenticationForm(IFormDocument $form, Setup $setup): void
    {
        $formData = $form->getData();

        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_user_multifactor_totp
                WHERE   setupID = ?
                    AND deviceID = ?
                FOR UPDATE";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            $setup->getId(),
            $formData['data']['deviceID'],
        ]);
        $device = $statement->fetchArray();

        if ($device === null) {
            throw new \RuntimeException('Unable to find the device.');
        }

        $sql = "UPDATE  wcf" . WCF_N . "_user_multifactor_totp
                SET     useTime = ?,
                        minCounter = ?
                WHERE   setupID = ?
                    AND deviceID = ?
                    AND minCounter < ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            \TIME_NOW,
            $formData['data']['onetimecode']['minCounter'],
            $setup->getId(),
            $formData['data']['deviceID'],
            $formData['data']['onetimecode']['minCounter'],
        ]);

        if ($statement->getAffectedRows() !== 1) {
            throw new \RuntimeException('Unable to invalidate the code.');
        }
    }
}
