{capture assign='__searchFormLink'}{link controller='Search'}{/link}{/capture}
{capture assign='__searchInputPlaceholder'}{lang}wcf.global.search.enterSearchTerm{/lang}{/capture}
{capture assign='__searchDropdownOptions'}<label><input type="checkbox" name="subjectOnly" value="1" /> {lang}wcf.search.subjectOnly{/lang}</label>{/capture}
{assign var='__searchHiddenInputFields' value=''}

{event name='settings'}

<div id="pageHeaderSearch" class="pageHeaderSearch" data-disable-auto-focus="true">
	<form method="post" action="{@$__searchFormLink}">
		<div class="pageHeaderSearchInputContainer">
			<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput" placeholder="{@$__searchInputPlaceholder}" autocomplete="off" required="required" value="{if $query|isset}{$query}{/if}" data-toggle="search" />
			
			<button class="pageHeaderSearchInputButton" type="submit">
				<span class="icon icon16 pointer fa-search" title="{lang}wcf.global.search{/lang}"></span>
			</button>
		</div>
		
		{@$__searchHiddenInputFields}
		{@SECURITY_TOKEN_INPUT_TAG}
	</form>
</div>
