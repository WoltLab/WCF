{include file='header' pageTitle='wcf.acp.article.'|concat:$action}

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

<script data-relocate="true">
	require(['WoltLab/WCF/Ui/User/Search/Input'], function(UiUserSearchInput) {
		new UiUserSearchInput(elBySel('input[name="username"]'));
	});
</script>

{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	<script data-relocate="true">
		{include file='mediaJavaScript'}
		
		require(['WoltLab/WCF/Media/Manager/Select'], function(MediaManagerSelect) {
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
		<h1 class="contentTitle">{if $action == 'add'}{lang}wcf.acp.article.add{/lang}{else}{lang}wcf.acp.article.edit{/lang}{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li><a href="{$article->getLink()}" class="button"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.acp.article.button.viewArticle{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='ArticleList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.article.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='ArticleAdd'}{/link}{else}{link controller='ArticleEdit' id=$articleID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'categoryID'} class="formError"{/if}>
			<dt><label for="categoryID">{lang}wcf.acp.article.category{/lang}</label></dt>
			<dd>
				<select name="categoryID" id="categoryID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$categoryNodeList item=category}
						<option value="{@$category->categoryID}"{if $category->categoryID == $categoryID} selected{/if}>{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
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
		
		<dl>
			<dt><label for="categoryID">{lang}wcf.acp.article.publicationStatus{/lang}</label></dt>
			<dd>
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
						<div id="imageDisplay">
							{if $images[0]|isset}
								{@$images[0]->getThumbnailTag('small')}
							{/if}
						</div>
						<p class="button jsMediaSelectButton" data-store="imageID0" data-display="imageDisplay">{lang}wcf.acp.media.chooseImage{/lang}</p>
						<input type="hidden" name="imageID[0]" id="imageID0"{if $imageID[0]|isset} value="{@$imageID[0]}"{/if}>
						{if $errorField == 'image'}
							<small class="innerError">{lang}wcf.acp.article.image.error.{@$errorType}{/lang}</small>
						{/if}
					</dd>
				</dl>
			{elseif $action == 'edit' && $images[0]|isset}
				<dl>
					<dt>{lang}wcf.acp.article.image{/lang}</dt>
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
								{lang}wcf.acp.article.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{if MODULE_TAGGING}
				<dl class="jsOnly">
					<dt><label for="tagSearchInput">{lang}wcf.tagging.tags{/lang}</label></dt>
					<dd>
						<input id="tagSearchInput" type="text" value="" class="long">
						<small>{lang}wcf.tagging.tags.description{/lang}</small>
					</dd>
				</dl>
				
				<script data-relocate="true">
					require(['WoltLab/WCF/Ui/ItemList'], function(UiItemList) {
						UiItemList.init(
							'tagSearchInput',
							[{if !$tags[0]|empty}{implode from=$tags[0] item=tag}'{$tag|encodeJS}'{/implode}{/if}],
							{
								ajax: {
									className: 'wcf\\data\\tag\\TagAction'
								},
								maxLength: {@TAGGING_MAX_TAG_LENGTH},
								submitFieldName: 'tags[0][]'
							}
						);
					});
				</script>
			{/if}
			
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
					<textarea name="content[0]" id="content0" rows="10">{if !$content[0]|empty}{$content[0]}{/if}</textarea>
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
		</div>
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
										{if $images[$availableLanguage->languageID]|isset}
											{@$images[$availableLanguage->languageID]->getThumbnailTag('small')}
										{/if}
									</div>
									<p class="button jsMediaSelectButton" data-store="imageID{@$availableLanguage->languageID}" data-display="imageDisplay{@$availableLanguage->languageID}">{lang}wcf.acp.media.chooseImage{/lang}</p>
									<input type="hidden" name="imageID[{@$availableLanguage->languageID}]" id="imageID{@$availableLanguage->languageID}"{if $imageID[$availableLanguage->languageID]|isset} value="{@$imageID[$availableLanguage->languageID]}"{/if}>
									{if $errorField == 'image'|concat:$availableLanguage->languageID}
										<small class="innerError">{lang}wcf.acp.article.image.error.{@$errorType}{/lang}</small>
									{/if}
								</dd>
							</dl>
						{elseif $action == 'edit' && $images[$availableLanguage->languageID]|isset}
							<dl>
								<dt>{lang}wcf.acp.article.image{/lang}</dt>
								<dd>
									<div id="imageDisplay">{@$images[$availableLanguage->languageID]->getThumbnailTag('small')}</div>
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
							<dl class="jsOnly">
								<dt><label for="tagSearchInput{@$availableLanguage->languageID}">{lang}wcf.tagging.tags{/lang}</label></dt>
								<dd>
									<input id="tagSearchInput{@$availableLanguage->languageID}" type="text" value="" class="long">
									<small>{lang}wcf.tagging.tags.description{/lang}</small>
								</dd>
							</dl>
							
							<script data-relocate="true">
								require(['WoltLab/WCF/Ui/ItemList'], function(UiItemList) {
									UiItemList.init(
										'tagSearchInput{@$availableLanguage->languageID}',
										[{if !$tags[$availableLanguage->languageID]|empty}{implode from=$tags[$availableLanguage->languageID] item=tag}'{$tag|encodeJS}'{/implode}{/if}],
										{
											ajax: {
												className: 'wcf\\data\\tag\\TagAction'
											},
											maxLength: {@TAGGING_MAX_TAG_LENGTH},
											submitFieldName: 'tags[{@$availableLanguage->languageID}][]'
										}
									);
								});
							</script>
						{/if}
						
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
								<textarea name="content[{@$availableLanguage->languageID}]" id="content{@$availableLanguage->languageID}" rows="10">{if !$content[$availableLanguage->languageID]|empty}{$content[$availableLanguage->languageID]}{/if}</textarea>
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
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="isMultilingual" value="{@$isMultilingual}">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
