{if $__searchTypeLabel|empty}
	{capture assign='__searchTypeLabel'}{lang}wcf.search.type.{if !$__searchObjectTypeName|empty}{@$__searchObjectTypeName}{else}everywhere{/if}{/lang}{/capture}
{/if}

{if MODULE_ARTICLE && SEARCH_ENABLE_ARTICLES && $__wcf->getActivePage() != null && ($__wcf->getActivePage()->identifier == 'com.woltlab.wcf.ArticleList' || $__wcf->getActivePage()->identifier == 'com.woltlab.wcf.CategoryArticleList' || $__wcf->getActivePage()->identifier == 'com.woltlab.wcf.Article')}
	{if $category|isset}
		{capture assign='__searchTypeLabel'}{$category->getTitle()}{/capture}
	{else}
		{capture assign='__searchTypeLabel'}{lang}wcf.search.type.com.woltlab.wcf.article{/lang}{/capture}
	{/if}
	
	{assign var='__searchObjectTypeName' value='com.woltlab.wcf.article'}
	
	{capture assign='__searchTypesScoped'}
		{if $category|isset}<li><a href="#" data-extended-link="{link controller='Search'}type=com.woltlab.wcf.article&extended=1{/link}" data-object-type="com.woltlab.wcf.article" data-parameters='{ "articleCategoryID": {@$category->categoryID} }'>{$category->getTitle()}</a></li>{/if}
	{/capture}
	{assign var='__searchAreaInitialized' value=true}
{/if}

{event name='settings'}

<button type="button" id="pageHeaderSearchMobile" class="pageHeaderSearchMobile" aria-expanded="false" aria-label="{lang}wcf.global.search{/lang}">
	{icon size=32 name='magnifying-glass'}
</button>

<div id="pageHeaderSearch" class="pageHeaderSearch">
	<form method="post" action="{link controller='Search'}{/link}">
		<div id="pageHeaderSearchInputContainer" class="pageHeaderSearchInputContainer">
			<div class="pageHeaderSearchType dropdown">
				<a href="#" class="button dropdownToggle" id="pageHeaderSearchTypeSelect">
					<span class="pageHeaderSearchTypeLabel">{@$__searchTypeLabel}</span>
					{icon name='angle-down' type='solid'}
				</a>
				<ul class="dropdownMenu">
					<li><a href="#" data-extended-link="{link controller='Search'}extended=1{/link}" data-object-type="everywhere">{lang}wcf.search.type.everywhere{/lang}</a></li>
					<li class="dropdownDivider"></li>
					
					{hascontent}
						{content}
							{if !$__searchTypesScoped|empty}{@$__searchTypesScoped}{/if}
						{/content}
						
						<li class="dropdownDivider"></li>
					{/hascontent}
					
					{foreach from=$__wcf->getSearchEngine()->getAvailableObjectTypes() key=_searchObjectTypeName item=_searchObjectType}
						{if $_searchObjectType->isAccessible()}
							<li><a href="#" data-extended-link="{link controller='Search'}type={@$_searchObjectTypeName}&extended=1{/link}" data-object-type="{@$_searchObjectTypeName}">{lang}wcf.search.type.{@$_searchObjectTypeName}{/lang}</a></li>
						{/if}
					{/foreach}
					
					<li class="dropdownDivider"></li>
					<li><a class="pageHeaderSearchExtendedLink" href="{link controller='Search'}extended=1{/link}">{lang}wcf.search.extended{/lang}</a></li>
				</ul>
			</div>
			
			<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" value="{if $query|isset}{$query}{/if}">
			
			<button type="submit" class="pageHeaderSearchInputButton button" title="{lang}wcf.global.search{/lang}">
				{icon name='magnifying-glass'}
			</button>
			
			<div id="pageHeaderSearchParameters"></div>
			
			{if !$__searchStaticOptions|empty}{@$__searchStaticOptions}{/if}
		</div>
	</form>
</div>

{if (!OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')) && (!FORCE_LOGIN || $__wcf->user->userID)}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Search/Page'], function(UiSearchPage) {
			UiSearchPage.init('{if !$__searchObjectTypeName|empty}{@$__searchObjectTypeName}{elseif !$searchPreselectObjectType|empty}{$searchPreselectObjectType}{else}everywhere{/if}');
		});
	</script>
{/if}
