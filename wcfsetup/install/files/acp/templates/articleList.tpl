{include file='header' pageTitle='wcf.acp.article.list'}

<script data-relocate="true">
	require(['WoltLab/WCF/Ui/User/Search/Input'], function(UiUserSearchInput) {
		new UiUserSearchInput(elBySel('input[name="username"]'));
	});
</script>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\article\\ArticleAction', '.jsArticleRow');
		new WCF.Action.Toggle('wcf\\data\\article\\ArticleAction', '.jsArticleRow');
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.article.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="#" class="button jsButtonArticleAdd"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
			
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
						<option value="0">{lang}wcf.acp.article.category{/lang}</option>
						
						{foreach from=$categoryNodeList item=category}
							<option value="{@$category->categoryID}"{if $category->categoryID == $categoryID} selected{/if}>{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
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
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{@SECURITY_TOKEN_INPUT_TAG}
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
			
			{pages print=true assign=pagesLinks controller="ArticleList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnArticleID{if $sortField == 'articleID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=articleID&sortOrder={if $sortField == 'articleID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnArticleTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.title{/lang}</a></th>
					<th class="columnDigits columnComments{if $sortField == 'comments'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=comments&sortOrder={if $sortField == 'comments' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.comments{/lang}</a></th>
					<th class="columnDigits columnViews{if $sortField == 'views'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=views&sortOrder={if $sortField == 'views' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.article.views{/lang}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='ArticleList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.date{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=article}
					<tr class="jsArticleRow">
						<td class="columnIcon">
							<a href="{link controller='ArticleEdit' id=$article->articleID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon24 fa-pencil"></span></a>
							{if $article->canDelete()}
								<span class="icon icon24 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$article->articleID}" data-confirm-message-html="{lang __encode=true}wcf.acp.article.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon24 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							<a href="{$article->getLink()}" title="{lang}wcf.acp.article.button.viewArticle{/lang}" class="jsTooltip"><span class="icon icon24 fa-search"></span></a>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnArticleID">{@$article->articleID}</td>
						<td class="columnText columnArticleTitle">
							<div class="box48">
								<span>
									{if $article->getImage()}
										{@$article->getImage()->getElementTag(48)}
									{else}
										<img src="{@$__wcf->getPath()}images/placeholderTiny.png" style="width: 48px; height: 48px" alt="">
									{/if}
								</span>
								
								<div class="containerHeadline">
									<h3><a href="{link controller='ArticleEdit' id=$article->articleID}{/link}" title="{lang}wcf.acp.article.edit{/lang}" class="jsTooltip">{$article->title}</a></h3>
									<ul class="inlineList dotSeparated">
										{if $article->categoryID}
											<li>{$article->getCategory()->getTitle()}</li>
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
						<td class="columnDigits columnComments">{#$article->comments}</td>
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
				<li><a href="#" class="button jsButtonArticleAdd"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.article.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='articleAddDialog'}

{include file='footer'}
