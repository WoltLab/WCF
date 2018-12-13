<div id="pageHeaderMobileSearch" class="pageHeaderMobileSearch">
	<!-- Placeholder for the mobile UI. -->
</div>
<div id="pageHeaderSearch" class="pageHeaderSearch" data-disable-auto-focus="true">
	<div class="pageHeaderSearchInputContainer">
		<div id="pageHeaderSearchType" class="pageHeaderSearchType dropdown">
			<a href="#" class="button dropdownToggle">{lang}wcf.search.type.everywhere{/lang}</a>
			<ul class="dropdownMenu">
				<li><a href="#" data-provider-name="everywhere">{lang}wcf.search.type.everywhere{/lang}</a></li>
				<li class="dropdownDivider"></li>
				
				{foreach from=$availableAcpSearchProviders key='availableAcpSearchProviderName' item='availableAcpSearchProviderLabel'}
					<li><a href="#" data-provider-name="{@$availableAcpSearchProviderName}">{@$availableAcpSearchProviderLabel}</a></li>
				{/foreach}
			</ul>
		</div>
		
		<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" required value="" data-toggle="search">
		
		<button class="pageHeaderSearchInputButton" type="submit">
			<span class="icon icon16 pointer fa-search" title="{lang}wcf.global.search{/lang}"></span>
		</button>
	</div>
</div>
<script data-relocate="true">
	(function() {
		elById('pageHeaderMobileSearch').addEventListener('click', function() {
			this.classList.toggle('active');
			
			if (elById('pageHeaderSearch').classList.toggle('open')) {
				window.setTimeout(function() {
					elById('pageHeaderSearchInput').focus();
				}, 100);
			}
		});
	})();
</script>
