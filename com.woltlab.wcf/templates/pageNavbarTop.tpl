<div class="pageNavigation">
	<div class="layoutBoundary">
		{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
		
		<ul class="pageNavigationIcons">
			<li id="sitemap" class="jsOnly"><a href="#" title="{lang}wcf.page.sitemap{/lang}" class="jsTooltip"><span class="icon icon16 fa-sitemap"></span> <span class="invisible">{lang}wcf.page.sitemap{/lang}</span></a></li>
			{if $headerNavigation|isset}{@$headerNavigation}{/if}
			{event name='navigationIcons'}
		</ul>
	</div>
</div>
