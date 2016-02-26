<div class="pageHeaderContainer">
	<header id="pageHeader" class="pageHeader">
		<div>
			<div class="layoutBoundary">
				{include file='pageHeaderLogo'}
				
				{* hide everything except the logo during login / in rescue mode *}
				{if $__isLogin|empty}
					{include file='pageHeaderUser'}
					
					{include file='pageHeaderMenu'}
					
					{include file='pageHeaderSearch'}
				{/if}
			</div>
		</div>
		
		<script data-relocate="true">
			var header = elById('pageHeader');
			var pageHeaderContainer = elBySel('.pageHeaderContainer');
			header.style.setProperty('min-height', header.clientHeight + 'px');
			
			function stickyHeader() {
				header.classList[(document.body.scrollTop > 50) ? 'add' : 'remove']('sticky');
				pageHeaderContainer.classList[(document.body.scrollTop > 50) ? 'add' : 'remove']('stickyPageHeader');
			}
			
			stickyHeader();
			window.addEventListener('scroll', stickyHeader);
		</script>
	</header>
</div>