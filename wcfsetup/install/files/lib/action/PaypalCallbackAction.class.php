<?php

namespace wcf\action;

use GuzzleHttp\Psr7\Request;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\io\HttpFactory;
use wcf\system\payment\type\IPaymentType;
use wcf\util\StringUtil;

/**
 * Handles Paypal callbacks.
 *
 * @author  Marcel Werk
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @see https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNImplementation/
 */
class PaypalCallbackAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        // check response
        $processor = null;
        try {
            // post back to paypal to validate
            /** @noinspection PhpUnusedLocalVariableInspection */
            $content = '';
            try {
                $url = 'https://www.paypal.com/cgi-bin/webscr';
                if (!empty($_POST['test_ipn'])) {
                    // IPN simulator notification
                    $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
                }

                $request = new Request('POST', $url, [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ], \http_build_query(\array_merge(['cmd' => '_notify-validate'], $_POST), '', '&', \PHP_QUERY_RFC1738));
                $client = HttpFactory::getDefaultClient();
                $response = $client->send($request);
                $content = (string)$response->getBody();
            } catch (ClientExceptionInterface $e) {
                throw new \Exception('PayPal IPN validation request failed: ' . $e->getMessage(), 0, $e);
            }

            if (\strpos($content, "VERIFIED") === false) {
                throw new \Exception("PayPal IPN validation did not return 'VERIFIED'.");
            }
        } catch (\Exception $e) {
            \wcf\functions\exception\logThrowable($e);

            return new EmptyResponse(500);
        }

        try {
            // fix encoding
            if (!empty($_POST['charset']) && \strtoupper($_POST['charset']) != 'UTF-8') {
                foreach ($_POST as &$value) {
                    $value = StringUtil::convertEncoding(\strtoupper($_POST['charset']), 'UTF-8', $value);
                }
            }

            // Check that receiver_email is your Primary PayPal email
            $paypalEmail = \strtolower(PAYPAL_EMAIL_ADDRESS);
            if (\strtolower($_POST['receiver_email']) != $paypalEmail && (!isset($_POST['business']) || \strtolower($_POST['business']) != $paypalEmail)) {
                $exceptionMessage = "Mismatching receiver_email ('" . $_POST['receiver_email'] . "')";
                if (isset($_POST['business'])) {
                    $exceptionMessage .= " and business ('" . $_POST['business'] . "')";
                }
                $exceptionMessage .= ", expected '" . PAYPAL_EMAIL_ADDRESS . "'.";
                throw new SystemException($exceptionMessage);
            }

            // get token
            if (!isset($_POST['custom'])) {
                throw new SystemException('invalid custom item');
            }
            $tokenParts = \explode(':', $_POST['custom'], 2);
            if (\count($tokenParts) != 2) {
                throw new SystemException('invalid custom item');
            }
            // get payment type object type
            $objectType = ObjectTypeCache::getInstance()->getObjectType(\intval($tokenParts[0]));
            if ($objectType === null || !($objectType->getProcessor() instanceof IPaymentType)) {
                throw new SystemException('invalid payment type id');
            }
            $processor = $objectType->getProcessor();

            // get status
            $transactionType = (!empty($_POST['txn_type']) ? $_POST['txn_type'] : '');
            $paymentStatus = (!empty($_POST['payment_status']) ? $_POST['payment_status'] : '');

            $status = '';
            if ($transactionType == 'web_accept' || $transactionType == 'subscr_payment') {
                if ($paymentStatus == 'Completed') {
                    $status = 'completed';
                }
            }
            if ($paymentStatus == 'Refunded' || $paymentStatus == 'Reversed') {
                $status = 'reversed';
            }
            if ($paymentStatus == 'Canceled_Reversal') {
                $status = 'canceled_reversal';
            }

            if ($status) {
                $processor->processTransaction(
                    ObjectTypeCache::getInstance()->getObjectTypeIDByName(
                        'com.woltlab.wcf.payment.method',
                        'com.woltlab.wcf.payment.method.paypal'
                    ),
                    $tokenParts[1],
                    $_POST['mc_gross'],
                    $_POST['mc_currency'],
                    $_POST['txn_id'],
                    $status,
                    $_POST
                );
            }

            $this->executed();
        } catch (\Exception $e) {
            \wcf\functions\exception\logThrowable($e);
        }

        // Request was either successful or failed due to an error that cannot be fixed by
        // resending the notification. Status code 200 marks the notification as completed for PayPal.
        return new EmptyResponse(200);
    }
}
