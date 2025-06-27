{if !$beforeContentInteraction|empty}
	{unsafe:$beforeContentInteraction}
{/if}

{capture assign='__contentInteractionPagination'}
	{if $contentInteractionPagination|isset}{unsafe:$contentInteractionPagination}{/if}
{/capture}
{assign var='__contentInteractionPagination' value=$__contentInteractionPagination|trim}

{capture assign='__contentInteractionButtons'}
	{event name='beforeButtons'}
	{if $contentInteractionButtons|isset}{unsafe:$contentInteractionButtons}{/if}
	{event name='afterButtons'}
{/capture}
{assign var='__contentInteractionButtons' value=$__contentInteractionButtons|trim}

{capture assign='__contentInteractionDropdownItems'}
	{event name='beforeDropdownItems'}
	{if $contentInteractionDropdownItems|isset}{unsafe:$contentInteractionDropdownItems}{/if}
	{event name='afterDropdownItems'}
{/capture}
{assign var='__contentInteractionDropdownItems' value=$__contentInteractionDropdownItems|trim}

{capture assign='__contentInteractionShareButton'}
	{if $contentInteractionShareButton|isset}{unsafe:$contentInteractionShareButton}{/if}
{/capture}
{assign var='__contentInteractionShareButton' value=$__contentInteractionShareButton|trim}

{if $contentInteractionTabsComponent|isset || $__contentInteractionPagination || $__contentInteractionButtons || $__contentInteractionDropdownItems || $__contentInteractionShareButton}
	<div class="contentInteraction{if $contentInteractionTabsComponent|isset} contentInteraction--withTabs{/if}">
		{if $contentInteractionTabsComponent|isset}
			{unsafe:$contentInteractionTabsComponent->render()}
		{elseif $__contentInteractionPagination}
			<div class="contentInteractionPagination paginationTop">
				{unsafe:$__contentInteractionPagination}
			</div>
		{/if}

		{if $__contentInteractionButtons || $__contentInteractionDropdownItems || $__contentInteractionShareButton}
			<div class="contentInteractionButtonContainer">
				{if $__contentInteractionButtons}
					<div class="contentInteractionButtons">
						{unsafe:$__contentInteractionButtons}
					</div>
				{/if}

				{if $__contentInteractionShareButton}
					 <div class="contentInteractionShareButton">
						{unsafe:$__contentInteractionShareButton}
					</div>
				{/if}

				{if $__contentInteractionDropdownItems}
					<div class="contentInteractionDropdown dropdown jsOnly">
						<button type="button" class="button small dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">{icon name='ellipsis-vertical'}</button>

						<ul class="contentInteractionDropdownItems dropdownMenu">
							{unsafe:$__contentInteractionDropdownItems}
						</ul>
					</div>
				{/if}
			</div>
		{/if}
	</div>
{/if}
