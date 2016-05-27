{capture assign='__searchLink'}{link controller='Search'}{/link}{/capture}

{event name='settings'}

<div id="pageHeaderSearch" class="pageHeaderSearch">
	<form method="post" action="{@$__searchLink}">
		<div id="pageHeaderSearchInputContainer" class="pageHeaderSearchInputContainer">
			<div class="pageHeaderSearchType dropdown">
				<a href="#" class="button dropdownToggle">{lang}wcf.search.type.{if !$__searchObjectTypeName|empty}{@$__searchObjectTypeName}{else}everywhere{/if}{/lang}</a>
				<ul class="dropdownMenu">
					<li><a href="#" data-object-type="everywhere">{lang}wcf.search.type.everywhere{/lang}</a></li>
					<li class="dropdownDivider"></li>
					
					{hascontent}
						{content}
							{if !$__searchTypesScoped|empty}{@$__searchTypesScoped}{/if}
						{/content}
						
						<li class="dropdownDivider"></li>
					{/hascontent}
					
					{foreach from=$__wcf->getSearchEngine()->getAvailableObjectTypes() key=_searchObjectTypeName item=_searchObjectType}
						{if $_searchObjectType->isAccessible()}
							<li><a href="#" data-object-type="{@$_searchObjectTypeName}">{lang}wcf.search.type.{@$_searchObjectTypeName}{/lang}</a></li>
						{/if}
					{/foreach}
					
					<li class="dropdownDivider"></li>
					<li><a href="{@$__searchLink}">{lang}wcf.search.extended{/lang}</a></li>
				</ul>
			</div>
			
			<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" value="{if $query|isset}{$query}{/if}" required>
			
			<button class="pageHeaderSearchInputButton button" type="submit">
				<span class="icon icon16 fa-search pointer" title="{lang}wcf.global.search{/lang}"></span>
			</button>
			
			<div id="pageHeaderSearchParameters"></div>
			
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
		
		<label for="pageHeaderSearchInput" class="pageHeaderSearchLabel"></label>
	</form>
</div>

{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
	<script data-relocate="true">
		require(['WoltLab/WCF/Ui/Search/Page'], function(UiSearchPage) {
			UiSearchPage.init('{if !$__searchObjectTypeName|empty}{@$__searchObjectTypeName}{else}everywhere{/if}');
		});
	</script>
{/if}