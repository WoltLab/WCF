{include file='header' pageTitle='wcf.acp.box.'|concat:$action}

{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	<script data-relocate="true">
		{include file='mediaJavaScript'}
		
		{if $boxType == 'system'}
			require(['WoltLab/WCF/Acp/Ui/Box/Controller/Handler'], function(AcpUiBoxControllerHandler) {
				AcpUiBoxControllerHandler.init({if $boxController}{@$boxController->objectTypeID}{/if});
			});
		{/if}
		
		require(['Dictionary', 'Language', 'WoltLab/WCF/Acp/Ui/Box/Handler', 'WoltLab/WCF/Media/Manager/Select'], function(Dictionary, Language, AcpUiBoxHandler, MediaManagerSelect) {
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
			
			AcpUiBoxHandler.init(handlers);
			
			new MediaManagerSelect({
				dialogTitle: '{lang}wcf.acp.media.chooseImage{/lang}',
				fileTypeFilters: {
					isImage: 1
				}
			});
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{if $action == 'add'}{lang}wcf.acp.box.add{/lang}{else}{lang}wcf.acp.box.edit{/lang}{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='BoxList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.cms.box.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='BoxAdd'}{/link}{else}{link controller='BoxEdit' id=$boxID}{/link}{/if}">
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem" id="pageTabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('general')}">{lang}wcf.global.form.data{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('contents')}">{lang}wcf.acp.box.contents{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('pages')}">{lang}wcf.acp.page.list{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('acl')}">{lang}wcf.acl.access{/lang}</a></li>
				
				{event name='tabMenuTabs'}
			</ul>
		</nav>
		
		<div id="general" class="tabMenuContent">
			<div class="section">
				<dl{if $errorField == 'name'} class="formError"{/if}>
					<dt><label for="name">{lang}wcf.global.name{/lang}</label></dt>
					<dd>
						<input type="text" id="name" name="name" value="{$name}" required autofocus class="long" maxlength="255">
						{if $errorField == 'name'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.box.name.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl id="boxControllerContainer"{if $errorField == 'boxControllerID'} class="formError"{/if}{if !$boxController} style="display: none;"{/if}>
					<dt><label for="boxControllerID">{lang}wcf.acp.box.boxController{/lang}</label></dt>
					<dd>
						<select name="boxControllerID" id="boxControllerID">
							{foreach from=$availableBoxControllers item=availableBoxController}
								<option value="{@$availableBoxController->objectTypeID}"{if $boxController && $availableBoxController->objectTypeID == $boxController->objectTypeID} selected{/if}>{lang}wcf.acp.box.boxController.{@$availableBoxController->objectType}{/lang}</option>
							{/foreach}
						</select>
						
						{if $errorField == 'boxType'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.box.boxController.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'position'} class="formError"{/if}>
					<dt><label for="position">{lang}wcf.acp.box.position{/lang}</label></dt>
					<dd>
						<select name="position" id="position">
							{foreach from=$availablePositions item=availablePosition}
								<option value="{@$availablePosition}"{if $availablePosition == $position} selected{/if}>{@$availablePosition}</option>
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
						<input type="number" id="showOrder" name="showOrder" value="{@$showOrder}" class="tiny" min="0">
					</dd>
				</dl>
				
				<dl{if $errorField == 'cssClassName'} class="formError"{/if}>
					<dt><label for="cssClassName">{lang}wcf.acp.box.cssClassName{/lang}</label></dt>
					<dd>
						<input type="text" id="cssClassName" name="cssClassName" value="{$cssClassName}" class="long" maxlength="255">
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
			
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.box.link{/lang}</h2>
				
				<dl>
					<dt></dt>
					<dd class="floated">
						<label><input type="radio" name="linkType" value="none"{if $linkType == 'none'} checked{/if}> {lang}wcf.acp.box.linkType.none{/lang}</label>
						<label><input type="radio" name="linkType" value="internal"{if $linkType == 'internal'} checked{/if}> {lang}wcf.acp.box.linkType.internal{/lang}</label>
						<label><input type="radio" name="linkType" value="external"{if $linkType == 'external'} checked{/if}> {lang}wcf.acp.box.linkType.external{/lang}</label>
					</dd>
				</dl>
				
				<dl id="linkPageIDContainer"{if $errorField == 'linkPageID'} class="formError"{/if}{if $linkType != 'internal'} style="display: none;"{/if}>
					<dt><label for="linkPageID">{lang}wcf.acp.page.page{/lang}</label></dt>
					<dd>
						<select name="linkPageID" id="linkPageID">
							<option value="0">{lang}wcf.global.noSelection{/lang}</option>
							
							{foreach from=$pageNodeList item=pageNode}
								<option value="{@$pageNode->pageID}"{if $pageNode->pageID == $linkPageID} selected{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->name}</option>
							{/foreach}
						</select>
						{if $errorField == 'linkPageID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.box.linkPageID.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl id="linkPageObjectIDContainer"{if $errorField == 'linkPageObjectID'} class="formError"{/if}{if !$linkPageID || !$pageHandler[$linkPageID]|isset} style="display: none;"{/if}>
					<dt><label for="linkPageObjectID">{lang}wcf.acp.page.objectID{/lang}</label></dt>
					<dd>
						<div class="inputAddon">
							<input type="text" id="linkPageObjectID" name="linkPageObjectID" value="{$linkPageObjectID}" class="short">
							<a href="#" id="searchLinkPageObjectID" class="inputSuffix button jsTooltip" title="{lang}wcf.acp.page.objectID.search{/lang}"><span class="icon icon16 fa-search"></span></a>
						</div>
						{if $errorField == 'linkPageObjectID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.box.linkPageObjectID.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl id="externalURLContainer"{if $errorField == 'externalURL'} class="formError"{/if}{if $linkType != 'external'} style="display: none;"{/if}>
					<dt><label for="externalURL">{lang}wcf.acp.box.link.externalURL{/lang}</label></dt>
					<dd>
						<input type="text" name="externalURL" id="externalURL" value="{$externalURL}" class="long" maxlength="255" placeholder="http://">
						{if $errorField == 'externalURL'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.box.link.externalURL.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{event name='linkFields'}
			</section>
			
			<div id="boxConditions">
				{if $boxController && $boxController->getProcessor()|is_subclass_of:'wcf\system\box\IConditionBoxController'}
					{@$boxController->getProcessor()->getConditionsTemplate()}
				{/if}
			</div>
		</div>
		
		<div id="contents" class="tabMenuContent">
			{if !$isMultilingual && $boxType != 'system'}
				<div class="section">
					{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
						<dl{if $errorField == 'image'} class="formError"{/if}>
							<dt><label for="image">{lang}wcf.acp.box.image{/lang}</label></dt>
							<dd>
								<div id="imageDisplay">
									{if $images[0]|isset}
										{@$images[0]->getThumbnailTag('small')}
									{/if}
								</div>
								<p class="button jsMediaSelectButton" data-store="imageID0" data-display="imageDisplay">{lang}wcf.acp.media.chooseImage{/lang}</p>
								<input type="hidden" name="imageID[0]" id="imageID0"{if $imageID[0]|isset} value="{@$imageID[0]}"{/if}>
								{if $errorField == 'image'}
									<small class="innerError">{lang}wcf.acp.box.image.error.{@$errorType}{/lang}</small>
								{/if}
							</dd>
						</dl>
					{elseif $action == 'edit' && $images[0]|isset}
						<dl>
							<dt>{lang}wcf.acp.box.image{/lang}</dt>
							<dd>
								<div id="imageDisplay">{@$images[0]->getThumbnailTag('small')}</div>
							</dd>
						</dl>
					{/if}
					
					<dl{if $errorField == 'title'} class="formError"{/if}>
						<dt><label for="title0">{lang}wcf.global.title{/lang}</label></dt>
						<dd>
							<input type="text" id="title0" name="title[0]" value="{if !$title[0]|empty}{$title[0]}{/if}" class="long" maxlength="255">
							{if $errorField == 'title'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.box.title.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorField == 'content'} class="formError"{/if}>
						<dt><label for="content0">{lang}wcf.acp.box.content{/lang}</label></dt>
						<dd>
							{include file='__boxAddContent' languageID=0}
							
							{if $errorField == 'content'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.box.content.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				</div>
			{else}
				<div class="tabMenuContainer">
					<nav class="menu">
						<ul>
							{foreach from=$availableLanguages item=availableLanguage}
								{assign var='containerID' value='language'|concat:$availableLanguage->languageID}
								<li><a href="{@$__wcf->getAnchor($containerID)}">{$availableLanguage->languageName}</a></li>
							{/foreach}
						</ul>
					</nav>
					
					{foreach from=$availableLanguages item=availableLanguage}
						<div id="language{@$availableLanguage->languageID}" class="tabMenuContent">
							<div class="section">
								{if $boxType != 'system'}
									{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
										<dl{if $errorField == 'image'|concat:$availableLanguage->languageID} class="formError"{/if}>
											<dt><label for="image{@$availableLanguage->languageID}">{lang}wcf.acp.box.image{/lang}</label></dt>
											<dd>
												<div id="imageDisplay{@$availableLanguage->languageID}">
													{if $images[$availableLanguage->languageID]|isset}
														{@$images[$availableLanguage->languageID]->getThumbnailTag('small')}
													{/if}
												</div>
												<p class="button jsMediaSelectButton" data-store="imageID{@$availableLanguage->languageID}" data-display="imageDisplay{@$availableLanguage->languageID}">{lang}wcf.acp.media.chooseImage{/lang}</p>
												<input type="hidden" name="imageID[{@$availableLanguage->languageID}]" id="imageID{@$availableLanguage->languageID}"{if $imageID[$availableLanguage->languageID]|isset} value="{@$imageID[$availableLanguage->languageID]}"{/if}>
												{if $errorField == 'image'|concat:$availableLanguage->languageID}
													<small class="innerError">{lang}wcf.acp.box.image.error.{@$errorType}{/lang}</small>
												{/if}
											</dd>
										</dl>
									{elseif $action == 'edit' && $images[$availableLanguage->languageID]|isset}
										<dl>
											<dt>{lang}wcf.acp.box.image{/lang}</dt>
											<dd>
												<div id="imageDisplay">{@$images[$availableLanguage->languageID]->getThumbnailTag('small')}</div>
											</dd>
										</dl>
									{/if}
								{/if}
								
								<dl{if $errorField == 'title'|concat:$availableLanguage->languageID} class="formError"{/if}>
									<dt><label for="title{@$availableLanguage->languageID}">{lang}wcf.global.title{/lang}</label></dt>
									<dd>
										<input type="text" id="title{@$availableLanguage->languageID}" name="title[{@$availableLanguage->languageID}]" value="{if !$title[$availableLanguage->languageID]|empty}{$title[$availableLanguage->languageID]}{/if}" class="long" maxlength="255">
										{if $errorField == 'title'|concat:$availableLanguage->languageID}
											<small class="innerError">
												{if $errorType == 'empty'}
													{lang}wcf.global.form.error.empty{/lang}
												{else}
													{lang}wcf.acp.box.title.error.{@$errorType}{/lang}
												{/if}
											</small>
										{/if}
									</dd>
								</dl>
								
								{if $boxType != 'system'}
									<dl{if $errorField == 'content'|concat:$availableLanguage->languageID} class="formError"{/if}>
										<dt><label for="content{@$availableLanguage->languageID}">{lang}wcf.acp.box.content{/lang}</label></dt>
										<dd>
											{include file='__boxAddContent' languageID=$availableLanguage->languageID}
											
											{if $errorField == 'content'|concat:$availableLanguage->languageID}
												<small class="innerError">
													{if $errorType == 'empty'}
														{lang}wcf.global.form.error.empty{/lang}
													{else}
														{lang}wcf.acp.box.content.error.{@$errorType}{/lang}
													{/if}
												</small>
											{/if}
										</dd>
									</dl>
								{/if}
							</div>
						</div>
					{/foreach}
				</div>
			{/if}
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
						<ul class="scrollableCheckboxList">
							{foreach from=$pageNodeList item=pageNode}
								<li{if $pageNode->getDepth() > 1} style="padding-left: {$pageNode->getDepth()*20-20}px"{/if}>
									<label><input type="checkbox" name="pageIDs[]" value="{@$pageNode->pageID}"{if $pageNode->pageID|in_array:$pageIDs} checked{/if}> {$pageNode->name}</label>
								</li>
							{/foreach}
						</ul>
					</dd>
				</dl>
			</div>
		</div>
		
		<div id="acl" class="tabMenuContent">
			{include file='aclSimple'}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="isMultilingual" value="{@$isMultilingual}">
		<input type="hidden" name="boxType" value="{$boxType}">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
