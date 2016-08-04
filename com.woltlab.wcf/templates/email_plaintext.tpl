{if $beforeContent|isset}{@$beforeContent}

{/if}{@$content}{if $afterContent|isset}

{@$afterContent}{/if}
{hascontent} {* this line ends with a space *}

-- {* this line ends with a space *}
{content}
{@MAIL_SIGNATURE|language}
{/content}
{/hascontent}
