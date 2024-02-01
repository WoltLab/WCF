{include file='header' pageTitle='wcf.media.media.pageTitle'}

<script data-relocate="true">
	{include file='mediaJavaScript'}
	
	require(['Language', 'WoltLabSuite/Core/Controller/Media/List'], function (Language, ControllerMediaList) {
		Language.addObject({
			'wcf.media.delete.confirmMessage': '{jslang __literal=true}wcf.media.delete.confirmMessage{/jslang}',
			'wcf.media.setCategory': '{jslang}wcf.media.setCategory{/jslang}'
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
		<h1 class="contentTitle">{lang}wcf.media.media.pageTitle{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><div id="uploadButton"></div></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

<form method="post" action="{link controller='MediaList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			{hascontent}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select id="categoryID" name="categoryID">
							<option value="0">{lang}wcf.global.categories{/lang}</option>
							<option value="-1"{if $categoryID == -1} selected="selected"{/if}>
								{lang}wcf.media.category.choose.noCategory{/lang}
							</option>
							
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
			{csrfToken}
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
	<table class="table jsClipboardContainer jsObjectActionContainer" data-object-action-class-name="wcf\data\media\MediaAction" data-type="com.woltlab.wcf.media">
		<thead>
			<tr>
				<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
				<th class="columnID columnMediaID{if $sortField == 'mediaID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=mediaID&sortOrder={if $sortField == 'mediaID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
				<th class="columnTitle columnFilename{if $sortField == 'filename'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filename&sortOrder={if $sortField == 'filename' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filename{/lang}</a></th>
				<th class="columnText columnMediaTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.title{/lang}</a></th>
				<th class="columnDate columnUploadTime{if $sortField == 'uploadTime'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=uploadTime&sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.uploadTime{/lang}</a></th>
				<th class="columnDigits columnFilesize{if $sortField == 'filesize'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filesize&sortOrder={if $sortField == 'filesize' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filesize{/lang}</a></th>
				<th class="columnDigits columnDownloads{if $sortField == 'downloads'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=downloads&sortOrder={if $sortField == 'downloads' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.downloads{/lang}</a></th>
				<th class="columnDate columnLastDownloadTime{if $sortField == 'lastDownloadTime'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=lastDownloadTime&sortOrder={if $sortField == 'lastDownloadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.lastDownloadTime{/lang}</a></th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody class="jsReloadPageWhenEmpty" id="mediaListTableBody" data-no-items-info="noItemsInfo">
			{foreach from=$objects item=media}
				<tr class="jsMediaRow jsClipboardObject jsObjectActionObject" data-object-id="{@$media->getObjectID()}">
					<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$media->mediaID}"></td>
					<td class="columnIcon">
						<button type="button" class="mediaEditButton jsMediaEditButton jsTooltip" title="{lang}wcf.global.button.edit{/lang}" data-object-id="{@$media->mediaID}">
							{icon name='pencil'}
						</span>
						{objectAction action="delete" objectTitle=$media->filename}
						
						{event name='rowButtons'}
					</td>
					<td class="columnID columnMediaID">{@$media->mediaID}</td>
					<td class="columnTitle columnFilename">
						<div class="box48">
							{@$media->getElementTag(48)}
							
							<div>
								<p><a href="{$media->getLink()}">{$media->filename|tableWordwrap}</a></p>
								<p><small>{if $media->userID}{if $__wcf->session->getPermission('admin.user.canEditUser')}<a href="{link controller='UserEdit' id=$media->userID}{/link}">{$media->username}</a>{else}{$media->username}{/if}{else}{lang}wcf.user.guest{/lang}{/if}</small></p>
							</div>
						</div>
					</td>
					<td class="columnText columnMediaTitle">{$media->title|tableWordwrap}</td>
					<td class="columnDate columnUploadTime">{@$media->uploadTime|time}</td>
					<td class="columnDigits columnFilesize">{@$media->filesize|filesize}</td>
					<td class="columnDigits columnDownloads">{#$media->downloads}</td>
					<td class="columnDate columnLastDownloadTime">{if $media->lastDownloadTime}{@$media->lastDownloadTime|time}{/if}</td>
					
					{event name='columns'}
				</tr>
			{foreachelse}
				<tr class="jsMediaRow jsClipboardObject jsObjectActionObject" data-object-id="0">
					<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="0"></td>
					<td class="columnIcon">
						<button type="button" class="mediaEditButton jsMediaEditButton jsTooltip" title="{lang}wcf.global.button.edit{/lang}" data-object-id="0">
							{icon name='pencil'}
						</button>
						{objectAction action="delete" confirmMessage=""}
						
						{event name='rowButtons'}
					</td>
					<td class="columnID columnMediaID"></td>
					<td class="columnTitle columnFilename">
						<div class="box48">
							{icon size=48 name='file'}
							
							<div>
								<p></p>
								<p><small>{if $__wcf->session->getPermission('admin.user.canEditUser')}<a href=""></a>{/if}</small></p>
							</div>
						</div>
					</td>
					<td class="columnText columnMediaTitle"></td>
					<td class="columnDate columnUploadTime"></td>
					<td class="columnDigits columnFilesize"></td>
					<td class="columnDigits columnDownloads"></td>
					<td class="columnDate columnLastDownloadTime"></td>
					
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
	<woltlab-core-notice type="info" id="noItemsInfo">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
