{include file='header' pageTitle='wcf.acp.page.'|concat:$action}

{if $action == 'add'}
	<script data-relocate="true">
		elById('name').addEventListener('blur', function() {
			var name = this.value.toLowerCase();
			if (!name) return;
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
{elseif $page->pageType !== 'system'}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Page/Copy'], function (Language, AcpUiPageCopy) {
			Language.addObject({
				'wcf.acp.page.copy': '{jslang}wcf.acp.page.copy{/jslang}'
			});

			AcpUiPageCopy.init();
		});
	</script>
	<div id="acpPageCopyDialog" style="display: none">
		<div>
			{lang}wcf.acp.page.copy.description{/lang}
		</div>

		<div class="formSubmit">
			<a href="{link controller='PageAdd' presetPageID=$page->pageID}{/link}" class="button buttonPrimary">{lang}wcf.global.button.submit{/lang}</a>
		</div>
	</div>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{if $action == 'add'}{lang}wcf.acp.page.add{/lang}{else}{lang}wcf.acp.page.edit{/lang}{/if}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				{if $page->pageType !== 'system'}
					<li><a href="#" class="button jsButtonCopyPage">{icon name='copy'} {lang}wcf.acp.page.button.copyPage{/lang}</a></li>
				{/if}

				{if !$page->requireObjectID}
					<li><a href="{$page->getLink()}" class="button">{icon name='magnifying-glass'} <span>{lang}wcf.acp.page.button.viewPage{/lang}</span></a></li>
				{/if}

				<li><a href="{link controller='PageBoxOrder' id=$page->pageID}{/link}" class="button">{icon name='arrow-down-short-wide'} <span>{lang}wcf.acp.page.button.boxOrder{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='PageList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.cms.page.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $action == 'edit' && !$lastVersion|empty}
	<woltlab-core-notice type="info">{lang}wcf.acp.page.lastVersion{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='PageAdd'}{/link}{else}{link controller='PageEdit' id=$pageID}{/link}{/if}">
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem" id="pageTabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="#general">{lang}wcf.global.form.data{/lang}</a></li>
				<li><a href="#contents">{lang}wcf.acp.page.contents{/lang}</a></li>
				<li><a href="#boxes">{lang}wcf.acp.box.list{/lang}</a></li>

				{if $action != 'edit' || $page->pageType != 'system'}
					<li><a href="#acl">{lang}wcf.acl.access{/lang}</a></li>
				{/if}

				{event name='tabMenuTabs'}
			</ul>
		</nav>

		<div id="general" class="tabMenuContent">
			<div class="section">
				<dl{if $errorField == 'name'} class="formError"{/if}>
					<dt><label for="name">{lang}wcf.global.name{/lang}</label></dt>
					<dd>
						<input type="text" id="name" name="name" value="{$name}" autofocus class="long" maxlength="255">
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
						<select name="parentPageID" id="parentPageID"{if $action == 'edit' && $page->hasFixedParent} disabled{/if}>
							<option value="0">{lang}wcf.acp.page.parentPage.none{/lang}</option>

							{foreach from=$pageNodeList item=pageNode}
								<option value="{$pageNode->pageID}"{if $pageNode->pageID == $parentPageID} selected{/if}{if $pageNode->requireObjectID || ($action === 'edit' && $pageNode->pageID == $page->pageID)} disabled{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->name}</option>
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

				<dl{if $errorField == 'applicationPackageID'} class="formError"{/if}{if $action == 'edit' && $page->originIsSystem} style="display: none"{/if}>
					<dt><label for="applicationPackageID">{lang}wcf.acp.page.application{/lang}</label></dt>
					<dd>
						<select name="applicationPackageID" id="applicationPackageID"{if $action == 'edit' && $page->originIsSystem} disabled{/if}>
							{foreach from=$availableApplications item=availableApplication}
								<option value="{$availableApplication->packageID}"{if $availableApplication->packageID == $applicationPackageID} selected{/if}>{$availableApplication->domainName}{$availableApplication->domainPath}</option>
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

				{if $action === 'edit' && $page->originIsSystem}
					<dl{if $errorField == 'overrideApplicationPackageID'} class="formError"{/if}>
						<dt><label for="overrideApplicationPackageID">{lang}wcf.acp.page.application{/lang}</label></dt>
						<dd>
							<select name="overrideApplicationPackageID" id="overrideApplicationPackageID">
								{assign var='_overrideApplicationPackageID' value=$overrideApplicationPackageID}
								{if !$_overrideApplicationPackageID}{assign var='_overrideApplicationPackageID' value=$page->applicationPackageID}{/if}
								{foreach from=$availableApplications item=availableApplication}
									<option value="{$availableApplication->packageID}"{if $availableApplication->packageID == $_overrideApplicationPackageID} selected{/if}>{$availableApplication->domainName}{$availableApplication->domainPath}</option>
								{/foreach}
							</select>
							{if $errorField == 'overrideApplicationPackageID'}
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
				{/if}

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

				<dl{if $errorField == 'cssClassName'} class="formError"{/if}>
					<dt><label for="cssClassName">{lang}wcf.acp.page.cssClassName{/lang}</label></dt>
					<dd>
						<input type="text" id="cssClassName" name="cssClassName" value="{$cssClassName}" class="long" maxlength="255">
						{if $errorField == 'cssClassName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.page.cssClassName.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>

				{if $action != 'edit' || $page->pageType != 'system'}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="isDisabled" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.page.isDisabled{/lang}</label>
						</dd>
					</dl>
				{/if}

				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" id="availableDuringOfflineMode" name="availableDuringOfflineMode" value="1"{if $availableDuringOfflineMode} checked{/if}> {lang}wcf.acp.page.availableDuringOfflineMode{/lang}</label>
					</dd>
				</dl>

				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" id="allowSpidersToIndex" name="allowSpidersToIndex" value="1"{if $allowSpidersToIndex} checked{/if}> {lang}wcf.acp.page.allowSpidersToIndex{/lang}</label>
					</dd>
				</dl>

				{if $action === 'add'}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="addPageToMainMenu" name="addPageToMainMenu" value="1"{if $addPageToMainMenu} checked{/if}> {lang}wcf.acp.page.addPageToMainMenu{/lang}</label>

							<script data-relocate="true">
								elById('addPageToMainMenu').addEventListener('change', function() {
									if (this.checked) {
										elShow(elById('parentMenuItemDl'));
									}
									else {
										elHide(elById('parentMenuItemDl'));
									}
								});
							</script>
						</dd>
					</dl>

					<dl id="parentMenuItemDl"{if $errorField == 'parentMenuItemID'} class="formError"{/if}{if !$addPageToMainMenu} style="display: none"{/if}>
						<dt><label for="parentMenuItemID">{lang}wcf.acp.menu.item.parentItem{/lang}</label></dt>
						<dd>
							<select name="parentMenuItemID" id="parentMenuItemID">
								<option value="0">{lang}wcf.global.noSelection{/lang}</option>

								{foreach from=$menuItemNodeList item=menuItemNode}
									<option value="{$menuItemNode->itemID}"{if $menuItemNode->itemID == $parentMenuItemID} selected{/if}>{if $menuItemNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($menuItemNode->getDepth() - 1)}{/if}{$menuItemNode->getTitle()}</option>
								{/foreach}
							</select>
							{if $errorField == 'parentMenuItemID'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.page.parentMenuItem.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				{/if}

				{if $pageType !== 'system'}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" id="enableShareButtons" name="enableShareButtons" value="1"{if $enableShareButtons} checked{/if}> {lang}wcf.acp.page.enableShareButtons{/lang}</label>
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

					{event name='informationFields'}

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

					{if $pageType == 'text'}
						{include file='messageFormTabs' wysiwygContainerID='content0'}
					{/if}

					{event name='messageFields'}

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

					{event name='metaFields'}
				</div>
			{else}
				<div class="tabMenuContainer">
					<nav class="menu">
						<ul>
							{foreach from=$availableLanguages item=availableLanguage}
								<li><a href="#language{$availableLanguage->languageID}">{$availableLanguage->languageName}</a></li>
							{/foreach}
						</ul>
					</nav>

					{foreach from=$availableLanguages item=availableLanguage}
						<div id="language{@$availableLanguage->languageID}" class="tabMenuContent">
							<div class="section">
								{assign var='__errorFieldName' value='title_'|concat:$availableLanguage->languageID}
								<dl{if $errorField == $__errorFieldName} class="formError"{/if}>
									<dt><label for="title{@$availableLanguage->languageID}">{lang}wcf.global.title{/lang}</label></dt>
									<dd>
										<input type="text" id="title{@$availableLanguage->languageID}" name="title[{@$availableLanguage->languageID}]" value="{if !$title[$availableLanguage->languageID]|empty}{$title[$availableLanguage->languageID]}{/if}" class="long" maxlength="255">
										{if $errorField == $__errorFieldName}
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

								{if $pageType == 'system'}
									{assign var='__errorFieldName' value='metaDescription_'|concat:$availableLanguage->languageID}
									<dl{if $errorField == $__errorFieldName} class="formError"{/if}>
										<dt><label for="metaDescription{@$availableLanguage->languageID}">{lang}wcf.acp.page.metaDescription{/lang}</label></dt>
										<dd>
											<input type="text" class="long" name="metaDescription[{@$availableLanguage->languageID}]" id="metaDescription{@$availableLanguage->languageID}" value="{if !$metaDescription[$availableLanguage->languageID]|empty}{$metaDescription[$availableLanguage->languageID]}{/if}">
											{if $errorField == $__errorFieldName}
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

									{event name='metaFieldsMultilingualSystemPage'}
								{/if}

								{event name='informationFieldsMultilingual'}

								{if $pageType != 'system'}
									{assign var='__errorFieldName' value='content_'|concat:$availableLanguage->languageID}
									<dl{if $errorField == $__errorFieldName} class="formError"{/if}>
										<dt><label for="content{@$availableLanguage->languageID}">{lang}wcf.acp.page.content{/lang}</label></dt>
										<dd>
											{include file='__pageAddContent' languageID=$availableLanguage->languageID}

											{if $errorField == $__errorFieldName}
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

									{if $pageType == 'text'}
										{include file='messageFormTabs' wysiwygContainerID='content'|concat:$availableLanguage->languageID}
									{/if}

									{event name='messageFieldsMultilingual'}

									{assign var='__errorFieldName' value='metaDescription_'|concat:$availableLanguage->languageID}
									<dl{if $errorField == $__errorFieldName} class="formError"{/if}>
										<dt><label for="metaDescription{@$availableLanguage->languageID}">{lang}wcf.acp.page.metaDescription{/lang}</label></dt>
										<dd>
											<input type="text" class="long" name="metaDescription[{@$availableLanguage->languageID}]" id="metaDescription{@$availableLanguage->languageID}" value="{if !$metaDescription[$availableLanguage->languageID]|empty}{$metaDescription[$availableLanguage->languageID]}{/if}">
											{if $errorField == $__errorFieldName}
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

									{event name='metaFieldsMultilingual'}
								{/if}
							</div>
						</div>
					{/foreach}
				</div>
			{/if}
		</div>

		<div id="boxes" class="tabMenuContent">
			<div class="section">
				<woltlab-core-notice type="info">{lang}wcf.acp.page.boxOrder.page{@$action|ucfirst}{/lang}</woltlab-core-notice>
				
				<dl{if $errorField == 'boxIDs'} class="formError"{/if}>
					<dt>{lang}wcf.acp.page.boxes{/lang}</dt>
					<dd>
						<ul class="scrollableCheckboxList" id="boxVisibilitySettings">
							{foreach from=$availableBoxes item=availableBox}
								<li>
									<label>
										<input type="checkbox" name="boxIDs[]" value="{$availableBox->boxID}"{if $availableBox->boxID|in_array:$boxIDs} checked{/if}{if $availableBox->identifier == 'com.woltlab.wcf.MainMenu'} disabled{/if}>
										{$availableBox->name}
										{if $availableBox->isDisabled}
											<span class="jsTooltip" title="{lang}wcf.acp.box.isDisabled{/lang}">
												{icon name='triangle-exclamation'}
											</span>
										{/if}
									</label>
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
						<script data-relocate="true">
							require(['Language', 'WoltLabSuite/Core/Ui/ItemList/Filter'], function(Language, UiItemListFilter) {
								Language.addObject({
									'wcf.global.filter.button.visibility': '{jslang}wcf.global.filter.button.visibility{/jslang}',
									'wcf.global.filter.button.clear': '{jslang}wcf.global.filter.button.clear{/jslang}',
									'wcf.global.filter.error.noMatches': '{jslang}wcf.global.filter.error.noMatches{/jslang}',
									'wcf.global.filter.placeholder': '{jslang}wcf.global.filter.placeholder{/jslang}',
									'wcf.global.filter.visibility.activeOnly': '{jslang}wcf.global.filter.visibility.activeOnly{/jslang}',
									'wcf.global.filter.visibility.highlightActive': '{jslang}wcf.global.filter.visibility.highlightActive{/jslang}',
									'wcf.global.filter.visibility.showAll': '{jslang}wcf.global.filter.visibility.showAll{/jslang}'
								});

								new UiItemListFilter('boxVisibilitySettings');
							});
						</script>
					</dd>
				</dl>
			</div>
		</div>

		{if $action != 'edit' || $page->pageType != 'system'}
			<div id="acl" class="tabMenuContent">
				{include file='shared_aclSimple' __supportsInvertedPermissions=true}
			</div>
		{/if}

		{event name='tabMenuContents'}
	</div>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="isMultilingual" value="{$isMultilingual}">
		<input type="hidden" name="pageType" value="{$pageType}">
		{csrfToken}
	</div>
</form>

{include file='footer'}
