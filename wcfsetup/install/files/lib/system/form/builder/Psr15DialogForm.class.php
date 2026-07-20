<?php

namespace wcf\system\form\builder;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\form\builder\button\IFormButton;

/**
 * Represents a PSR15 compatible form builder that
 * interfaces with the dialog implementation exposed
 * through `dialogFactory().usingFormBuilder()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
        $this->prefix($id);
        $this->title = $title;

        $this->ajax = true;
    }

    /**
     * Processes the form using the request's parsed body. Returns 'null'
     * if validation succeeded and the result of 'toResponse()' otherwise.
     *
     * @see Psr15DialogForm::toResponse()
     */
    public function validateRequest(ServerRequestInterface $request): ?ResponseInterface
    {
        $this->requestData($request->getParsedBody() ?? []);
        $this->readValues();
        $this->validate();

        if ($this->hasValidationErrors()) {
            return $this->toResponse();
        }

        return null;
    }

    /**
     * Returns a response that can be consumed by JavaScript's `dialogFactory().usingFormBuilder()`.
     */
    public function toResponse(): ResponseInterface
    {
        return new JsonResponse([
            'dialog' => $this->getHtml(),
            'formId' => $this->getId(),
            'title' => $this->title,
        ]);
    }

    #[\Override]
    public function addButton(IFormButton $button)
    {
        throw new \LogicException(self::class . ' does not support custom buttons.');
    }

    #[\Override]
    public function validate()
    {
        $this->traitValidate();
    }

    #[\Override]
    protected function createDefaultButton()
    {
        /* Buttons are implicitly added by the dialog API. */
    }

    #[\Override]
    public function ajax(bool $ajax = true)
    {
        /* This implementation forces `$ajax = true`. */

        return $this;
    }
}
