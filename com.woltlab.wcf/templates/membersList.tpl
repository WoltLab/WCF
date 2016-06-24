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
						{@SECURITY_TOKEN_INPUT_TAG}
					</dd>
				</dl>
			</div>
		</form>
	</section>
	
	<section class="box">
		<h2 class="boxTitle">{lang}wcf.user.members.sort.letters{/lang}</h2>
		
		<div class="boxContent">
			<ul class="buttonList smallButtons letters">
				{foreach from=$letters item=__letter}
					<li><a href="{if $searchID}{link controller='MembersList' id=$searchID}sortField={$sortField}&sortOrder={$sortOrder}&letter={$__letter|rawurlencode}{/link}{else}{link controller='MembersList'}sortField={$sortField}&sortOrder={$sortOrder}&letter={$__letter|rawurlencode}{/link}{/if}" class="button small{if $letter == $__letter} active{/if}">{$__letter}</a></li>
				{/foreach}
				{if !$letter|empty}<li><a href="{if $searchID}{link controller='MembersList' id=$searchID}sortField={$sortField}&sortOrder={$sortOrder}{/link}{else}{link controller='MembersList'}sortField={$sortField}&sortOrder={$sortOrder}{/link}{/if}" class="button small">{lang}wcf.user.members.sort.letters.all{/lang}</a></li>{/if}
			</ul>
		</div>	
	</section>

	<section class="box">
		<form method="post" action="{if $searchID}{link controller='MembersList' id=$searchID}{/link}{else}{link controller='MembersList'}{/link}{/if}">
			<h2 class="boxTitle">{lang}wcf.user.members.sort{/lang}</h2>
			
			<div class="boxContent">
				<dl>
					<dt></dt>
					<dd>
						<select id="sortField" name="sortField">
							<option value="username"{if $sortField == 'username'} selected{/if}>{lang}wcf.user.username{/lang}</option>
							<option value="registrationDate"{if $sortField == 'registrationDate'} selected{/if}>{lang}wcf.user.registrationDate{/lang}</option>
							<option value="activityPoints"{if $sortField == 'activityPoints'} selected{/if}>{lang}wcf.user.activityPoint{/lang}</option>
							{if MODULE_LIKE}<option value="likesReceived"{if $sortField == 'likesReceived'} selected{/if}>{lang}wcf.like.likesReceived{/lang}</option>{/if}
							<option value="lastActivityTime"{if $sortField == 'lastActivityTime'} selected{/if}>{lang}wcf.user.usersOnline.lastActivity{/lang}</option>
							{event name='sortField'}
						</select>
						<select name="sortOrder">
							<option value="ASC"{if $sortOrder == 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
							<option value="DESC"{if $sortOrder == 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
						</select>
					</dd>
				</dl>
				
				<div class="formSubmit">
					<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
					<input type="hidden" name="letter" value="{$letter}">
					{@SID_INPUT_TAG}
				</div>
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
		<ol class="containerList userList">
			{foreach from=$objects item=user}
				{include file='userListItem'}
			{/foreach}
		</ol>
	</div>
{else}
	<p class="info">{lang}wcf.user.members.noMembers{/lang}</p>
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
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
			'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
			'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
			'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
		});
		
		new WCF.User.Action.Follow($('.userList > li'));
		new WCF.User.Action.Ignore($('.userList > li'));
		
		new WCF.Search.User('#searchUsername', function(data) {
			var $link = '{link controller='User' id=2147483646 title='wcfTitlePlaceholder' encode=false}{/link}';
			window.location = $link.replace('2147483646', data.objectID).replace('wcfTitlePlaceholder', data.label);
		}, false, [ ], false);
	});
	//]]>
</script>

{include file='footer'}
