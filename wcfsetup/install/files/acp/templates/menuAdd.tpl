{include file='header' pageTitle='wcf.acp.menu.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li><a href="{link controller='MenuItemList' id=$menuID}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.item.list{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='MenuList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='MenuAdd'}{/link}{else}{link controller='MenuEdit' id=$menuID}{/link}{/if}">
	{if $action == 'edit' && $menu->identifier == 'com.woltlab.wcf.MainMenu'}
		{* editing the main menu *}
		<div class="section">
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" autofocus class="long">
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'empty' || $errorType == 'multilingual'}
								{lang}wcf.global.form.error.{@$errorType}{/lang}
							{else}
								{lang}wcf.acp.menu.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					{include file='shared_multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
				</dd>
			</dl>
		</div>
	{else}
		{* anything, but the main menu *}
		<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem" id="pageTabMenuContainer">
			<nav class="tabMenu">
				<ul>
					<li><a href="#general">{lang}wcf.global.form.data{/lang}</a></li>
					<li><a href="#pages">{lang}wcf.acp.page.list{/lang}</a></li>
					<li><a href="#acl">{lang}wcf.acl.access{/lang}</a></li>
					
					{event name='tabMenuTabs'}
				</ul>
			</nav>
			
			<div id="general" class="tabMenuContent">
				<div class="section">
					<dl{if $errorField == 'title'} class="formError"{/if}>
						<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
						<dd>
							<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" autofocus class="long">
							{if $errorField == 'title'}
								<small class="innerError">
									{if $errorType == 'empty' || $errorType == 'multilingual'}
										{lang}wcf.global.form.error.{@$errorType}{/lang}
									{else}
										{lang}wcf.acp.menu.title.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
							{include file='shared_multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
						</dd>
					</dl>
					
					<dl{if $errorField == 'position'} class="formError"{/if}>
						<dt><label for="position">{lang}wcf.acp.box.position{/lang}</label></dt>
						<dd>
							<select name="position" id="position">
								{foreach from=$availablePositions item=availablePosition}
									<option value="{$availablePosition}"{if $availablePosition == $position} selected{/if}>{lang}wcf.acp.box.position.{@$availablePosition}{/lang}</option>
								{/foreach}
							</select>
							
							{if $errorField == 'position'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.box.position.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl>
						<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
						<dd>
							<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="tiny" min="0">
						</dd>
					</dl>
					
					<dl{if $errorField == 'cssClassName'} class="formError"{/if}>
						<dt><label for="cssClassName">{lang}wcf.acp.box.cssClassName{/lang}</label></dt>
						<dd>
							<input type="text" id="cssClassName" name="cssClassName" value="{$cssClassName}" class="long">
							{if $errorField == 'cssClassName'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.box.cssClassName.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="showHeader" name="showHeader" value="1"{if $showHeader} checked{/if}> {lang}wcf.acp.box.showHeader{/lang}</label>
						</dd>
					</dl>
				</div>
			</div>
			
			<div id="pages" class="tabMenuContent">
				<div class="section">
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="visibleEverywhere" name="visibleEverywhere" value="1"{if $visibleEverywhere} checked{/if}> {lang}wcf.acp.box.visibleEverywhere{/lang}</label>
							<script data-relocate="true">
								elById('visibleEverywhere').addEventListener('change', function() {
									if (this.checked) {
										elShow(elById('visibilityExceptionHidden'));
										elHide(elById('visibilityExceptionVisible'));
									}
									else {
										elHide(elById('visibilityExceptionHidden'));
										elShow(elById('visibilityExceptionVisible'));
									}
								});
							</script>
						</dd>
					</dl>
					
					<dl>
						<dt>
							<span id="visibilityExceptionVisible"{if $visibleEverywhere} style="display: none"{/if}>{lang}wcf.acp.box.visibilityException.visible{/lang}</span>
							<span id="visibilityExceptionHidden"{if !$visibleEverywhere} style="display: none"{/if}>{lang}wcf.acp.box.visibilityException.hidden{/lang}</span>
						</dt>
						<dd>
							{include file='shared_scrollablePageCheckboxList' pageCheckboxListContainerID='menuVisibilitySettings' pageCheckboxID='pageIDs'}
						</dd>
					</dl>
					
					{event name='dataFields'}
				</div>
			</div>
			
			<div id="acl" class="tabMenuContent">
				{include file='shared_aclSimple'}
			</div>
		</div>
	{/if}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
