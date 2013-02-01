{if $searchID}
	{assign var='pageTitle' value='wcf.acp.user.search'}
{else}
	{assign var='pageTitle' value='wcf.acp.user.list'}
{/if}

{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var actionObjects = { };
		actionObjects['com.woltlab.wcf.user'] = { };
		actionObjects['com.woltlab.wcf.user']['delete'] = new WCF.Action.Delete('wcf\\data\\user\\UserAction', '.jsUserRow');
		
		WCF.Clipboard.init('wcf\\acp\\page\\UserListPage', {@$hasMarkedItems}, actionObjects);
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#userTableContainer'), 'jsUserRow', options);
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}{@$pageTitle}{/lang}</h1>
	</hgroup>
</header>

{assign var=encodedURL value=$url|rawurlencode}
{assign var=encodedAction value=$action|rawurlencode}
<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserList" link="pageNo=%d&searchID=$searchID&action=$encodedAction&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="{link controller='UserAdd'}{/link}" title="{lang}wcf.acp.user.add{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='UserSearch'}{/link}" title="{lang}wcf.acp.user.search{/lang}" class="button"><span class="icon icon16 icon-search"></span> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

<div id="userTableContainer" class="tabularBox marginTop">
	<nav class="wcf-menu">
		<ul>
			<li{if $action == ''} class="active"{/if}><a href="{link controller='UserList'}{/link}"><span>{lang}wcf.acp.user.list.all{/lang}</span> <span class="wcf-badge" title="{lang}wcf.acp.user.list.count{/lang}">{#$items}</span></a></li>
			
			{event name='userListOptions'}
		</ul>
	</nav>
	
	{hascontent}
		<table data-type="com.woltlab.wcf.user" class="table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll" /></label></th>
					<th class="columnID columnUserID{if $sortField == 'userID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserList'}searchID={@$searchID}&action={@$encodedAction}&pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='UserList'}searchID={@$searchID}&action={@$encodedAction}&pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<th class="column{$column|ucfirst}{if $sortField == $column} active{/if}"><a href="{link controller='UserList'}searchID={@$searchID}&action={@$encodedAction}&pageNo={@$pageNo}&sortField={$column}&sortOrder={if $sortField == $column && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}{$columnLanguageVariable}{/lang}{if $sortField == $column} <span class="icon icon16 icon-sort-{@$sortOrder}"></span>{/if}</a></th>
					{/foreach}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$users item=user}
						<tr class="jsUserRow">
							<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$user->userID}" /></td>
							<td class="columnIcon">
								{if $user->editable}
									<a href="{link controller='UserEdit' id=$user->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
								{else}
									<span class="icon icon16 icon-pencil disabled" title="{lang}wcf.acp.user.edit{/lang}"></span>
								{/if}
								{if $user->deletable}
									<span class="icon icon16 icon-remove jsTooltip jsDeleteButton pointer" title="{lang}wcf.acp.user.delete{/lang}" data-object-id="{@$user->userID}" data-confirm-message="{lang}wcf.acp.user.delete.sure{/lang}"></span>
								{else}
									<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.user.delete{/lang}"></span>
								{/if}
								
								{event name='rowButtons'}
							</td>
							<td class="columnID columnUserID"><p>{@$user->userID}</p></td>
							<td class="columnTitle columnUsername"><p>{if $user->editable}<a title="{lang}wcf.acp.user.edit{/lang}" href="{link controller='UserEdit' id=$user->userID}{/link}">{$user->username}</a>{else}{$user->username}{/if}</p></td>
							
							{foreach from=$columnHeads key=column item=columnLanguageVariable}
								<td class="column{$column|ucfirst}"><p>{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</p></td>
							{/foreach}
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<div class="wcf-clipboardEditor jsClipboardEditor" data-types="[ 'com.woltlab.wcf.user' ]"></div>
		
		<nav>
			<ul>
				{if $__wcf->session->getPermission('admin.user.canAddUser')}
					<li><a href="{link controller='UserAdd'}{/link}" title="{lang}wcf.acp.user.add{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
				{/if}
				<li><a href="{link controller='UserSearch'}{/link}" title="{lang}wcf.acp.user.search{/lang}" class="button"><span class="icon icon16 icon-search"></span> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{hascontentelse}
</div>

<p class="wcf-info">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/hascontent}

{include file='footer'}
