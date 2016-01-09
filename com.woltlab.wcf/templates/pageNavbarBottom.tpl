<div class="navigation navigationBottom">
	<div class="layoutFluid">
		{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
		
		<ul class="navigationIcons">
			<li id="toTopLink" class="toTopLink"><a href="{$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="jsTooltip"><span class="icon icon16 fa-arrow-up"></span> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
			{event name='navigationIcons'}
		</ul>
	</div>
</div>
