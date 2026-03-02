<?php

namespace wcf\action;

/**
 * @deprecated 5.5 Use PSR-7 responses (e.g. Laminas' JsonResponse).
 */
abstract class AbstractAjaxAction extends AbstractAction
{
    /**
     * Sends a JSON-encoded response.
     *
     * @param mixed[] $data
     * @return never
     */
    protected function sendJsonResponse(array $data)
    {
        $json = \json_encode($data, \JSON_THROW_ON_ERROR);

        // send JSON response
        \header('Content-type: application/json; charset=UTF-8');
        echo $json;

        exit;
    }
}
