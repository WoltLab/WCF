<?php

namespace wcf\action;

use wcf\util\JSON;

/**
 * @deprecated 5.5 Use PSR-7 responses (e.g. Laminas' JsonResponse).
 */
class AbstractAjaxAction extends AbstractAction
{
    /**
     * Sends a JSON-encoded response.
     *
     * @param array $data
     */
    protected function sendJsonResponse(array $data)
    {
        $json = JSON::encode($data);

        // send JSON response
        \header('Content-type: application/json; charset=UTF-8');
        echo $json;

        exit;
    }
}
