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
		
		$('#isDisabled').change(function(event) {
			if ($('#isDisabled')[0].checked) {
				$('#isLandingPage')[0].checked = false;
				$('#isLandingPage')[0].disabled = true;
			}
			else {
				$('#isLandingPage')[0].disabled = false;
			}
		}).trigger('change');
	});
</script>

<header class="boxHeadline">
	<h1>{if $action == 'add'}{if $isMultilingual}{lang}wcf.acp.page.addMultilingual{/lang}{else}{lang}wcf.acp.page.add{/lang}{/if}{else}{lang}wcf.acp.page.edit{/lang}{/if}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.cms.page.list{/lang}</span></a></li>
				
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='PageAdd'}{/link}{else}{link controller='PageEdit' id=$pageID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'displayName'} class="formError"{/if}>
				<dt><label for="displayName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="displayName" name="displayName" value="{$displayName}" required="required" autofocus="autofocus" class="long" />
					{if $errorField == 'displayName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.page.displayName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{if $action == 'add' || !$page->controller}
				<dl{if $errorField == 'parentPageID'} class="formError"{/if}>
					<dt><label for="parentPageID">{lang}wcf.acp.page.parentPageID{/lang}</label></dt>
					<dd>
						<select name="parentPageID" id="parentPageID">
							<option value="0">{lang}wcf.acp.page.parentPageID.noParentPage{/lang}</option>
							
							{foreach from=$pageNodeList item=pageNode}
								<option value="{@$pageNode->getPage()->pageID}"{if $pageNode->getPage()->pageID == $parentPageID} selected="selected"{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->getPage()->displayName}</option>
							{/foreach}
						</select>
						{if $errorField == 'parentPageID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.page.parentPageID.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'packageID'} class="formError"{/if}>
					<dt><label for="packageID">{lang}wcf.acp.page.packageID{/lang}</label></dt>
					<dd>
						<select name="packageID" id="packageID">
							{foreach from=$availableApplications item=availableApplication}
								<option value="{@$availableApplication->packageID}"{if $availableApplication->packageID == $packageID} selected="selected"{/if}>{$availableApplication->domainName}{$availableApplication->domainPath}</option>
							{/foreach}
						</select>
						{if $errorField == 'parentPageID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.page.packageID.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
			{/if}
			
			{if !$isMultilingual}
				<dl{if $errorField == 'customURL'} class="formError"{/if}>
					<dt><label for="customURL">{lang}wcf.acp.page.customURL{/lang}</label></dt>
					<dd>
						<input type="text" id="customURL" name="customURL[0]" value="{if !$customURL[0]|empty}{$customURL[0]}{/if}" class="long" />
						{if $errorField == 'customURL'}
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
			{/if}
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="isLandingPage" name="isLandingPage" value="1" {if $isLandingPage}checked="checked" {/if}{if $action == 'edit' && $page->isLandingPage}disabled="disabled" {/if}/> {lang}wcf.acp.page.isLandingPage{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="isDisabled" name="isDisabled" value="1" {if $isDisabled}checked="checked" {/if}/> {lang}wcf.acp.page.isDisabled{/lang}</label>
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		{if $action == 'add' || !$page->controller}
			{if !$isMultilingual}
				<fieldset>
					<legend>content</legend>
				
					<dl{if $errorField == 'title'} class="formError"{/if}>
						<dt><label for="title">{lang}wcf.acp.page.title{/lang}</label></dt>
						<dd>
							<input type="text" id="title" name="title[0]" value="{if !$title[0]|empty}{$title[0]}{/if}" class="long" />
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
							<textarea name="content[0]" id="content0">{if !$content[0]|empty}{$content[0]}{/if}</textarea>
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
					
					<dl{if $errorField == 'metaKeywords'} class="formError"{/if}>
						<dt><label for="metaKeywords">{lang}wcf.acp.page.metaKeywords{/lang}</label></dt>
						<dd>
							<textarea name="metaKeywords[0]" id="metaKeywords">{if !$metaKeywords[0]|empty}{$metaKeywords[0]}{/if}</textarea>
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
					
					<dl{if $errorField == 'metaDescription'} class="formError"{/if}>
						<dt><label for="metaDescription">{lang}wcf.acp.page.metaDescription{/lang}</label></dt>
						<dd>
							<textarea name="metaDescription[0]" id="metaDescription">{if !$metaDescription[0]|empty}{$metaDescription[0]}{/if}</textarea>
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
				</fieldset>
			{else}
				<div class="tabMenuContainer">
					<nav class="tabMenu">
						<ul>
							{foreach from=$availableLanguages item=availableLanguage}
								{assign var='containerID' value='language'|concat:$availableLanguage->languageID}
								<li><a href="{@$__wcf->getAnchor($containerID)}">{$availableLanguage->languageName}</a></li>
							{/foreach}
						</ul>
					</nav>
					
					{foreach from=$availableLanguages item=availableLanguage}
						<div id="language{@$availableLanguage->languageID}" class="container containerPadding tabMenuContent">
							<fieldset>
								<dl{if $errorField == 'customURL'} class="formError"{/if}>
									<dt><label for="customURL{@$availableLanguage->languageID}">{lang}wcf.acp.page.customURL{/lang}</label></dt>
									<dd>
										<input type="text" id="customURL{@$availableLanguage->languageID}" name="customURL[{@$availableLanguage->languageID}]" value="{if !$customURL[$availableLanguage->languageID]|empty}{$customURL[$availableLanguage->languageID]}{/if}" class="long" />
										{if $errorField == 'customURL'}
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
							
								<dl{if $errorField == 'title'} class="formError"{/if}>
									<dt><label for="title{@$availableLanguage->languageID}">{lang}wcf.acp.page.title{/lang}</label></dt>
									<dd>
										<input type="text" id="title{@$availableLanguage->languageID}" name="title[{@$availableLanguage->languageID}]" value="{if !$title[$availableLanguage->languageID]|empty}{$title[$availableLanguage->languageID]}{/if}" class="long" />
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
									<dt><label for="content{@$availableLanguage->languageID}">{lang}wcf.acp.page.content{/lang}</label></dt>
									<dd>
										<textarea name="content[{@$availableLanguage->languageID}]" id="content{@$availableLanguage->languageID}">{if !$content[$availableLanguage->languageID]|empty}{$content[$availableLanguage->languageID]}{/if}</textarea>
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
								
								<dl{if $errorField == 'metaKeywords'} class="formError"{/if}>
									<dt><label for="metaKeywords{@$availableLanguage->languageID}">{lang}wcf.acp.page.metaKeywords{/lang}</label></dt>
									<dd>
										<textarea name="metaKeywords[{@$availableLanguage->languageID}]" id="metaKeywords{@$availableLanguage->languageID}">{if !$metaKeywords[$availableLanguage->languageID]|empty}{$metaKeywords[$availableLanguage->languageID]}{/if}</textarea>
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
								
								<dl{if $errorField == 'metaDescription'} class="formError"{/if}>
									<dt><label for="metaDescription{@$availableLanguage->languageID}">{lang}wcf.acp.page.metaDescription{/lang}</label></dt>
									<dd>
										<textarea name="metaDescription[{@$availableLanguage->languageID}]" id="metaDescription{@$availableLanguage->languageID}">{if !$metaDescription[$availableLanguage->languageID]|empty}{$metaDescription[$availableLanguage->languageID]}{/if}</textarea>
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
							</fieldset>
						</div>
					{/foreach}
				</div>
			{/if}
		{/if}
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="isMultilingual" value="{@$isMultilingual}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
