<div class="pageHeaderContainer">
	<header id="pageHeader" class="pageHeader">
		<div>
			<div class="layoutBoundary">
				{include file='pageHeaderLogo'}
				
				{include file='pageHeaderSearch'}
				
				{include file='pageHeaderMenu'}
				
				{include file='pageHeaderUser'}
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