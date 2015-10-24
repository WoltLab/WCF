<header id="pageHeader" class="pageHeader">
	<div>
		<div class="layoutFluid">
			{include file='pageLogo'}
			
			{include file='pageSearchArea'}
			
			{include file='pageMenu'}
			
			{include file='pageMenuUser'}
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
