<?php

namespace wcf\system\form\builder;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Represents a PSR15 compatible form builder that
 * interfaces with the dialog implementation exposed
 * through `dialogFactory().usingFormBuilder()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder
 * @since 6.0
 */
final class Psr15DialogForm extends FormDocument
{
    private readonly string $title;

    public function __construct(
        string $id,
        string $title
    ) {
        $this->id($id);
        $this->title = $title;

        $this->ajax();
        $this->addDefaultButton(false);
    }

    public function validate()
    {
        return $this->traitValidate();
    }

    public function validatePsr7Request(ServerRequestInterface $request): ?JsonResponse
    {
        $this->requestData($request->getParsedBody());
        $this->readValues();
        $this->validate();

        if ($this->hasValidationErrors()) {
            return $this->toJsonResponse();
        }

        return null;
    }

    public function toJsonResponse(): JsonResponse
    {
        return new JsonResponse([
            'dialog' => $this->getHtml(),
            'formId' => $this->getId(),
            'title' => $this->title,
        ]);
    }
}
