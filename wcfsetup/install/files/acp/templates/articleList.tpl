{include file='header' pageTitle='wcf.acp.article.list'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Controller/Clipboard', 'WoltLabSuite/Core/Ui/User/Search/Input', 'WoltLabSuite/Core/Acp/Ui/Article/InlineEditor'],
		function(Language, ControllerClipboard, UiUserSearchInput, AcpUiArticleInlineEditor) {
		Language.addObject({
			'wcf.acp.article.publicationStatus.unpublished': '{jslang}wcf.acp.article.publicationStatus.unpublished{/jslang}',
			'wcf.acp.article.setCategory': '{jslang}wcf.acp.article.setCategory{/jslang}',
			'wcf.message.status.deleted': '{jslang}wcf.message.status.deleted{/jslang}'
		});
		
		new UiUserSearchInput(elBySel('input[name="username"]'));
		new AcpUiArticleInlineEditor(0);
		
		ControllerClipboard.setup({
			hasMarkedItems: {if $hasMarkedItems}true{else}false{/if},
			pageClassName: 'wcf\\acp\\page\\ArticleListPage'
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.article.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $availableLanguages|count > 1}
				<li><a href="#" class="button jsButtonArticleAdd">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
			{else}
				<li><a href="{link controller='ArticleAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
			{/if}
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<form method="post" action="{link controller='ArticleList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="categoryID" id="categoryID">
						<option value="0">{lang}wcf.global.category{/lang}</option>
						
						{foreach from=$categoryNodeList item=category}
							<option value="{$category->categoryID}"{if $category->categoryID == $categoryID} selected{/if}>{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
						{/foreach}
					</select>
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
					<input type="text" id="pageContent" name="content" value="{$content}" placeholder="{lang}wcf.acp.article.content{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.acp.article.author{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="publicationStatus" id="publicationStatus">
						<option value="-1">{lang}wcf.acp.article.publicationStatus{/lang}</option>
						
						<option value="0"{if $publicationStatus == 0} selected{/if}>{lang}wcf.acp.article.publicationStatus.unpublished{/lang}</option>
						<option value="1"{if $publicationStatus == 1} selected{/if}>{lang}wcf.acp.article.publicationStatus.published{/lang}</option>
						<option value="2"{if $publicationStatus == 2} selected{/if}>{lang}wcf.acp.article.publicationStatus.delayed{/lang}</option>
					</select>
				</dd>
			</dl>
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="isDeleted" id="isDeleted" value="1"{if $isDeleted === 1} checked{/if}> {lang}wcf.acp.article.isDeleted{/lang}</label>
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
			{if $categoryID}{capture append=linkParameters}&categoryID={@$categoryID}{/capture}{/if}
			{if $title}{capture append=linkParameters}&title={@$title|rawurlencode}{/capture}{/if}
			{if $content}{capture append=linkParameters}&content={@$content|rawurlencode}{/capture}{/if}
			{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
			{if $publicationStatus != -1}{capture append=linkParameters}&publicationStatus={@$publicationStatus}{/capture}{/if}
			{if $isDeleted != -1}{capture append=linkParameters}&isDeleted=1{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="ArticleList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table data-type="com.woltlab.wcf.article" class="table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
					<th class="columnID columnArticleID{if $sortField == 'articleID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=articleID&sortOrder={if $sortField == 'articleID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnArticleTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.title{/lang}</a></th>
					<th class="columnDigits columnViews{if $sortField == 'views'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=views&sortOrder={if $sortField == 'views' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.article.views{/lang}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.date{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=article}
					<tr class="jsArticleRow jsClipboardObject" data-object-id="{@$article->articleID}" data-title="{$article->title}">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$article->articleID}"></td>
						<td class="columnIcon">
							{if $article->canEdit()}
								<a href="{link controller='ArticleEdit' id=$article->articleID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
							{else}
								<span class="disabled" title="{lang}wcf.global.button.edit{/lang}">
									{icon name='pencil'}
								</span>
							{/if}
							{if $article->canDelete()}
								<a href="#" class="jsButtonRestore jsTooltip" title="{lang}wcf.global.button.restore{/lang}"{if !$article->isDeleted} style="display: none"{/if}>{icon name='arrows-rotate'}</a>
								<a href="#" class="jsButtonDelete jsTooltip" title="{lang}wcf.global.button.delete{/lang}"{if !$article->isDeleted} style="display: none"{/if}>{icon name='xmark'}</a>
								<a href="#" class="jsButtonTrash jsTooltip" title="{lang}wcf.global.button.trash{/lang}"{if $article->isDeleted} style="display: none"{/if}>{icon name='xmark'}</a>
							{else}
								<span class="disabled" title="{lang}wcf.global.button.delete{/lang}">
									{icon name='xmark'}
								</span>
							{/if}
							
							<a href="{$article->getLink()}" title="{lang}wcf.acp.article.button.viewArticle{/lang}" class="jsTooltip">{icon name='magnifying-glass'}</a>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnArticleID">{@$article->articleID}</td>
						<td class="columnText columnArticleTitle">
							<div class="box48">
								<span>
									{if $article->getTeaserImage()}
										{@$article->getTeaserImage()->getElementTag(48)}
									{else}
										<img src="{@$__wcf->getPath()}images/placeholderTiny.png" style="width: 48px; height: 48px" alt="">
									{/if}
								</span>
								
								<div class="containerHeadline">
									{if $article->hasLabels()}
										<ul class="labelList" style="float: right; padding-left: 7px;">
											{foreach from=$article->getLabels() item=label}
												<li>{@$label->render()}</li>
											{/foreach}
										</ul>
									{/if}
									
									<h3>
										{if $article->isDeleted}<span class="badge label red jsIconDeleted">{lang}wcf.message.status.deleted{/lang}</span>{/if}
										{if $article->publicationStatus == 0}<span class="badge jsUnpublishedArticle">{lang}wcf.acp.article.publicationStatus.unpublished{/lang}</span>{/if}
										{if $article->publicationStatus == 2}<span class="badge" title="{$article->publicationDate|plainTime}">{lang}wcf.acp.article.publicationStatus.delayed{/lang}</span>{/if}
										<a href="{link controller='ArticleEdit' id=$article->articleID}{/link}" title="{lang}wcf.acp.article.edit{/lang}" class="jsTooltip">{$article->title}</a>
									</h3>
									<ul class="inlineList dotSeparated">
										{if $article->categoryID}
											<li class="jsArticleCategory">{$article->getCategory()->getTitle()}</li>
										{/if}
										
										{if $article->username}
											<li>
												{if $article->userID}
													<a href="{link controller='UserEdit' id=$article->userID}{/link}">{$article->username}</a>
												{else}
													{$article->username}
												{/if}
											</li>
										{/if}
									</ul>
								</div>
							</div>
						</td>
						<td class="columnDigits columnViews">{#$article->views}</td>
						<td class="columnDate columnTime">{@$article->time|time}</td>
						
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
				{if $availableLanguages|count > 1}
					<li><a href="#" class="button jsButtonArticleAdd">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
				{else}
					<li><a href="{link controller='ArticleAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
				{/if}
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='shared_articleAddDialog'}

{include file='footer'}
