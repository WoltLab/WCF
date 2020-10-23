{include file='header' pageTitle='wcf.acp.modificationLog.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.modificationLog.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if !$unsupportedObjectTypes|empty}
	<div class="warning">
		<p>{lang}wcf.acp.modificationLog.unsupportedObjectTypes{/lang}</p>
		<ul class="nativeList">
			{foreach from=$unsupportedObjectTypes item=unsupportedObjectType}
				<li><kbd>{$unsupportedObjectType->objectType}</kbd> ({$unsupportedObjectType->getPackage()})</li>
			{/foreach}
		</ul>
	</div>
{/if}

<form method="post" action="{link controller='ModificationLogList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="" placeholder="{lang}wcf.user.username{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-8">
				<dt></dt>
				<dd>
					<select name="action" id="action">
						<option value=""{if $action === ''} selected{/if}>{lang}wcf.acp.modificationLog.action.all{/lang}</option>
						{if !$actions|empty}<option disabled>-----</option>{/if}
						
						{foreach from=$actions key=_packageID item=$availableActions}
							{assign var=_package value=$packages[$_packageID]}
							
							<option value="{@$_package->packageID}"{if $action == $_package->packageID} selected{/if}>{lang package=$_package}wcf.acp.modificationLog.action.allPackageActions{/lang}</option>
							{foreach from=$availableActions key=actionName item=actionLabel}
								<option value="{$actionName}"{if $action === $actionName} selected{/if}>{@'&nbsp;'|str_repeat:4}{$actionLabel}</option>
							{/foreach}
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="date" name="afterDate" id="afterDate" value="{$afterDate}" placeholder="{lang}wcf.acp.modificationLog.time.afterDate{/lang}">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="date" name="beforeDate" id="beforeDate" value="{$beforeDate}" placeholder="{lang}wcf.acp.modificationLog.time.beforeDate{/lang}">
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</section>
</form>

{capture assign=pageParameters}{if $username}&username={$username}{/if}{if $action}&action={$action}{/if}{if $afterDate}&afterDate={$afterDate}{/if}{if $beforeDate}&beforeDate={$beforeDate}{/if}{/capture}
{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="ModificationLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$pageParameters"}{/content}
	</div>
{/hascontent}

{if $logItems|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnLogID{if $sortField == 'logID'} active {@$sortOrder}{/if}"><a href="{link controller='ModificationLogList'}pageNo={@$pageNo}&sortField=logID&sortOrder={if $sortField == 'logID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$pageParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='ModificationLogList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$pageParameters}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnText columnAction">{lang}wcf.acp.modificationLog.action{/lang}</th>
					<th class="columnText columnAffectedObject">{lang}wcf.acp.modificationLog.affectedObject{/lang}</th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='ModificationLogList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$pageParameters}{/link}">{lang}wcf.global.date{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$logItems item=modificationLog}
					{assign var=_objectType value=$objectTypes[$modificationLog->objectTypeID]}
					
					<tr>
						<td class="columnID columnLogID">{@$modificationLog->logID}</td>
						<td class="columnText columnUsername">{if $modificationLog->userID}<a href="{link controller='User' id=$modificationLog->userID title=$modificationLog->username forceFrontend=true}{/link}">{$modificationLog->username}</a>{else}{$modificationLog->username}{/if}</td>
						<td class="columnText columnAction">{lang}wcf.acp.modificationLog.{$_objectType->objectType}.{$modificationLog->action}{/lang}</td>
						<td class="columnText columnAffectedObject" title="{lang}wcf.acp.modificationLog.affectedObject.id{/lang}">
							{if $modificationLog->getAffectedObject()}
								<a href="{$modificationLog->getAffectedObject()->getLink()}">{$modificationLog->getAffectedObject()->getTitle()}</a>
							{else}
								<small>{lang}wcf.acp.modificationLog.affectedObject.unknown{/lang}</small>
							{/if}
						</td>
						<td class="columnDate columnTime">{@$modificationLog->time|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
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
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
