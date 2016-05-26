<div class="pageNavigation">
	<div class="layoutBoundary">
		{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
		
		<ul class="pageNavigationIcons">
			{if $headerNavigation|isset}{@$headerNavigation}{/if}
			{event name='navigationIcons'}
		</ul>
	</div>
</div>
