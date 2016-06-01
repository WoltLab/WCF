{@$content}
{hascontent}

-- {* The Space is important, do not remove *}
{content}
{@MAIL_SIGNATURE|language}
{if $mailbox|is_a:'wcf\system\email\UserMailbox'}{if MAIL_SIGNATURE|language}{* add newlines *}


{/if}This email was sent to you, because you registered on
the {$mailbox->getUser()->registrationDate|plainTime} at {@PAGE_TITLE|language}.{/if} {* TODO: language item *}
{/content}
{/hascontent}
