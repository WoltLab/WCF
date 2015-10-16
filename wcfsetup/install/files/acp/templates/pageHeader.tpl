<header id="pageHeader" class="header">
	<div>
		<div class="{if $__wcf->getStyleHandler()->getStyle()->getVariable('useFluidLayout')}layoutFluid{else}layoutFixed{/if}">
			{include file='pageLogo'}
			
			{include file='searchArea'}
			
			{include file='mainMenu'}
			
			{include file='userPanel'}
		</div>
	</div>
	
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
