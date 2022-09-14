<button type="button" id="pageHeaderSearchMobile" class="pageHeaderSearchMobile" aria-expanded="false" aria-label="{lang}wcf.global.search{/lang}">
	{icon size=32 name='magnifying-glass'}
</button>

<div id="pageHeaderSearch" class="pageHeaderSearch" data-disable-auto-focus="true">
	<div class="pageHeaderSearchInputContainer">
		<div id="pageHeaderSearchType" class="pageHeaderSearchType dropdown">
			<a href="#" class="button dropdownToggle" id="pageHeaderSearchTypeSelect"><span class="pageHeaderSearchTypeLabel">{lang}wcf.search.type.everywhere{/lang}</span></a>
			<ul class="dropdownMenu">
				<li><a href="#" data-provider-name="everywhere">{lang}wcf.search.type.everywhere{/lang}</a></li>
				<li class="dropdownDivider"></li>
				
				{foreach from=$availableAcpSearchProviders key='availableAcpSearchProviderName' item='availableAcpSearchProviderLabel'}
					<li><a href="#" data-provider-name="{@$availableAcpSearchProviderName}">{@$availableAcpSearchProviderLabel}</a></li>
				{/foreach}
			</ul>
		</div>
		
		<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" required value="" data-toggle="search">
		
		<button type="button" class="button pageHeaderSearchInputButton" type="submit" title="{lang}wcf.global.search{/lang}">
			{icon name='magnifying-glass'}
		</button>
	</div>
</div>
