{if $beforeContent|isset}{unsafe:$beforeContent}

{/if}{unsafe:$content}{if $afterContent|isset}

{unsafe:$afterContent}{/if}
{hascontent} {* this line ends with a space *}

-- {* this line ends with a space *}
{content}
{unsafe:MAIL_SIGNATURE|phrase}
{/content}
{/hascontent}
