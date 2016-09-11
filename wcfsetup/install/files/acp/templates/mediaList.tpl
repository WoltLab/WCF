{include file='header' pageTitle='wcf.media.media'}

<script data-relocate="true">
	{include file='mediaJavaScript'}
	
	require(['WoltLabSuite/Core/Controller/Media/List'], function (ControllerMediaList) {
		ControllerMediaList.init({
			hasMarkedItems: {if $hasMarkedItems}true{else}false{/if}
		});
	});
</script>

{* hidden container element containg the element for the uploaded media file *}
<p id="mediaFile" style="display: none;"></p>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.media.media{/lang}</h1>
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
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="filename" name="filename" value="{$filename}" placeholder="{lang}wcf.media.filename{/lang}" class="long">
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
			{if $filename}{capture append=linkParameters}&filename={@$filename|rawurlencode}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="MediaList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table jsClipboardContainer" data-type="com.woltlab.wcf.media">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
					<th class="columnID columnMediaID{if $sortField == 'mediaID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=mediaID&sortOrder={if $sortField == 'mediaID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnFilename{if $sortField == 'filename'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filename&sortOrder={if $sortField == 'filename' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filename{/lang}</a></th>
					<th class="columnDate columnUploadTime{if $sortField == 'uploadTime'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=uploadTime&sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.uploadTime{/lang}</a></th>
					<th class="columnDigits columnFilesize{if $sortField == 'filesize'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filesize&sortOrder={if $sortField == 'filesize' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filesize{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=media}
					<tr class="jsMediaRow jsClipboardObject">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$media->mediaID}"></td>
						<td class="columnIcon">
							<span class="icon icon24 fa-pencil jsMediaEditButton jsTooltip pointer" title="{lang}wcf.global.button.edit{/lang}" data-object-id="{@$media->mediaID}"></span>
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
						<td class="columnDate columnUploadTime">{@$media->uploadTime|time}</td>
						<td class="columnDigits columnFilesize">{@$media->filesize|filesize}</td>
						
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
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
