{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Clipboard.init('wcf\\acp\\page\\UserListPage', {@$hasMarkedItems});
		new WCF.ACP.User.List();
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{if $searchID}search{else}user{/if}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.{if $searchID}search{else}list{/if}{/lang}</h1>
		<h2>{if $searchID}{lang}wcf.acp.user.search.matches{/lang}{/if}</h2><!-- deprecated display -->
	</hgroup>
</header>

{assign var=encodedURL value=$url|rawurlencode}
{assign var=encodedAction value=$action|rawurlencode}
<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserList&pageNo=%d&searchID=$searchID&action=$encodedAction&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
	
	<nav class="largeButtons">
		<ul>
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="index.php?form=UserAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.user.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="index.php?form=UserSearch{@SID_ARG_2ND}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/search1.svg" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<div class="border boxTitle">
	<nav class="menu">
		<ul>
			<li{if $action == ''} class="active"{/if}><a href="index.php?page=UserList{@SID_ARG_2ND}"><span>{lang}wcf.acp.user.list.all{/lang}</span> <span class="badge" title="{lang}wcf.acp.user.list.count{/lang}">{#$items}</span></a></li>
			{if $additionalUserListOptions|isset}{@$additionalUserListOptions}{/if}
		</ul>
	</nav>
	
	{hascontent}
		<table data-type="com.woltlab.wcf.user" class="clipboardContainer">
			<thead>
				<tr class="tableHead">
					<th class="columnMark"><label><input type="checkbox" class="clipboardMarkAll" /></label></th>
					<th class="columnID columnUserID{if $sortField == 'userID'} active{/if}" colspan="2"><a href="index.php?page=UserList&amp;searchID={@$searchID}&amp;action={@$encodedAction}&amp;pageNo={@$pageNo}&amp;sortField=userID&amp;sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.global.objectID{/lang}{if $sortField == 'userID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active{/if}"><a href="index.php?page=UserList&amp;searchID={@$searchID}&amp;action={@$encodedAction}&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<th class="column{$column|ucfirst}{if $sortField == $column} active{/if}"><a href="index.php?page=UserList&amp;searchID={@$searchID}&amp;action={@$encodedAction}&amp;pageNo={@$pageNo}&amp;sortField={$column}&amp;sortOrder={if $sortField == $column && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}{$columnLanguageVariable}{/lang}{if $sortField == $column} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					{/foreach}
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$users item=user}
						<tr id="userRow{@$user->userID}">
							<td class="columnMark"><input type="checkbox" class="clipboardItem" data-objectID="{@$user->userID}" /></td>
							<td class="columnIcon">
								{if $user->editable}
									<a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.acp.user.edit{/lang}" class="balloonTooltip" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/edit1D.svg" alt="" title="{lang}wcf.acp.user.edit{/lang}" />
								{/if}
								{if $user->deletable}
									<a onclick="return confirm('{lang}wcf.acp.user.delete.sure{/lang}')" href="index.php?action=UserDelete&amp;userID={@$user->userID}&amp;url={@$encodedURL}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.user.delete{/lang}" class="balloonTooltip" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.acp.user.delete{/lang}" />
								{/if}
						
								{if $additionalButtons[$user->userID]|isset}{@$additionalButtons[$user->userID]}{/if}
							</td>
							<td class="columnID columnUserID"><p>{@$user->userID}</p></td>
							<td class="columnTitle columnUsername"><p>{if $user->editable}<a title="{lang}wcf.acp.user.edit{/lang}" href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user->username}</a>{else}{$user->username}{/if}</p></td>
					
							{foreach from=$columnHeads key=column item=columnLanguageVariable}
								<td class="column{$column|ucfirst}"><p>{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</p></td>
							{/foreach}
					
							{if $additionalColumns[$user->userID]|isset}{@$additionalColumns[$user->userID]}{/if}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks}
		
		<div data-types="[ 'com.woltlab.wcf.user' ]" class="clipboardEditor"></div>
		
		<nav class="largeButtons">
			<ul>
				{if $__wcf->session->getPermission('admin.user.canAddUser')}
					<li><a href="index.php?form=UserAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.user.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
				{/if}
				<li><a href="index.php?form=UserSearch{@SID_ARG_2ND}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/search1.svg" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</nav>
	</div>
{hascontentelse}
</div>

<p class="info">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/hascontent}

{include file='footer'}
