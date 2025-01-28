{if $contextMenuOptions}
	<div class="dropdown">
		<button type="button" class="button small dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">
			{icon name='ellipsis-vertical'}
		</button>

		<ul class="dropdownMenu">
			{unsafe:$contextMenuOptions}
		</ul>
	</div>
{else}
	<button type="button" disabled class="button small" aria-label="{lang}wcf.global.button.more{/lang}">
		{icon name='ellipsis-vertical'}
	</button>
{/if}
