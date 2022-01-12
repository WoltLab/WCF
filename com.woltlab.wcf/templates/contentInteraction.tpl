{if !$beforeContentInteraction|empty}
    {@$beforeContentInteraction}
{/if}

<div class="contentInteraction">
    {hascontent}
		<div class="contentInteractionPagination paginationTop">
			{content}{if $contentInteractionPagination|isset}{@$contentInteractionPagination}{/if}{/content}
		</div>
	{/hascontent}

    <div class="contentInteractionButtonContainer">
        {hascontent}
            <div class="contentInteractionButtons">
                {content}
                    {event name='beforeButtons'}
                    {if $contentInteractionButtons|isset}{@$contentInteractionButtons}{/if}
                    {event name='afterButtons'}
                {/content}
            </div>
        {/hascontent}

        {hascontent}
            <div class="contentInteractionDropdown dropdown jsOnly">
                <a href="#" class="button small dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}"><span class="icon icon16 fa-ellipsis-v"></span></a>

                <ul class="contentInteractionDropdownItems dropdownMenu">
                    {content}
                        {event name='beforeDropdownItems'}
                        {if $contentInteractionDropdownItems|isset}{@$contentInteractionDropdownItems}{/if}
                        {event name='afterDropdownItems'}
                    {/content}
                </ul>
            </div>
        {/hascontent}
    </div>
</div>
