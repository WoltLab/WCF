{if $beforeContent|isset}{@$beforeContent}

{/if}{@$content}{if $afterContent|isset}

{@$afterContent}{/if}
{hascontent} {* this line ends with a space *}

-- {* this line ends with a space *}
{content}
{@MAIL_SIGNATURE|language}
{if $mailbox|is_a:'wcf\system\email\UserMailbox'}{if MAIL_SIGNATURE|language}{* add newlines *}


{/if}This email was sent to you, because you registered on
the {$mailbox->getUser()->registrationDate|plainTime} at {@PAGE_TITLE|language}.{/if} {* TODO: language item *}
{/content}
{/hascontent}
