<div class="navigation navigationTop">
	<div class="layoutFluid">
		{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
		
		<ul class="navigationIcons">
			<li id="toBottomLink"><a href="{$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-down"></span> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
			<li id="sitemap" class="jsOnly"><a href="#" title="{lang}wcf.page.sitemap{/lang}" class="jsTooltip"><span class="icon icon16 icon-sitemap"></span> <span class="invisible">{lang}wcf.page.sitemap{/lang}</span></a></li>
			{if $headerNavigation|isset}{@$headerNavigation}{/if}
			{event name='navigationIcons'}
		</ul>
	</div>
</div>
