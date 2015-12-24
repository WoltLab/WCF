{include file='header' pageTitle='wcf.acp.media.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.media.list{/lang}</h1>
	<p>{lang}wcf.acp.media.stats{/lang}</p>
</header>

{include file='formError'}

{*
TODO: add file search
<form method="post" action="{link controller='MediaList'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.filter{/lang}</legend>
			
			<dl>
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" class="long" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="filename">{lang}wcf.media.filename{/lang}</label></dt>
				<dd>
					<input type="text" id="filename" name="filename" value="{$filename}" class="long" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="fileType">{lang}wcf.media.fileType{/lang}</label></dt>
				<dd>
					<select name="fileType" id="fileType">
						<option value="">{lang}wcf.global.noSelection{/lang}</option>
						{htmlOptions options=$availableFileTypes selected=$fileType}
					</select>
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>
*}

<div class="contentNavigation">
	{assign var='linkParameters' value=''}
	{*
	{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
	{if $filename}{capture append=linkParameters}&filename={@$filename|rawurlencode}{/capture}{/if}
	{if $fileType}{capture append=linkParameters}&fileType={@$fileType|rawurlencode}{/capture}{/if}
	*}
	
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
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnMediaID{if $sortField == 'mediaID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=mediaID&sortOrder={if $sortField == 'mediaID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnFilename{if $sortField == 'filename'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filename&sortOrder={if $sortField == 'filename' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filename{/lang}</a></th>
					<th class="columnDate columnUploadTime{if $sortField == 'uploadTime'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=uploadTime&sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.uploadTime{/lang}</a></th>
					<th class="columnDigits columnFilesize{if $sortField == 'filesize'} active {@$sortOrder}{/if}"><a href="{link controller='MediaList'}pageNo={@$pageNo}&sortField=filesize&sortOrder={if $sortField == 'filesize' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.media.filesize{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=media}
					<tr class="jsMediaRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$media->media}" data-confirm-message="{lang}wcf.media.delete.confirmMessage{/lang}"></span>
							
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
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
