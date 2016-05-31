<div id="pageHeaderSearch" class="pageHeaderSearch" data-disable-auto-focus="true">
	<div class="pageHeaderSearchInputContainer">
		<div id="pageHeaderSearchType" class="pageHeaderSearchType dropdown">
			<a href="#" class="button dropdownToggle">{lang}wcf.search.type.everywhere{/lang}</a>
			<ul class="dropdownMenu">
				<li><a href="#" data-provider-name="everywhere">{lang}wcf.search.type.everywhere{/lang}</a></li>
				<li class="dropdownDivider"></li>
				
				{foreach from=$availableAcpSearchProviders item='availableAcpSearchProvider'}
					<li><a href="#" data-provider-name="{@$availableAcpSearchProvider->providerName}">{lang}wcf.acp.search.provider.{@$availableAcpSearchProvider->providerName}{/lang}</a></li>
				{/foreach}
			</ul>
		</div>
		
		<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" required="required" value="" data-toggle="search" />
		
		<button class="pageHeaderSearchInputButton" type="submit">
			<span class="icon icon16 pointer fa-search" title="{lang}wcf.global.search{/lang}"></span>
		</button>
	</div>
</div>
