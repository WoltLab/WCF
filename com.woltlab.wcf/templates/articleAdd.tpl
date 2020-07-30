{capture assign='__contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{if $action == 'add'}{lang}wcf.acp.article.add{/lang}{else}{lang}wcf.acp.article.edit{/lang}{/if}</h1>
		</div>
		
		<nav class="contentHeaderNavigation">
			<ul>
				{if $action == 'edit'}
					{if $article->canDelete()}
						<li><a href="#" class="button jsButtonRestore" data-confirm-message-html="{lang __encode=true isArticleEdit=true}wcf.acp.article.restore.confirmMessage{/lang}"{if !$article->isDeleted} style="display: none"{/if}><span class="icon icon16 fa-refresh"></span> <span>{lang}wcf.global.button.restore{/lang}</span></a></li>
						<li><a href="#" class="button jsButtonDelete" data-confirm-message-html="{lang __encode=true isArticleEdit=true}wcf.acp.article.delete.confirmMessage{/lang}"{if !$article->isDeleted} style="display: none"{/if}><span class="icon icon16 fa-times"></span> <span>{lang}wcf.global.button.delete{/lang}</span></a></li>
						<li><a href="#" class="button jsButtonTrash" data-confirm-message-html="{lang __encode=true isArticleEdit=true}wcf.acp.article.trash.confirmMessage{/lang}"{if $article->isDeleted} style="display: none"{/if}><span class="icon icon16 fa-times"></span> <span>{lang}wcf.global.button.trash{/lang}</span></a></li>
					{/if}
					{if $languages|count > 1 || $article->isMultilingual}
						<li><a href="#" class="button jsButtonToggleI18n"><span class="icon icon16 fa-flag"></span> <span>{lang}wcf.acp.article.button.toggleI18n{/lang}</span></a></li>
					{/if}
					<li><a href="{$article->getLink()}" class="button"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.acp.article.button.viewArticle{/lang}</span></a></li>
				{/if}
				<li><a href="{link controller='ArticleList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.article.list{/lang}</span></a></li>
				
				{event name='contentHeaderNavigation'}
			</ul>
		</nav>
	</header>
{/capture}

{if $articleIsFrontend|empty}
	{include file='header' pageTitle='wcf.acp.article.'|concat:$action}
{else}
	{include file='header' contentHeader=$__contentHeader}
{/if}

{if $__wcf->session->getPermission('admin.content.article.canManageArticle')}
	<script data-relocate="true">
		$(function() {
			$('input[type="radio"][name="publicationStatus"]').change(function(event) {
				var $selected = $('input[type="radio"][name="publicationStatus"]:checked');
				if ($selected.length > 0) {
					if ($selected.val() == 2) {
						$('#publicationDateDl').show();
					}
					else {
						$('#publicationDateDl').hide();
					}
				}
			}).trigger('change');
		});
	</script>
{/if}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Ui/User/Search/Input', 'WoltLabSuite/Core/Acp/Ui/Article/InlineEditor'], function(Language, UiUserSearchInput, AcpUiArticleInlineEditor) {
		Language.addObject({
			'wcf.acp.article.i18n.source': '{lang}wcf.acp.article.i18n.source{/lang}',
			'wcf.acp.article.i18n.toI18n.confirmMessage': '{lang}wcf.acp.article.i18n.toI18n.confirmMessage{/lang}',
			'wcf.acp.article.i18n.fromI18n.confirmMessage': '{lang}wcf.acp.article.i18n.fromI18n.confirmMessage{/lang}',
			'wcf.message.status.deleted': '{lang}wcf.message.status.deleted{/lang}',
		});
		
		new UiUserSearchInput(elBySel('input[name="username"]'));
		{if $action == 'edit'}
			new AcpUiArticleInlineEditor({@$article->articleID}, {
				i18n: {
					defaultLanguageId: {@$defaultLanguageID},
					isI18n: {if $article->isMultilingual}true{else}false{/if},
					languages: { {implode from=$languages item=language glue=', '}{@$language->languageID}: '{$language|encodeJS}'{/implode} }
				},
				redirectUrl: '{link controller='ArticleList'}{/link}'
			});
		{/if}
	});
</script>

{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	<script data-relocate="true">
		{include file='mediaJavaScript'}
		
		require(['WoltLabSuite/Core/Media/Manager/Select'], function(MediaManagerSelect) {
			new MediaManagerSelect({
				dialogTitle: '{lang}wcf.media.chooseImage{/lang}',
				imagesOnly: 1
			});
		});
	</script>
{/if}

{if $articleIsFrontend|empty}
	{@$__contentHeader}
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success" role="status">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

{if $action == 'edit'}
	<p class="info jsArticleNoticeTrash"{if !$article->isDeleted} style="display: none;"{/if}>{lang}wcf.acp.article.trash.notice{/lang}</p>
	
	{if $lastVersion && $__wcf->session->getPermission('admin.general.canUseAcp')}<p class="info" role="status">{lang}wcf.acp.article.lastVersion{/lang}</p>{/if}
{/if}

<form class="articleAddForm" method="post" action="{if $action == 'add'}{link controller='ArticleAdd'}{/link}{else}{link controller='ArticleEdit' id=$articleID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'categoryID'} class="formError"{/if}>
			<dt><label for="categoryID">{lang}wcf.acp.article.category{/lang}</label></dt>
			<dd>
				<select name="categoryID" id="categoryID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$categoryNodeList item=category}
						<option value="{@$category->categoryID}"{if !$category->categoryID|in_array:$accessibleCategoryIDs} disabled{elseif $category->categoryID == $categoryID} selected{/if}>{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
					{/foreach}
				</select>
				{if $errorField == 'categoryID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.article.category.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='categoryFields'}
		
		{if $labelGroups|count}
			{foreach from=$labelGroups item=labelGroup}
				{if $labelGroup|count}
					<dl{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset} class="formError"{/if}>
						<dt><label>{$labelGroup->getTitle()}</label></dt>
						<dd>
							<ul class="labelList jsOnly" data-object-id="{@$labelGroup->groupID}">
								<li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}" data-force-selection="{if $labelGroup->forceSelection}true{else}false{/if}">
									<div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
									<div class="dropdownMenu">
										<ul class="scrollableDropdownMenu">
											{foreach from=$labelGroup item=label}
												<li data-label-id="{@$label->labelID}"><span>{@$label->render()}</span></li>
											{/foreach}
										</ul>
									</div>
								</li>
							</ul>
							<noscript>
								<select name="labelIDs[{@$labelGroup->groupID}]">
									{foreach from=$labelGroup item=label}
										<option value="{@$label->labelID}">{$label->getTitle()}</option>
									{/foreach}
								</select>
							</noscript>
							{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset}
								<small class="innerError">
									{if $errorType[$labelGroup->groupID] == 'missing'}
										{lang}wcf.label.error.missing{/lang}
									{else}
										{lang}wcf.label.error.invalid{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
				{/if}
			{/foreach}
		{/if}
		
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.acp.article.author{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" class="medium" maxlength="255">
				{if $errorField == 'username'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.username.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'time'} class="formError"{/if}>
			<dt><label for="time">{lang}wcf.global.date{/lang}</label></dt>
			<dd>
				<input type="datetime" id="time" name="time" value="{$time}" class="medium">
				{if $errorField == 'time'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.article.time.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{if $__wcf->session->getPermission('admin.content.article.canManageArticle') || $__wcf->session->getPermission('admin.content.article.canManageOwnArticles')}
			<dl>
				<dt><label for="categoryID">{lang}wcf.acp.article.publicationStatus{/lang}</label></dt>
				<dd class="floated">
					<label><input type="radio" name="publicationStatus" value="0"{if $publicationStatus == 0} checked{/if}> {lang}wcf.acp.article.publicationStatus.unpublished{/lang}</label>
					<label><input type="radio" name="publicationStatus" value="1"{if $publicationStatus == 1} checked{/if}> {lang}wcf.acp.article.publicationStatus.published{/lang}</label>
					<label><input type="radio" name="publicationStatus" value="2"{if $publicationStatus == 2} checked{/if}> {lang}wcf.acp.article.publicationStatus.delayed{/lang}</label>
				</dd>
			</dl>
			
			<dl id="publicationDateDl"{if $errorField == 'publicationDate'} class="formError"{/if}{if $publicationStatus != 2} style="display: none"{/if}>
				<dt><label for="publicationDate">{lang}wcf.acp.article.publicationDate{/lang}</label></dt>
				<dd>
					<input type="datetime" id="publicationDate" name="publicationDate" value="{$publicationDate}" class="medium">
					{if $errorField == 'publicationDate'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.article.publicationDate.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		<dl>
			<dt></dt>
			<dd>
				<label><input name="enableComments" type="checkbox" value="1"{if $enableComments} checked{/if}> {lang}wcf.acp.article.enableComments{/lang}</label>
			</dd>
		</dl>
	</div>
	
	{if !$isMultilingual}
		<div class="section">
			{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
				<dl{if $errorField == 'image'} class="formError"{/if}>
					<dt><label for="image">{lang}wcf.acp.article.image{/lang}</label></dt>
					<dd>
						<div id="imageDisplay" class="selectedImagePreview">
							{if $images[0]|isset && $images[0]->hasThumbnail('small')}
								{@$images[0]->getThumbnailTag('small')}
							{/if}
						</div>
						<p class="button jsMediaSelectButton" data-store="imageID0" data-display="imageDisplay">{lang}wcf.media.chooseImage{/lang}</p>
						<input type="hidden" name="imageID[0]" id="imageID0"{if $imageID[0]|isset} value="{@$imageID[0]}"{/if}>
						{if $errorField == 'image'}
							<small class="innerError">{lang}wcf.acp.article.image.error.{@$errorType}{/lang}</small>
						{/if}
					</dd>
				</dl>
			{elseif $action == 'edit' && $images[0]|isset && $images[0]->hasThumbnail('small')}
				<dl>
					<dt>{lang}wcf.acp.article.image{/lang}</dt>
					<dd>
						<div id="imageDisplay">{@$images[0]->getThumbnailTag('small')}</div>
					</dd>
				</dl>
			{/if}
			
			{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
				<dl{if $errorField == 'teaserImage'} class="formError"{/if}>
					<dt><label for="teaserImage">{lang}wcf.acp.article.teaserImage{/lang}</label></dt>
					<dd>
						<div id="teaserImageDisplay" class="selectedImagePreview">
							{if $teaserImages[0]|isset && $teaserImages[0]->hasThumbnail('small')}
								{@$teaserImages[0]->getThumbnailTag('small')}
							{/if}
						</div>
						<p class="button jsMediaSelectButton" data-store="teaserImageID0" data-display="teaserImageDisplay">{lang}wcf.media.chooseImage{/lang}</p>
						<input type="hidden" name="teaserImageID[0]" id="teaserImageID0"{if $teaserImageID[0]|isset} value="{@$teaserImageID[0]}"{/if}>
						{if $errorField == 'teaserImage'}
							<small class="innerError">{lang}wcf.acp.article.image.error.{@$errorType}{/lang}</small>
						{/if}
					</dd>
				</dl>
			{elseif $action == 'edit' && $teaserImages[0]|isset && $teaserImages[0]->hasThumbnail('small')}
				<dl>
					<dt>{lang}wcf.acp.article.teaserImage{/lang}</dt>
					<dd>
						<div id="teaserImageDisplay">{@$teaserImages[0]->getThumbnailTag('small')}</div>
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
								{lang}wcf.acp.article.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{if MODULE_TAGGING}
				{include file='tagInput' tagInputSuffix='0' tagSubmitFieldName='tags[0][]' tags=$tags[0] sandbox=true}
			{/if}
			
			{event name='informationFields'}
			
			<dl{if $errorField == 'teaser'} class="formError"{/if}>
				<dt><label for="teaser0">{lang}wcf.acp.article.teaser{/lang}</label></dt>
				<dd>
					<textarea name="teaser[0]" id="teaser0" rows="5">{if !$teaser[0]|empty}{$teaser[0]}{/if}</textarea>
					{if $errorField == 'teaser'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.article.teaser.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'content'} class="formError"{/if}>
				<dt><label for="content0">{lang}wcf.acp.article.content{/lang}</label></dt>
				<dd>
					<textarea name="content[0]" id="content0" class="wysiwygTextarea" data-autosave="com.woltlab.wcf.article{$action|ucfirst}-{if $action == 'edit'}{@$articleID}{else}0{/if}-0">{if !$content[0]|empty}{$content[0]}{/if}</textarea>
					
					{include file='__wysiwygCmsToolbar'}
					{include file='wysiwyg' wysiwygSelector='content0'}
					
					{if $errorField == 'content'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.article.content.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='messageFields'}
		</div>
		
		{include file='messageFormTabs' wysiwygContainerID='content0'}
	{else}
		<div class="section tabMenuContainer">
			<nav class="tabMenu">
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
						{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
							<dl{if $errorField == 'image'|concat:$availableLanguage->languageID} class="formError"{/if}>
								<dt><label for="image{@$availableLanguage->languageID}">{lang}wcf.acp.article.image{/lang}</label></dt>
								<dd>
									<div id="imageDisplay{@$availableLanguage->languageID}">
										{if $images[$availableLanguage->languageID]|isset && $images[$availableLanguage->languageID]->hasThumbnail('small')}
											{@$images[$availableLanguage->languageID]->getThumbnailTag('small')}
										{/if}
									</div>
									<p class="button jsMediaSelectButton" data-store="imageID{@$availableLanguage->languageID}" data-display="imageDisplay{@$availableLanguage->languageID}">{lang}wcf.media.chooseImage{/lang}</p>
									<input type="hidden" name="imageID[{@$availableLanguage->languageID}]" id="imageID{@$availableLanguage->languageID}"{if $imageID[$availableLanguage->languageID]|isset} value="{@$imageID[$availableLanguage->languageID]}"{/if}>
									{if $errorField == 'image'|concat:$availableLanguage->languageID}
										<small class="innerError">{lang}wcf.acp.article.image.error.{@$errorType}{/lang}</small>
									{/if}
								</dd>
							</dl>
						{elseif $action == 'edit' && $images[$availableLanguage->languageID]|isset && $images[$availableLanguage->languageID]->hasThumbnail('small')}
							<dl>
								<dt>{lang}wcf.acp.article.image{/lang}</dt>
								<dd>
									<div id="imageDisplay">{@$images[$availableLanguage->languageID]->getThumbnailTag('small')}</div>
								</dd>
							</dl>
						{/if}
						
						{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
							<dl{if $errorField == 'image'|concat:$availableLanguage->languageID} class="formError"{/if}>
								<dt><label for="teaserImage{@$availableLanguage->languageID}">{lang}wcf.acp.article.teaserImage{/lang}</label></dt>
								<dd>
									<div id="teaserImageDisplay{@$availableLanguage->languageID}">
										{if $teaserImages[$availableLanguage->languageID]|isset && $teaserImages[$availableLanguage->languageID]->hasThumbnail('small')}
											{@$teaserImages[$availableLanguage->languageID]->getThumbnailTag('small')}
										{/if}
									</div>
									<p class="button jsMediaSelectButton" data-store="teaserImageID{@$availableLanguage->languageID}" data-display="teaserImageDisplay{@$availableLanguage->languageID}">{lang}wcf.media.chooseImage{/lang}</p>
									<input type="hidden" name="teaserImageID[{@$availableLanguage->languageID}]" id="teaserImageID{@$availableLanguage->languageID}"{if $teaserImageID[$availableLanguage->languageID]|isset} value="{@$teaserImageID[$availableLanguage->languageID]}"{/if}>
									{if $errorField == 'teaserImage'|concat:$availableLanguage->languageID}
										<small class="innerError">{lang}wcf.acp.article.image.error.{@$errorType}{/lang}</small>
									{/if}
								</dd>
							</dl>
						{elseif $action == 'edit' && $teaserImages[$availableLanguage->languageID]|isset && $teaserImages[$availableLanguage->languageID]->hasThumbnail('small')}
							<dl>
								<dt>{lang}wcf.acp.article.teaserImage{/lang}</dt>
								<dd>
									<div id="imageDisplay">{@$teaserImages[$availableLanguage->languageID]->getThumbnailTag('small')}</div>
								</dd>
							</dl>
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
											{lang}wcf.acp.article.title.error.{@$errorType}{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
						
						{if MODULE_TAGGING}
							{assign var='tagSubmitFieldName' value='tags['|concat:$availableLanguage->languageID:'][]'}
							{include file='tagInput' tagInputSuffix=$availableLanguage->languageID tagSubmitFieldName=$tagSubmitFieldName tags=$tags[$availableLanguage->languageID] sandbox=true}
						{/if}
						
						{event name='informationFieldsMultilingual'}
						
						<dl{if $errorField == 'teaser'|concat:$availableLanguage->languageID} class="formError"{/if}>
							<dt><label for="teaser{@$availableLanguage->languageID}">{lang}wcf.acp.article.teaser{/lang}</label></dt>
							<dd>
								<textarea name="teaser[{@$availableLanguage->languageID}]" id="teaser{@$availableLanguage->languageID}" rows="5">{if !$teaser[$availableLanguage->languageID]|empty}{$teaser[$availableLanguage->languageID]}{/if}</textarea>
								{if $errorField == 'teaser'|concat:$availableLanguage->languageID}
									<small class="innerError">
										{if $errorType == 'empty'}
											{lang}wcf.global.form.error.empty{/lang}
										{else}
											{lang}wcf.acp.article.teaser.error.{@$errorType}{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
						
						<dl{if $errorField == 'content'|concat:$availableLanguage->languageID} class="formError"{/if}>
							<dt><label for="content{@$availableLanguage->languageID}">{lang}wcf.acp.article.content{/lang}</label></dt>
							<dd>
								<textarea name="content[{@$availableLanguage->languageID}]" id="content{@$availableLanguage->languageID}" class="wysiwygTextarea" data-autosave="com.woltlab.wcf.article{$action|ucfirst}-{if $action == 'edit'}{@$articleID}{else}0{/if}-{@$availableLanguage->languageID}">{if !$content[$availableLanguage->languageID]|empty}{$content[$availableLanguage->languageID]}{/if}</textarea>
								
								{include file='__wysiwygCmsToolbar'}
								{include file='wysiwyg' wysiwygSelector='content'|concat:$availableLanguage->languageID}
								
								{if $errorField == 'content'|concat:$availableLanguage->languageID}
									<small class="innerError">
										{if $errorType == 'empty'}
											{lang}wcf.global.form.error.empty{/lang}
										{else}
											{lang}wcf.acp.article.content.error.{@$errorType}{/lang}
										{/if}
									</small>
								{/if}
							</dd>
						</dl>
						
						{event name='messageFieldsMultilingual'}
					</div>
					
					{include file='messageFormTabs' wysiwygContainerID='content'|concat:$availableLanguage->languageID}
				</div>
			{/foreach}
		</div>
	{/if}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="isMultilingual" value="{@$isMultilingual}">
		<input type="hidden" name="timeNowReference" value="{@TIME_NOW}">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{js application='wcf' file='WCF.Label' bundle='WCF.Combined'}
<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.label.none': '{lang}wcf.label.none{/lang}',
		});
		
		{if !$labelGroups|empty}
			new WCF.Label.ArticleLabelChooser({ {implode from=$labelGroupsToCategories key=__labelCategoryID item=labelGroupIDs}{@$__labelCategoryID}: [ {implode from=$labelGroupIDs item=labelGroupID}{@$labelGroupID}{/implode} ] {/implode} }, { {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '.articleAddForm');
		{/if}
	});
</script>

{include file='footer'}
