<?php

namespace wcf\data;

/**
 * @deprecated 5.5 The concept of starting a message in a simple editor and then migrating to an extended editor no longer exists.
 */
interface IExtendedMessageQuickReplyAction extends IMessageQuickReplyAction
{
    /**
     * Saves message and jumps to extended mode.
     *
     * @return  array
     */
    public function jumpToExtended();

    /**
     * Validates parameters to jump to extended mode.
     */
    public function validateJumpToExtended();
}
