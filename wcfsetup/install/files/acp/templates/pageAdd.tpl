{include file='header' pageTitle='wcf.acp.page.'|concat:$action}

<script data-relocate="true">
	$(function() {
		$('#isLandingPage').change(function(event) {
			if ($('#isLandingPage')[0].checked) {
				$('#isDisabled')[0].checked = false;
				$('#isDisabled')[0].disabled = true;
			}
			else {
				$('#isDisabled')[0].disabled = false;
			}
		}).trigger('change');
		
		{if $action != 'edit' || !$page->isLandingPage}
			$('#isDisabled').change(function(event) {
				if ($('#isDisabled')[0].checked) {
					$('#isLandingPage')[0].checked = false;
					$('#isLandingPage')[0].disabled = true;
				}
				else {
					$('#isLandingPage')[0].disabled = false;
				}
			}).trigger('change');
		{/if}
	});
</script>

{if $action == 'add'}
	<script data-relocate="true">
		elById('name').addEventListener('blur', function() {
			var name = this.value;
			name = name.replace(/ /g, '-');
			name = name.replace(/[^a-z0-9-]/gi, '');
			
			{if !$isMultilingual}
				if (elById('customURL').value === '') {
					elById('customURL').value = name;
				}
			{else}
				{foreach from=$availableLanguages item=availableLanguage}
					if (elById('customURL{@$availableLanguage->languageID}').value === '') {
						elById('customURL{@$availableLanguage->languageID}').value = name + '-{@$availableLanguage->languageCode}';
					}
				{/foreach}
			{/if}
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{if $action == 'add'}{lang}wcf.acp.page.add{/lang}{else}{lang}wcf.acp.page.edit{/lang}{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit' && !$page->requireObjectID}
				<li><a href="{$page->getLink()}" class="button"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.acp.page.button.viewPage{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='PageList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.cms.page.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='PageAdd'}{/link}{else}{link controller='PageEdit' id=$pageID}{/link}{/if}">
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem" id="pageTabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('general')}">{lang}wcf.global.form.data{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('contents')}">{lang}wcf.acp.page.contents{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('boxes')}">{lang}wcf.acp.box.list{/lang}</a></li>
				
				{if $action != 'edit' || $page->pageType != 'system'}
					<li><a href="{@$__wcf->getAnchor('acl')}">{lang}wcf.acl.access{/lang}</a></li>
				{/if}
				
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
									{lang}wcf.acp.page.name.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'parentPageID'} class="formError"{/if}>
					<dt><label for="parentPageID">{lang}wcf.acp.page.parentPage{/lang}</label></dt>
					<dd>
						<select name="parentPageID" id="parentPageID"{if $action == 'edit' && $page->originIsSystem} disabled{/if}>
							<option value="0">{lang}wcf.acp.page.parentPage.none{/lang}</option>
							
							{foreach from=$pageNodeList item=pageNode}
								<option value="{@$pageNode->pageID}"{if $pageNode->pageID == $parentPageID} selected{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->name}</option>
							{/foreach}
						</select>
						{if $errorField == 'parentPageID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.page.parentPage.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'applicationPackageID'} class="formError"{/if}>
					<dt><label for="applicationPackageID">{lang}wcf.acp.page.application{/lang}</label></dt>
					<dd>
						<select name="applicationPackageID" id="applicationPackageID"{if $action == 'edit' && $page->originIsSystem} disabled{/if}>
							{foreach from=$availableApplications item=availableApplication}
								<option value="{@$availableApplication->packageID}"{if $availableApplication->packageID == $applicationPackageID} selected{/if}>{$availableApplication->domainName}{$availableApplication->domainPath}</option>
							{/foreach}
						</select>
						{if $errorField == 'applicationPackageID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.page.application.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{if !$isMultilingual}
					<dl{if $errorField == 'customURL_0'} class="formError"{/if}>
						<dt><label for="customURL">{lang}wcf.acp.page.customURL{/lang}</label></dt>
						<dd>
							<input type="text" id="customURL" name="customURL[0]" value="{if !$customURL[0]|empty}{$customURL[0]}{/if}" class="long" maxlength="255">
							{if $errorField == 'customURL_0'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.page.customURL.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				{else}
					{foreach from=$availableLanguages item=availableLanguage}
						{assign var='__errorFieldName' value='customURL_'|concat:$availableLanguage->languageID}
						<dl{if $errorField == $__errorFieldName} class="formError"{/if}>
							<dt><label for="customURL{@$availableLanguage->languageID}">{lang}wcf.acp.page.customURL{/lang} ({$availableLanguage->languageName})</label></dt>
							<dd>
								<input type="text" id="customURL{@$availableLanguage->languageID}" name="customURL[{@$availableLanguage->languageID}]" value="{if !$customURL[$availableLanguage->languageID]|empty}{$customURL[$availableLanguage->languageID]}{/if}" class="long" maxlength="255">
								{if $errorField == $__errorFieldName}
									<small class="innerError">
										{if $errorType == 'empty'}
											{lang}wcf.global.form.error.empty{/lang}
										{else}
											{lang}wcf.acp.page.customURL.error.{@$errorType}{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
					{/foreach}	
				{/if}
				
				{if $action != 'edit' || !$page->requireObjectID}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="isLandingPage" name="isLandingPage" value="1"{if $isLandingPage} checked{/if}{if $action == 'edit' && $page->isLandingPage} disabled{/if}> {lang}wcf.acp.page.isLandingPage{/lang}</label>
						</dd>
					</dl>
				{/if}
				
				{if $action != 'edit' || $page->pageType != 'system'}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="isDisabled" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.page.isDisabled{/lang}</label>
						</dd>
					</dl>
				{/if}
				
				{event name='dataFields'}
			</div>
		</div>
		
		<div id="contents" class="tabMenuContent">
			{if !$isMultilingual && $pageType != 'system'}
				<div class="section">
					<dl{if $errorField == 'title'} class="formError"{/if}>
						<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
						<dd>
							<input type="text" id="title" name="title[0]" value="{if !$title[0]|empty}{$title[0]}{/if}" class="long" maxlength="255">
							{if $errorField == 'title'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.page.title.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorField == 'content'} class="formError"{/if}>
						<dt><label for="content0">{lang}wcf.acp.page.content{/lang}</label></dt>
						<dd>
							{include file='__pageAddContent' languageID=0}
							
							{if $errorField == 'content'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.page.content.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorField == 'metaDescription'} class="formError"{/if}>
						<dt><label for="metaDescription">{lang}wcf.acp.page.metaDescription{/lang}</label></dt>
						<dd>
							<input type="text" class="long" name="metaDescription[0]" id="metaDescription" value="{if !$metaDescription[0]|empty}{$metaDescription[0]}{/if}">
							{if $errorField == 'metaDescription'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.page.metaDescription.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					<dl{if $errorField == 'metaKeywords'} class="formError"{/if}>
						<dt><label for="metaKeywords">{lang}wcf.acp.page.metaKeywords{/lang}</label></dt>
						<dd>
							<input type="text" class="long" name="metaKeywords[0]" id="metaKeywords" value="{if !$metaKeywords[0]|empty}{$metaKeywords[0]}{/if}">
							{if $errorField == 'metaKeywords'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.page.metaKeywords.error.{@$errorType}{/lang}
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
								<dl{if $errorField == 'title'} class="formError"{/if}>
									<dt><label for="title{@$availableLanguage->languageID}">{lang}wcf.global.title{/lang}</label></dt>
									<dd>
										<input type="text" id="title{@$availableLanguage->languageID}" name="title[{@$availableLanguage->languageID}]" value="{if !$title[$availableLanguage->languageID]|empty}{$title[$availableLanguage->languageID]}{/if}" class="long" maxlength="255">
										{if $errorField == 'title'}
											<small class="innerError">
												{if $errorType == 'empty'}
													{lang}wcf.global.form.error.empty{/lang}
												{else}
													{lang}wcf.acp.page.title.error.{@$errorType}{/lang}
												{/if}
											</small>
										{/if}
									</dd>
								</dl>
								
								{if $pageType != 'system'}
									<dl{if $errorField == 'content'} class="formError"{/if}>
										<dt><label for="content{@$availableLanguage->languageID}">{lang}wcf.acp.page.content{/lang}</label></dt>
										<dd>
											{include file='__pageAddContent' languageID=$availableLanguage->languageID}
											
											{if $errorField == 'content'}
												<small class="innerError">
													{if $errorType == 'empty'}
														{lang}wcf.global.form.error.empty{/lang}
													{else}
														{lang}wcf.acp.page.content.error.{@$errorType}{/lang}
													{/if}
												</small>
											{/if}
										</dd>
									</dl>
									
									<dl{if $errorField == 'metaDescription'} class="formError"{/if}>
										<dt><label for="metaDescription{@$availableLanguage->languageID}">{lang}wcf.acp.page.metaDescription{/lang}</label></dt>
										<dd>
											<input type="text" class="long" name="metaDescription[{@$availableLanguage->languageID}]" id="metaDescription{@$availableLanguage->languageID}" value="{if !$metaDescription[$availableLanguage->languageID]|empty}{$metaDescription[$availableLanguage->languageID]}{/if}">
											{if $errorField == 'metaDescription'}
												<small class="innerError">
													{if $errorType == 'empty'}
														{lang}wcf.global.form.error.empty{/lang}
													{else}
														{lang}wcf.acp.page.metaDescription.error.{@$errorType}{/lang}
													{/if}
												</small>
											{/if}
										</dd>
									</dl>
									
									<dl{if $errorField == 'metaKeywords'} class="formError"{/if}>
										<dt><label for="metaKeywords{@$availableLanguage->languageID}">{lang}wcf.acp.page.metaKeywords{/lang}</label></dt>
										<dd>
											<input type="text" class="long" name="metaKeywords[{@$availableLanguage->languageID}]" id="metaKeywords{@$availableLanguage->languageID}" value="{if !$metaKeywords[$availableLanguage->languageID]|empty}{$metaKeywords[$availableLanguage->languageID]}{/if}">
											{if $errorField == 'metaKeywords'}
												<small class="innerError">
													{if $errorType == 'empty'}
														{lang}wcf.global.form.error.empty{/lang}
													{else}
														{lang}wcf.acp.page.metaKeywords.error.{@$errorType}{/lang}
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
		
		<div id="boxes" class="tabMenuContent">
			<div class="section">
				<dl{if $errorField == 'boxIDs'} class="formError"{/if}>
					<dt>{lang}wcf.acp.page.boxes{/lang}</dt>
					<dd>
						<ul class="scrollableCheckboxList">
							{foreach from=$availableBoxes item=availableBox}
								<li>
									<label><input type="checkbox" name="boxIDs[]" value="{@$availableBox->boxID}"{if $availableBox->boxID|in_array:$boxIDs} checked{/if}{if $availableBox->identifier == 'com.woltlab.wcf.MainMenu'} disabled{/if}> {$availableBox->name}</label>
								</li>
							{/foreach}
						</ul>
						{if $errorField == 'boxIDs'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.page.boxIDs.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
			</div>
		</div>
		
		{if $action != 'edit' || $page->pageType != 'system'}
			<div id="acl" class="tabMenuContent">
				{include file='aclSimple'}
			</div>
		{/if}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="isMultilingual" value="{@$isMultilingual}">
		<input type="hidden" name="pageType" value="{$pageType}">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
