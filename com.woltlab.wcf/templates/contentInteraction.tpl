{if !$beforeContentInteraction|empty}
    {@$beforeContentInteraction}
{/if}

{capture assign='__contentInteractionPagination'}
    {if $contentInteractionPagination|isset}{@$contentInteractionPagination}{/if}
{/capture}
{assign var='__contentInteractionPagination' value=$__contentInteractionPagination|trim}

{capture assign='__contentInteractionButtons'}
    {event name='beforeButtons'}
    {if $contentInteractionButtons|isset}{@$contentInteractionButtons}{/if}
    {event name='afterButtons'}
{/capture}
{assign var='__contentInteractionButtons' value=$__contentInteractionButtons|trim}

{capture assign='__contentInteractionDropdownItems'}
    {event name='beforeDropdownItems'}
    {if $contentInteractionDropdownItems|isset}{@$contentInteractionDropdownItems}{/if}
    {event name='afterDropdownItems'}
{/capture}
{assign var='__contentInteractionDropdownItems' value=$__contentInteractionDropdownItems|trim}

{if $__contentInteractionPagination || $__contentInteractionButtons || $__contentInteractionDropdownItems}
    <div class="contentInteraction">
        {if $__contentInteractionPagination}
    		<div class="contentInteractionPagination paginationTop">
    			{@$__contentInteractionPagination}
    		</div>
    	{/if}

        {if $__contentInteractionButtons || $__contentInteractionDropdownItems}
            <div class="contentInteractionButtonContainer">
                {if $__contentInteractionButtons}
                    <div class="contentInteractionButtons">
                        {@$__contentInteractionButtons}
                    </div>
                {/if}

                {if $__contentInteractionDropdownItems}
                    <div class="contentInteractionDropdown dropdown jsOnly">
                        <a href="#" class="button small dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}"><span class="icon icon16 fa-ellipsis-v"></span></a>

                        <ul class="contentInteractionDropdownItems dropdownMenu">
                            {@$__contentInteractionDropdownItems}
                        </ul>
                    </div>
                {/if}
            </div>
        {/if}
    </div>
{/if}
