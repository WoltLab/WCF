<?php

namespace wcf\system\form\builder;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Represents a PSR15 compatible form builder that is embedded directly into
 * a page and submitted via `setupInlineForm()` exposed through the module
 * `WoltLabSuite/Core/Component/FormBuilder/Inline`. The field values are
 * persisted into a `DatabaseObjectBuilder` via the `applyValuesToBuilder()`
 * mechanism inherited from `DatabaseObjectBuilderFormDocument`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class Psr15BuilderForm extends DatabaseObjectBuilderFormDocument
{
    public function __construct(string $id)
    {
        $this->id($id);
        $this->prefix($id);

        $this->ajax = true;
    }

    /**
     * Processes the form using the request's parsed body. Returns 'null'
     * if validation succeeded and the result of 'toResponse()' otherwise.
     *
     * @see Psr15BuilderForm::toResponse()
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
     * Returns a response that can be consumed by JavaScript's `setupInlineForm()`.
     */
    public function toResponse(): ResponseInterface
    {
        return new JsonResponse([
            'form' => $this->getHtml(),
            'formId' => $this->getId(),
        ]);
    }

    #[\Override]
    public function validate()
    {
        $this->traitValidate();
    }

    #[\Override]
    public function ajax(bool $ajax = true)
    {
        /* This implementation forces `$ajax = true`. */

        return $this;
    }
}
