<?php

namespace wcf\data\comment\response;

use wcf\data\DatabaseObjectDecorator;

/**
 * Provides methods to handle response data.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   CommentResponse
 * @extends DatabaseObjectDecorator<CommentResponse>
 */
class StructuredCommentResponse extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    public static $baseClass = CommentResponse::class;

    /**
     * deletable by current user
     * @var bool
     */
    public $deletable = false;

    /**
     * editable for current user
     * @var bool
     */
    public $editable = false;

    /**
     * Returns a structured response.
     *
     * @return ?StructuredCommentResponse
     */
    public static function getResponse(int $responseID)
    {
        $response = new CommentResponse($responseID);
        if ($response->isNil()) {
            return null;
        }

        // prepare structured response
        $response = new self($response);

        return $response;
    }

    /**
     * Sets deletable state.
     *
     * @return void
     */
    public function setIsDeletable(bool $deletable)
    {
        $this->deletable = $deletable;
    }

    /**
     * Sets editable state.
     *
     * @return void
     */
    public function setIsEditable(bool $editable)
    {
        $this->editable = $editable;
    }

    /**
     * Returns true if the response is deletable by current user.
     *
     * @return  bool
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * Returns true if the response is editable by current user.
     *
     * @return  bool
     */
    public function isEditable()
    {
        return $this->editable;
    }
}
