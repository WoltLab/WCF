<ul class="contentInteractionTabs">
	{foreach from=$tabs item='tab'}
		<li class="contentInteractionTab{if $tab->active} contentInteractionTab--active{/if}">
			<a
				href="{$tab->link}"
				class="contentInteractionTab__link"
				{if $tab->active} aria-current="page"{/if}
			>
				{lang}{$tab->title}{/lang}
			</a>
		</li>
	{/foreach}
</ul>
