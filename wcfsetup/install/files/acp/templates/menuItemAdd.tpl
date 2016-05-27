{include file='header' pageTitle='wcf.acp.menu.item.'|concat:$action}

<script data-relocate="true">
	require(['Dictionary', 'Language', 'WoltLab/WCF/Acp/Ui/Menu/Item/Handler'], function(Dictionary, Language, AcpUiMenuItemHandler) {
		Language.addObject({
			'wcf.page.pageObjectID.search.noResults': '{lang}wcf.page.pageObjectID.search.noResults{/lang}',
			'wcf.page.pageObjectID.search.results': '{lang}wcf.page.pageObjectID.search.results{/lang}',
			'wcf.page.pageObjectID.search.results.description': '{lang}wcf.page.pageObjectID.search.results.description{/lang}',
			'wcf.page.pageObjectID.search.terms': '{lang}wcf.page.pageObjectID.search.terms{/lang}',
			'wcf.page.pageObjectID.search.terms.description': '{lang}wcf.page.pageObjectID.search.terms.description{/lang}'
		});
		
		var handlers = new Dictionary();
		{foreach from=$pageHandlers key=handlerPageID item=requireObjectID}
			handlers.set({@$handlerPageID}, {if $requireObjectID}true{else}false{/if});
		{/foreach}
		
		AcpUiMenuItemHandler.init(handlers);
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.item.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='MenuItemList' id=$menuID}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.item.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='MenuItemAdd'}{/link}{else}{link controller='MenuItemEdit' id=$itemID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'parentItemID'} class="formError"{/if}>
			<dt><label for="parentItemID">{lang}wcf.acp.menu.item.parentItem{/lang}</label></dt>
			<dd>
				<select name="parentItemID" id="parentItemID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$menuItemNodeList item=menuItemNode}
						<option value="{@$menuItemNode->itemID}"{if $menuItemNode->itemID == $parentItemID} selected="selected"{/if}>{if $menuItemNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($menuItemNode->getDepth() - 1)}{/if}{lang}{$menuItemNode->title}{/lang}</option>
					{/foreach}
				</select>
				{if $errorField == 'parentItemID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.parentItemID.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" name="title" id="title" value="{$title}" class="long" required="required" />
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.menu.item.title.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				
				{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.menu.item.link{/lang}</h2>
	
		<dl>
			<dt></dt>
			<dd class="floated">
				<label><input type="radio" name="isInternalLink" value="1"{if $isInternalLink} checked="checked"{/if} /> {lang}wcf.acp.menu.item.link.internal{/lang}</label>
				<label><input type="radio" name="isInternalLink" value="0"{if !$isInternalLink} checked="checked"{/if} /> {lang}wcf.acp.menu.item.link.external{/lang}</label>
			</dd>
		</dl>
		
		<dl id="pageIDContainer"{if $errorField == 'pageID'} class="formError"{/if}{if !$isInternalLink} style="display: none;"{/if}>
			<dt><label for="pageID">{lang}wcf.acp.page.parentPageID{/lang}</label></dt>
			<dd>
				<select name="pageID" id="pageID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$pageNodeList item=pageNode}
						<option value="{@$pageNode->pageID}"{if $pageNode->pageID == $pageID} selected="selected"{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->name}</option>
					{/foreach}
				</select>
				{if $errorField == 'pageID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.pageID.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl id="pageObjectIDContainer"{if $errorField == 'pageObjectID'} class="formError"{/if}{if !$pageID || !$pageHandler[$pageID]|isset} style="display: none;"{/if}>
			<dt><label for="pageObjectID">{lang}wcf.acp.page.pageObjectID{/lang}</label></dt>
			<dd>
				<div class="inputAddon">
					<input type="text" id="pageObjectID" name="pageObjectID" value="{$pageObjectID}" class="short">
					<a href="#" id="searchPageObjectID" class="inputSuffix button jsTooltip" title="{lang}wcf.acp.page.objectID.search{/lang}"><span class="icon icon16 fa-search"></span></a>
				</div>
				{if $errorField == 'pageObjectID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.pageObjectID.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl id="externalURLContainer"{if $errorField == 'externalURL'} class="formError"{/if}{if $isInternalLink} style="display: none;"{/if}>
			<dt><label for="externalURL">{lang}wcf.acp.menu.item.externalURL{/lang}</label></dt>
			<dd>
				<input type="text" name="externalURL" id="externalURL" value="{$externalURL}" class="long" />
				{if $errorField == 'externalURL'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.externalURL.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				
				{include file='multipleLanguageInputJavascript' elementIdentifier='externalURL' forceSelection=false}
			</dd>
		</dl>
		
		{event name='linkFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.menu.item.advanced{/lang}</h2>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.acp.menu.item.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" name="showOrder" id="showOrder" value="{@$showOrder}" class="tiny" min="0" />
			</dd>
		</dl>
		
		{if $action == 'add' || !$menuItem->isLandingPage}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="isDisabled" id="isDisabled" value="1"{if $isDisabled} checked="checked"{/if} /> <span>{lang}wcf.acp.menu.item.isDisabled{/lang}</span></label>
				</dd>
			</dl>
		{/if}
		
		{event name='advancedFields'}
	</section>
		
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		{if $action == 'add'}<input type="hidden" name="menuID" value="{@$menuID}" />{/if}
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
