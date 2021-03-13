{include file='header' pageTitle='wcf.acp.page.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\page\\PageAction', '.jsPageRow');
		new WCF.Action.Toggle('wcf\\data\\page\\PageAction', '.jsPageRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.page.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="#" class="button jsButtonPageAdd"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<form method="post" action="{link controller='PageList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="name" name="name" value="{$name}" placeholder="{lang}wcf.global.name{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="pageTitle" name="title" value="{$title}" placeholder="{lang}wcf.global.title{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="pageContent" name="content" value="{$content}" placeholder="{lang}wcf.acp.page.content{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="applicationPackageID" id="applicationPackageID">
						<option value="0">{lang}wcf.acp.page.application{/lang}</option>
						{foreach from=$availableApplications item=availableApplication}
							<option value="{@$availableApplication->packageID}"{if $availableApplication->packageID == $applicationPackageID} selected{/if}>{$availableApplication->domainName}{$availableApplication->domainPath}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="pageType" id="pageType">
						<option value="">{lang}wcf.acp.page.type{/lang}</option>
						<option value="text"{if $pageType == 'text'} selected{/if}>{lang}wcf.acp.page.type.text{/lang}</option>
						<option value="html"{if $pageType == 'html'} selected{/if}>{lang}wcf.acp.page.type.html{/lang}</option>
						<option value="tpl"{if $pageType == 'tpl'} selected{/if}>{lang}wcf.acp.page.type.tpl{/lang}</option>
						<option value="system"{if $pageType == 'system'} selected{/if}>{lang}wcf.acp.page.type.system{/lang}</option>
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="originIsNotSystem" value="1"{if $originIsNotSystem} checked{/if}> {lang}wcf.acp.page.originIsNotSystem{/lang}</label>
					<label><input type="checkbox" name="controllerCustomURL" value="1"{if $controllerCustomURL} checked{/if}> {lang}wcf.acp.page.customURL{/lang}</label>
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

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $name}{capture append=linkParameters}&name={@$name|rawurlencode}{/capture}{/if}
			{if $title}{capture append=linkParameters}&title={@$title|rawurlencode}{/capture}{/if}
			{if $content}{capture append=linkParameters}&content={@$content|rawurlencode}{/capture}{/if}
			{if $applicationPackageID}{capture append=linkParameters}&applicationPackageID={@$applicationPackageID}{/capture}{/if}
			{if $pageType}{capture append=linkParameters}&pageType={@$pageType|rawurlencode}{/capture}{/if}
			{if $originIsNotSystem}{capture append=linkParameters}&originIsNotSystem=1{/capture}{/if}
			{if $controllerCustomURL}{capture append=linkParameters}&controllerCustomURL=1{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="PageList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnPageID{if $sortField == 'pageID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=pageID&sortOrder={if $sortField == 'pageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnURL">{lang}wcf.acp.page.url{/lang}</th>
					<th class="columnText columnPageType{if $sortField == 'pageType'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=pageType&sortOrder={if $sortField == 'pageType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.page.type{/lang}</a></th>
					<th class="columnDate columnLastUpdateTime{if $sortField == 'lastUpdateTime'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=lastUpdateTime&sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.page.lastUpdateTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=page}
					<tr class="jsPageRow">
						<td class="columnIcon">
							{if $page->canDisable()}
								<span class="icon icon16 fa-{if !$page->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$page->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$page->pageID}"></span>
							{else}
								<span class="icon icon16 fa-{if !$page->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if !$page->isDisabled}disable{else}enable{/if}{/lang}"></span>
							{/if}
							<a href="{link controller='PageEdit' id=$page->pageID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $page->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$page->pageID}" data-confirm-message-html="{lang __encode=true}wcf.acp.page.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							{if !$page->requireObjectID}
								<a href="{$page->getLink()}" title="{lang}wcf.acp.page.button.viewPage{/lang}" class="jsTooltip"><span class="icon icon16 fa-search"></span></a>
							{else}
								<span class="icon icon16 fa-search disabled" title="{lang}wcf.acp.page.button.viewPage{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnPageID">{@$page->pageID}</td>
						<td class="columnTitle columnName">{if $page->isLandingPage}<span class="icon icon16 fa-home jsTooltip" title="{lang}wcf.acp.page.isLandingPage{/lang}"></span> {/if}<a href="{link controller='PageEdit' id=$page->pageID}{/link}">{$page->name}</a></td>
						<td class="columnText columnURL">
							{if $page->applicationPackageID === null}
								<span class="icon icon16 fa-exclamation-triangle red"></span> <span>{lang}wcf.acp.page.application.error.missing{/lang}</span>
							{else}
								{$page->getDisplayLink()}
								{if $page->controllerCustomURL || $page->pageType !== 'system'}
									<span class="icon icon16 fa-exclamation-circle blue jsTooltip" title="{lang}wcf.acp.page.customURL{/lang}"></span>
								{/if}
							{/if}
						</td>
						<td class="columnText columnPageType">{lang}wcf.acp.page.type.{@$page->pageType}{/lang}</td>
						<td class="columnDate columnLastUpdateTime">{@$page->lastUpdateTime|time}</td>
						
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
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="#" class="button jsButtonPageAdd"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='pageAddDialog'}

{include file='footer'}
