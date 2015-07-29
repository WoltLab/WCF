<header id="pageHeader" class="header">
	<div>
		<div class="{if $__wcf->getStyleHandler()->getStyle()->getVariable('useFluidLayout')}layoutFluid{else}layoutFixed{/if}">
			{include file='pageLogo'}
			
			{include file='searchArea'}
			
			{include file='mainMenu'}
			
			{include file='userPanel'}
		</div>
	</div>
	{*
	<div>
		<nav class="navigation navigationHeader">
			{include file='mainMenuSubMenu'}
			
			<ul class="navigationIcons">
				<li id="toBottomLink"><a href="{$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-down"></span> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				<li id="sitemap" class="jsOnly"><a href="#" title="{lang}wcf.page.sitemap{/lang}" class="jsTooltip"><span class="icon icon16 icon-sitemap"></span> <span class="invisible">{lang}wcf.page.sitemap{/lang}</span></a></li>
				{if $headerNavigation|isset}{@$headerNavigation}{/if}
				{event name='navigationIcons'}
			</ul>
		</nav>
	</div>
	*}
	<script data-relocate="true">
		var header = elById('pageHeader');
		header.style.setProperty('min-height', header.clientHeight + 'px');
		
		function stickyHeader() {
			header.classList[(document.body.scrollTop > 50) ? 'add' : 'remove']('sticky');
		}
		
		stickyHeader();
		window.addEventListener('scroll', stickyHeader);
	</script>
</header>