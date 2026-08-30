<?php

namespace wcf\data\comment\response;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit comment responses.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `CommentResponseBuilder` instead.
 *
 * @mixin       CommentResponse
 * @extends DatabaseObjectEditor<CommentResponse>
 */
class CommentResponseEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = CommentResponse::class;
}
