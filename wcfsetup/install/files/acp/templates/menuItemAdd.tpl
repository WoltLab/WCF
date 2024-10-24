{include file='header' pageTitle='wcf.acp.menu.item.'|concat:$action}

<script data-relocate="true">
	require(['Dictionary', 'Language', 'WoltLabSuite/Core/Acp/Ui/Menu/Item/Handler'], function(Dictionary, Language, AcpUiMenuItemHandler) {
		Language.addObject({
			'wcf.page.pageObjectID': '{jslang}wcf.page.pageObjectID{/jslang}',
			{foreach from=$pageNodeList item=pageNode}
				{capture assign='pageObjectIDLanguageItem'}{lang __optional=true}wcf.page.pageObjectID.{@$pageNode->identifier}{/lang}{/capture}
				{if $pageObjectIDLanguageItem}
					'wcf.page.pageObjectID.{@$pageNode->identifier}': '{@$pageObjectIDLanguageItem|encodeJS}',
				{/if}
				{capture assign='pageObjectIDLanguageItem'}{lang __optional=true}wcf.page.pageObjectID.search.{@$pageNode->identifier}{/lang}{/capture}
				{if $pageObjectIDLanguageItem}
					'wcf.page.pageObjectID.search.{@$pageNode->identifier}': '{@$pageObjectIDLanguageItem|encodeJS}',
				{/if}
			{/foreach}
			'wcf.page.pageObjectID.search.noResults': '{jslang}wcf.page.pageObjectID.search.noResults{/jslang}',
			'wcf.page.pageObjectID.search.results': '{jslang}wcf.page.pageObjectID.search.results{/jslang}',
			'wcf.page.pageObjectID.search.terms': '{jslang}wcf.page.pageObjectID.search.terms{/jslang}'
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
		<p class="contentHeaderDescription">{lang}wcf.acp.menu.item.action.description{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				{*
				Technically this dropdown should check whether the number of menu items is larger than one,
				but this is non-trivial with the iterator. It's unlikely that there's only a single menu item,
				thus we let this slip.
				*}
				<li class="dropdown">
					<a class="button dropdownToggle">{icon name='sort'} <span>{lang}wcf.acp.menu.item.button.choose{/lang}</span></a>
					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$menuItemNodeList item='menuItemNode'}
								<li{if $menuItemNode->itemID == $itemID} class="active"{/if}><a href="{link controller='MenuItemEdit' object=$menuItemNode}{/link}">{if $menuItemNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($menuItemNode->getDepth() - 1)}{/if}{$menuItemNode->getTitle()}</a></li>
							{/foreach}
						</ul>
					</div>
				</li>
			{/if}
			<li><a href="{link controller='MenuItemList' id=$menuID}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.item.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='MenuItemAdd'}{/link}{else}{link controller='MenuItemEdit' id=$itemID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'parentItemID'} class="formError"{/if}>
			<dt><label for="parentItemID">{lang}wcf.acp.menu.item.parentItem{/lang}</label></dt>
			<dd>
				<select name="parentItemID" id="parentItemID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$menuItemNodeList item=menuItemNode}
						<option
							value="{$menuItemNode->itemID}"
							{if $menuItemNode->itemID == $parentItemID} selected{/if}
							{if $action === 'edit' && $menuItemNode->itemID == $itemID} disabled{/if}
						>
							{if $menuItemNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($menuItemNode->getDepth() - 1)}{/if}{$menuItemNode->getTitle()}
						</option>
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
				<input type="text" name="title" id="title" value="{$i18nPlainValues['title']}" maxlength="255" class="long" required>
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'empty' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{@$errorType}{/lang}
						{else}
							{lang}wcf.acp.menu.item.title.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				
				{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" name="showOrder" id="showOrder" value="{$showOrder}" class="tiny" min="0">
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" id="isDisabled" value="1"{if $isDisabled} checked{/if}> <span>{lang}wcf.acp.menu.item.isDisabled{/lang}</span></label>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.menu.item.link{/lang}</h2>
	
		<dl>
			<dt></dt>
			<dd class="floated">
				<label><input type="radio" name="isInternalLink" value="1"{if $isInternalLink} checked{/if}> {lang}wcf.acp.menu.item.link.internal{/lang}</label>
				<label><input type="radio" name="isInternalLink" value="0"{if !$isInternalLink} checked{/if}> {lang}wcf.acp.menu.item.link.external{/lang}</label>
			</dd>
		</dl>
		
		<dl id="pageIDContainer"{if $errorField == 'pageID'} class="formError"{/if}{if !$isInternalLink} style="display: none;"{/if}>
			<dt><label for="pageID">{lang}wcf.acp.page.page{/lang}</label></dt>
			<dd>
				<select name="pageID" id="pageID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$pageNodeList item=pageNode}
						{if !$pageNode->requireObjectID || $pageHandlers[$pageNode->pageID]|isset}
							<option value="{$pageNode->pageID}"{if $pageNode->pageID == $pageID} selected{/if} data-identifier="{@$pageNode->identifier}">{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->name}</option>
						{/if}
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
			<dt><label for="pageObjectID">{lang}wcf.page.pageObjectID{/lang}</label></dt>
			<dd>
				<div class="inputAddon">
					<input type="text" id="pageObjectID" name="pageObjectID" value="{$pageObjectID}" class="short">
					<a href="#" id="searchPageObjectID" class="inputSuffix button jsTooltip" title="{lang}wcf.page.pageObjectID.search{/lang}">{icon name='magnifying-glass'}</a>
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
				<input type="text" name="externalURL" id="externalURL" value="{$externalURL}" class="long" maxlength="255" placeholder="http://">
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
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}">
		{if $action == 'add'}<input type="hidden" name="menuID" value="{$menuID}">{/if}
		{csrfToken}
	</div>
</form>

{include file='footer'}
