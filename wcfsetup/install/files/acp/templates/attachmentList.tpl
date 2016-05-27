{include file='header' pageTitle='wcf.acp.attachment.list'}

<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.ImageViewer.js?v={@LAST_UPDATE_TIME}"></script>
{include file='imageViewer'}
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.jsAttachmentRow');
		new WCF.Search.User('#username', null, false, [ ], true);
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.attachment.list{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.attachment.stats{/lang}</p>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

<form method="post" action="{link controller='AttachmentList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.user.username{/lang}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="filename" name="filename" value="{$filename}" placeholder="{lang}wcf.attachment.filename{/lang}" class="long" />
				</dd>
			</dl>
			
			{if $availableFileTypes|count > 1}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select name="fileType" id="fileType">
							<option value="">{lang}wcf.attachment.fileType{/lang}</option>
							{htmlOptions options=$availableFileTypes selected=$fileType}
						</select>
					</dd>
				</dl>
			{/if}
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
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
			{if $fileType}{capture append=linkParameters}&fileType={@$fileType|rawurlencode}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="AttachmentList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnAttachmentID{if $sortField == 'attachmentID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='AttachmentList'}pageNo={@$pageNo}&sortField=attachmentID&sortOrder={if $sortField == 'attachmentID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnFilename{if $sortField == 'filename'} active {@$sortOrder}{/if}"><a href="{link controller='AttachmentList'}pageNo={@$pageNo}&sortField=filename&sortOrder={if $sortField == 'filename' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.attachment.filename{/lang}</a></th>
					<th class="columnDate columnUploadTime{if $sortField == 'uploadTime'} active {@$sortOrder}{/if}"><a href="{link controller='AttachmentList'}pageNo={@$pageNo}&sortField=uploadTime&sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.attachment.uploadTime{/lang}</a></th>
					<th class="columnDigits columnFilesize{if $sortField == 'filesize'} active {@$sortOrder}{/if}"><a href="{link controller='AttachmentList'}pageNo={@$pageNo}&sortField=filesize&sortOrder={if $sortField == 'filesize' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.attachment.filesize{/lang}</a></th>
					<th class="columnDigits columnDownloads{if $sortField == 'downloads'} active {@$sortOrder}{/if}"><a href="{link controller='AttachmentList'}pageNo={@$pageNo}&sortField=downloads&sortOrder={if $sortField == 'downloads' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.attachment.downloads{/lang}</a></th>
					<th class="columnDate columnLastDownloadTime{if $sortField == 'lastDownloadTime'} active {@$sortOrder}{/if}"><a href="{link controller='AttachmentList'}pageNo={@$pageNo}&sortField=lastDownloadTime&sortOrder={if $sortField == 'lastDownloadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.attachment.lastDownloadTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=attachment}
					<tr class="jsAttachmentRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$attachment->attachmentID}" data-confirm-message="{lang}wcf.attachment.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnAttachmentID">{@$attachment->attachmentID}</td>
						<td class="columnTitle columnFilename">
							<div class="box48">
								<a href="{link controller='Attachment' id=$attachment->attachmentID}{/link}"{if $attachment->isImage} class="jsImageViewer" title="{$attachment->filename}"{/if}>
									{if $attachment->tinyThumbnailType}
										<img src="{link controller='Attachment' id=$attachment->attachmentID}tiny=1{/link}" class="attachmentTinyThumbnail" alt="" />
									{else}
										<span class="icon icon48 fa-paperclip"></span>
									{/if}
								</a>
								
								<div>
									<p><a href="{link controller='Attachment' id=$attachment->attachmentID}{/link}">{$attachment->filename|tableWordwrap}</a></p>
									<p><small>{if $attachment->userID}{if $__wcf->session->getPermission('admin.user.canEditUser')}<a href="{link controller='UserEdit' id=$attachment->userID}{/link}">{$attachment->username}</a>{else}{$attachment->username}{/if}{else}{lang}wcf.user.guest{/lang}{/if}</small></p>
									{if $attachment->getContainerObject()}<p><small><a href="{$attachment->getContainerObject()->getLink()}">{$attachment->getContainerObject()->getTitle()|tableWordwrap}</a></small></p>{/if}
								</div>
							</div>
						</td>
						<td class="columnDate columnUploadTime">{@$attachment->uploadTime|time}</td>
						<td class="columnDigits columnFilesize">{@$attachment->filesize|filesize}</td>
						<td class="columnDigits columnDownloads">{#$attachment->downloads}</td>
						<td class="columnDate columnLastDownloadTime">{if $attachment->lastDownloadTime}{@$attachment->lastDownloadTime|time}{/if}</td>
						
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
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
