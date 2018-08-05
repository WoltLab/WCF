{include file='header' pageTitle='wcf.media.media'}

<script data-relocate="true">
	{include file='mediaJavaScript'}
	
	require(['Language', 'WoltLabSuite/Core/Controller/Media/List'], function (Language, ControllerMediaList) {
		Language.addObject({
			'wcf.media.delete.confirmMessage': '{lang __literal=true}wcf.media.delete.confirmMessage{/lang}',
			'wcf.media.setCategory': '{lang}wcf.media.setCategory{/lang}'
		});
		
		ControllerMediaList.init({
			{if $categoryID}
				categoryId: {@$categoryID},
			{/if}
			hasMarkedItems: {if $hasMarkedItems}true{else}false{/if}
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.media.media{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><div id="uploadButton"></div></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

<form method="post" action="{link controller='MediaList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			{hascontent}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select id="categoryID" name="categoryID">
							<option value="0">{lang}wcf.media.category.choose{/lang}</option>
							
							{content}
								{foreach from=$categoryList item=categoryItem}
									<option value="{$categoryItem->categoryID}"{if $categoryItem->categoryID == $categoryID} selected="selected"{/if}>{$categoryItem->getTitle()}</option>
									
									{if $categoryItem->hasChildren()}
										{foreach from=$categoryItem item=subCategoryItem}
											<option value="{$subCategoryItem->categoryID}"{if $subCategoryItem->categoryID == $categoryID} selected="selected"{/if}>&nbsp;&nbsp;&nbsp;&nbsp;{$subCategoryItem->getTitle()}</option>
											
											{if $subCategoryItem->hasChildren()}
												{foreach from=$subCategoryItem item=subSubCategoryItem}
													<option value="{$subSubCategoryItem->categoryID}"{if $subSubCategoryItem->categoryID == $categoryID} selected="selected"{/if}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$subSubCategoryItem->getTitle()}</option>
												{/foreach}
											{/if}
										{/foreach}
									{/if}
								{/foreach}
							{/content}
						</select>
					</dd>
				</dl>
			{/hascontent}
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="q" name="q" value="{$q}" placeholder="{lang}wcf.media.search.placeholder{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.user.username{/lang}" class="long">
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
			{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
			{if $q}{capture append=linkParameters}&q={@$q|rawurlencode}{/capture}{/if}
			{if $categoryID}{capture append=linkParameters}&categoryID={@$categoryID}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="MediaList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

<div class="section tabularBox"{if !$objects|count} style="display: none;{/if}">
	<table class="table jsClipboardContainer" data-type="com.woltlab.wcf.media">
		<thead>
			<tr>
				<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
				<th class="columnID columnMediaID{if $sortField == 'mediaID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=mediaID&sortOrder={if $sortField == 'mediaID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
				<th class="columnTitle columnFilename{if $sortField == 'filename'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filename&sortOrder={if $sortField == 'filename' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filename{/lang}</a></th>
				<th class="columnText columnMediaTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.title{/lang}</a></th>
				<th class="columnDate columnUploadTime{if $sortField == 'uploadTime'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=uploadTime&sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.uploadTime{/lang}</a></th>
				<th class="columnDigits columnFilesize{if $sortField == 'filesize'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filesize&sortOrder={if $sortField == 'filesize' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filesize{/lang}</a></th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody id="mediaListTableBody" data-no-items-info="noItemsInfo">
			{foreach from=$objects item=media}
				<tr class="jsMediaRow jsClipboardObject">
					<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$media->mediaID}"></td>
					<td class="columnIcon">
						<span class="icon icon24 fa-pencil mediaEditButton jsMediaEditButton jsTooltip pointer" title="{lang}wcf.global.button.edit{/lang}" data-object-id="{@$media->mediaID}"></span>
						<span class="icon icon24 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$media->mediaID}" data-confirm-message-html="{lang title=$media->filename __encode=true}wcf.media.delete.confirmMessage{/lang}"></span>
						
						{event name='rowButtons'}
					</td>
					<td class="columnID columnMediaID">{@$media->mediaID}</td>
					<td class="columnTitle columnFilename">
						<div class="box48">
							{@$media->getElementTag(48)}
							
							<div>
								<p>{$media->filename|tableWordwrap}</p>
								<p><small>{if $media->userID}{if $__wcf->session->getPermission('admin.user.canEditUser')}<a href="{link controller='UserEdit' id=$media->userID}{/link}">{$media->username}</a>{else}{$media->username}{/if}{else}{lang}wcf.user.guest{/lang}{/if}</small></p>
							</div>
						</div>
					</td>
					<td class="columnText columnMediaTitle">{$media->title|tableWordwrap}</td>
					<td class="columnDate columnUploadTime">{@$media->uploadTime|time}</td>
					<td class="columnDigits columnFilesize">{@$media->filesize|filesize}</td>
					
					{event name='columns'}
				</tr>
			{foreachelse}
				<tr class="jsMediaRow jsClipboardObject">
					<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="0"></td>
					<td class="columnIcon">
						<span class="icon icon24 fa-pencil mediaEditButton jsMediaEditButton jsTooltip pointer" title="{lang}wcf.global.button.edit{/lang}" data-object-id="0"></span>
						<span class="icon icon24 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="0"></span>
						
						{event name='rowButtons'}
					</td>
					<td class="columnID columnMediaID"></td>
					<td class="columnTitle columnFilename">
						<div class="box48">
							<span class="icon icon48 fa-file"></span>
							
							<div>
								<p></p>
								<p><small>{if $__wcf->session->getPermission('admin.user.canEditUser')}<a href=""></a>{/if}</small></p>
							</div>
						</div>
					</td>
					<td class="columnText columnMediaTitle"></td>
					<td class="columnDate columnUploadTime"></td>
					<td class="columnDigits columnFilesize"></td>
					
					{event name='columns'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

{if $objects|count}
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info" id="noItemsInfo">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
