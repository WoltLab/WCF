{include file='header' pageTitle='wcf.acp.media.list'}

<script data-relocate="true">
	document.addEventListener('DOMContentLoaded', function() {
		require(['EventHandler', 'Language', 'Ui/SimpleDropdown', 'WoltLab/WCF/Controller/Clipboard', 'WoltLab/WCF/Media/Search'], function (EventHandler, Language, UiSimpleDropdown, Clipboard, MediaSearch) {
			Language.add('wcf.media.search.filetype', '{lang}wcf.media.search.filetype{/lang}');
			
			Clipboard.setup({
				hasMarkedItems: {if $hasMarkedItems}true{else}false{/if},
				pageClassName: 'wcf\\acp\\page\\MediaListPage'
			});
			
			EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.media', function (actionData) {
				// only consider events if the action has been executed
				if (actionData.responseData === null) {
					return;
				}
				
				if (actionData.data.actionName === 'com.woltlab.wcf.media.delete') {
					var mediaIds = actionData.responseData.objectIDs;
					
					var mediaRows = elByClass('jsMediaRow');
					for (var i = 0; i < mediaRows.length; i++) {
						var media = mediaRows[i];
						var mediaID = ~~elData(elByClass('jsClipboardItem', media)[0], 'object-id');
						
						if (mediaIds.indexOf(mediaID) !== -1) {
							elRemove(media);
							i--;
						}
					}
					
					if (!mediaRows.length) {
						window.location.reload();
					}
				}
			});
			
			new MediaSearch('{$fileType}');
			
			new WCF.Action.Delete('wcf\\data\\media\\MediaAction', '.jsMediaRow');
		});
	});
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.media.list{/lang}</h1>
	<p>{lang}wcf.acp.media.stats{/lang}</p>
</header>

{include file='formError'}

<form method="post" action="{link controller='MediaList'}{/link}">
	<section>
		<h1 class="subHeadline">{lang}wcf.global.filter{/lang}</h1>
		
		<dl>
			<dt><label for="filename">{lang}wcf.media.filename{/lang}</label></dt>
			<dd>
				<div class="inputAddon dropdown" id="mediaSearch">
					<span class="button dropdownToggle inputPrefix">
						<span class="active">{lang}wcf.media.search.filetype{/lang}</span>
					</span>
					<ul class="dropdownMenu">
						<li data-file-type="image"><span>{lang}wcf.media.search.filetype.image{/lang}</span></li>
						<li data-file-type="text"><span>{lang}wcf.media.search.filetype.text{/lang}</span></li>
						<li data-file-type="pdf"><span>{lang}wcf.media.search.filetype.pdf{/lang}</span></li>
						<li data-file-type="other"><span>{lang}wcf.media.search.filetype.other{/lang}</span></li>
						{event name='filetype'}
						<li class="dropdownDivider"></li>
						<li data-file-type="all"><span>{lang}wcf.media.search.filetype.all{/lang}</span></li>
					</ul>
					<input type="text" id="filename" name="filename" value="{$filename}" class="long" />
				</div>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" class="long" />
			</dd>
		</dl>
		
		{event name='filterFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div class="contentNavigation">
	{assign var='linkParameters' value=''}
	
	{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
	{if $filename}{capture append=linkParameters}&filename={@$filename|rawurlencode}{/capture}{/if}
	{if $fileType}{capture append=linkParameters}&fileType={@$fileType|rawurlencode}{/capture}{/if}
	
	{pages print=true assign=pagesLinks controller="MediaList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
	
	<nav>
		<ul>
			<li><a href="{link controller='MediaAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.media.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.media.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table jsClipboardContainer" data-type="com.woltlab.wcf.media">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll" /></label></th>
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
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$media->mediaID}" /></td>
						<td class="columnIcon">
							<a href="{link controller='MediaEdit' object=$media}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$media->mediaID}" data-confirm-message="{lang}wcf.media.delete.confirmMessage{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnMediaID">{@$media->mediaID}</td>
						<td class="columnTitle columnFilename">
							<div class="box48">
								{@$media->getElementTag(48)}
								
								<div>
									<p><a href="{link controller='MediaEdit' object=$media}{/link}">{$media->filename|tableWordwrap}</a></p>
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
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='MediaAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.media.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>

		<nav class="jsClipboardEditor" data-types="[ 'com.woltlab.wcf.media' ]"></nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
