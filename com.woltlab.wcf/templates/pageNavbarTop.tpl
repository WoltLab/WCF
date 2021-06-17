<div class="pageNavigation">
	<div class="layoutBoundary">
		{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
		
		{hascontent}
			{* DEPRECATED: use contentInteractionButtons or contentInteractionDropdownItems instead *}
			<ul class="pageNavigationIcons jsPageNavigationIcons">
				{content}
					{if $headerNavigation|isset}{@$headerNavigation}{/if}
					{event name='navigationIcons'}
				{/content}
			</ul>
		{/hascontent}
	</div>
</div>
