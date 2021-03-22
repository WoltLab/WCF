{capture assign='pageTitle'}{if $searchID}{lang}wcf.user.search.results{/lang}{else}{$__wcf->getActivePage()->getTitle()}{/if}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{if $searchID}{lang}wcf.user.search.results{/lang}{else}{$__wcf->getActivePage()->getTitle()}{/if} <span class="badge">{#$items}</span>{/capture}

{capture assign='canonicalURLParameters'}sortField={@$sortField}&sortOrder={@$sortOrder}{if $letter}&letter={@$letter|rawurlencode}{/if}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='MembersList'}pageNo={@$pageNo+1}&{@$canonicalURLParameters}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='MembersList'}{if $pageNo > 2}pageNo={@$pageNo-1}&{/if}{@$canonicalURLParameters}{/link}">
	{/if}
	<link rel="canonical" href="{link controller='MembersList'}{if $pageNo > 1}pageNo={@$pageNo}&{/if}{@$canonicalURLParameters}{/link}">
{/capture}

{capture assign='sidebarRight'}
	{assign var=encodedLetter value=$letter|rawurlencode}
	<section class="jsOnly box">
		<form method="post" action="{link controller='UserSearch'}{/link}">
			<h2 class="boxTitle"><a href="{link controller='UserSearch'}{/link}">{lang}wcf.user.search{/lang}</a></h2>
			
			<div class="boxContent">
				<dl>
					<dt></dt>
					<dd>
						<input type="text" id="searchUsername" name="username" class="long" placeholder="{lang}wcf.user.username{/lang}">
						{csrfToken}
					</dd>
				</dl>
			</div>
		</form>
	</section>
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{if $searchID}
				{pages print=true assign=pagesLinks controller='MembersList' id=$searchID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&letter=$encodedLetter"}
			{else}
				{pages print=true assign=pagesLinks controller='MembersList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&letter=$encodedLetter"}
			{/if}
		{/content}
	</div>
{/hascontent}

{if $items}
	<div class="section sectionContainerList">
		<div class="containerListDisplayOptions">
			<div class="containerListSortOptions">
				<a rel="nofollow" href="{link controller='MembersList' id=$searchID}pageNo={@$pageNo}&sortField={$sortField}&sortOrder={if $sortOrder == 'ASC'}DESC{else}ASC{/if}{if $letter}&letter={$letter}{/if}{/link}">
					<span class="icon icon16 fa-sort-amount-{$sortOrder|strtolower} jsTooltip" title="{lang}wcf.global.sorting{/lang} ({lang}wcf.global.sortOrder.{if $sortOrder === 'ASC'}ascending{else}descending{/if}{/lang})"></span>
				</a>
				<span class="dropdown">
					<span class="dropdownToggle">{lang}wcf.user.sortField.{$sortField}{/lang}</span>
					
					<ul class="dropdownMenu">
						{foreach from=$validSortFields item=_sortField}
							<li{if $_sortField === $sortField} class="active"{/if}><a rel="nofollow" href="{link controller='MembersList' id=$searchID}pageNo={@$pageNo}&sortField={$_sortField}&sortOrder={if $sortField === $_sortField}{if $sortOrder === 'DESC'}ASC{else}DESC{/if}{else}{$sortOrder}{/if}{if $letter}&letter={$letter}{/if}{/link}">{lang}wcf.user.sortField.{$_sortField}{/lang}</a></li>
						{/foreach}
					</ul>
				</span>
			</div>
			
			{hascontent}
				<div class="containerListActiveFilters">
					<ul class="inlineList">
						{content}
							{if $letter}<li><span class="icon icon16 fa-bold jsTooltip" title="{lang}wcf.user.members.sort.letters{/lang}"></span> {$letter}</li>{/if}
						{/content}
					</ul>
				</div>
			{/hascontent}
			
			<div class="containerListFilterOptions jsOnly">
				<button class="small jsStaticDialog" data-dialog-id="membersListSortFilter"><span class="icon icon16 fa-filter"></span> {lang}wcf.global.filter{/lang}</button>
			</div>
		</div>
		<ol class="containerList userList">
			{foreach from=$objects item=user}
				{include file='userListItem'}
			{/foreach}
		</ol>
	</div>
	
	<div id="membersListSortFilter" class="jsStaticDialogContent" data-title="{lang}wcf.user.members.filter{/lang}">
		<form method="post" action="{link controller='MembersList' id=$searchID}{/link}">
			<div class="section">
				<dl>
					<dt><label for="letter">{lang}wcf.user.members.sort.letters{/lang}</label></dt>
					<dd>
						<select name="letter" id="letter">
							<option value="">{lang}wcf.user.members.sort.letters.all{/lang}</option>
							{foreach from=$letters item=__letter}
								<option value="{$__letter}"{if $__letter == $letter} selected{/if}>{$__letter}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
			</div>
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				<a href="{link controller='MembersList'}{/link}" class="button">{lang}wcf.global.button.reset{/lang}</a>
				<input type="hidden" name="sortField" value="{$sortField}">
				<input type="hidden" name="sortOrder" value="{$sortOrder}">
			</div>
		</form>
	</div>
{else}
	<p class="info" role="status">{lang}wcf.user.members.noMembers{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.user.button.follow': '{jslang}wcf.user.button.follow{/jslang}',
			'wcf.user.button.ignore': '{jslang}wcf.user.button.ignore{/jslang}',
			'wcf.user.button.unfollow': '{jslang}wcf.user.button.unfollow{/jslang}',
			'wcf.user.button.unignore': '{jslang}wcf.user.button.unignore{/jslang}'
		});
		
		new WCF.User.Action.Follow($('.userList > li'));
		new WCF.User.Action.Ignore($('.userList > li'));
	});
	
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		new UiUserSearchInput(document.getElementById('searchUsername'), {
			callbackSelect(item) {
				const link = '{link controller='User' id=2147483646 title='wcftitleplaceholder' encode=false}{/link}';
				window.location = link.replace('2147483646', item.dataset.objectId).replace('wcftitleplaceholder', item.dataset.label);
			}
		});
	});
</script>

{include file='footer'}
