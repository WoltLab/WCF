{capture assign='pageTitle'}{$object->getTitle()} - {lang}wcf.edit.versions{/lang}{/capture}

{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.edit.versions{/lang}: {$object->getTitle()}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{$object->getEditLink()}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>
			<li><a href="{$object->getLink()}" class="button"><span class="icon icon16 fa-arrow-right"></span> <span>{lang}wcf.edit.button.goToContent{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if !$diffs|empty}
{if !$diffs[0]|isset}
<div class="section tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			{foreach from=$languages item=language}
				<li data-name="language{@$language->languageID}"><a href="#">{$language}</a></li>
			{/foreach}
		</ul>
	</nav>
{/if}
{foreach from=$diffs key=languageID item=properties}
{if $languageID}<div class="tabMenuContent" data-name="language{@$languageID}">{/if}
<div class="section editHistoryDiff">
	<table class="table">
		<thead>
			<tr>
				<th>{lang}wcf.edit.headline.old{/lang}</th>
				<th>{lang}wcf.edit.headline.newOrCurrent{/lang}</th>
			</tr>
		</thead>
		
		<tbody>
		{foreach from=$properties key=property item=diff}
			<tr>
				<td class="diffSection" colspan="2">{lang}wcf.edit.headline.comparison{/lang}: {$objectTypeProcessor->getPropertyLabel($property)}</td>
			</tr>
	
			{assign var='prevType' value=''}
			{assign var='colspan' value=false}
			{foreach from=$diff item='line'}
				{if $line[0] !== $prevType}
					{if $prevType !== ''}</td>{/if}
					
					{* unmodified, after deletion needs a "fake" insertion *}
					{if $line[0] === ' ' && $prevType === '-'}<td></td>{/if}
					
					{* unmodified and deleted start a new container *}
					{if $prevType !== '' && ($line[0] === ' ' || $line[0] === '-')}</tr>{/if}
					
					{* adding, without deleting needs a "fake" deletion *}
					{if $line[0] === '+' && $prevType !== '-'}
						{if $prevType !== ''}</tr>{/if}
						<tr>
							<td></td>
					{/if}
					
					{if $line[0] === ' '}
						<tr>
						{assign var='colspan' value=true}
					{/if}
					{if $line[0] === '-'}
						<tr>
					{/if}
					<td{if $line[0] === '+'} class="diffAdded"{elseif $line[0] === '-'} class="diffRemoved"{/if}{if $colspan} colspan="2"{assign var='colspan' value=false}{/if}>
				{/if}
				{if $line[0] === ' '}{@$line[1]}<br>{/if}
				{if $line[0] === '-'}{@$line[1]}<br>{/if}
				{if $line[0] === '+'}{@$line[1]}<br>{/if}
				{assign var='prevType' value=$line[0]}
			{/foreach}
		{/foreach}
		</tbody>
	</table>
</div>
{if $languageID}</div>{/if}
{/foreach}
{if !$diffs[0]|isset}</div>{/if}
{/if}

<form action="{link controller='VersionTrackerList'}{/link}" method="post">
	<section class="section tabularBox editHistoryVersionList">
		{assign var='versionCount' value=$versions|count}
		<h2 class="sectionTitle">
			{lang}wcf.edit.versions{/lang} <span class="badge">{#$versionCount+1}</span>
		</h2>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnEditID" colspan="2">{lang}wcf.edit.version{/lang}</th>
					<th class="columnText columnUser">{lang}wcf.user.username{/lang}</th>
					<th class="columnDate columnTime">{lang}wcf.edit.time{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td class="columnIcon">
						<span class="icon icon16 fa-undo disabled"></span>
						<input type="radio" name="oldID" value="current"{if $oldID === 'current'} checked{/if}> <input type="radio" name="newID" value="current"{if $newID === 'current'} checked{/if}>
						{event name='rowButtons'}
					</td>
					<td class="columnID"><strong>{lang}wcf.edit.currentVersion{/lang}</strong></td>
					<td class="columnText columnUser">{if $object->getUserID()}<a href="{link controller='UserEdit' id=$object->getUserID()}{/link}">{$object->getUsername()}{else}---{/if}</a></td>
					<td class="columnDate columnTime">{if $object->getTime()}{@$object->getTime()|time}{else}---{/if}</td>
					
					{event name='columns'}
				</tr>
				{foreach from=$versions item=edit name=edit}
					<tr class="jsEditRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-undo pointer jsRevertButton jsTooltip" title="{lang}wcf.edit.revert{/lang}" data-object-id="{@$edit->versionID}" data-confirm-message="{lang __encode=true}wcf.edit.revert.sure{/lang}"></span>
							<input type="radio" name="oldID" value="{@$edit->versionID}"{if $oldID == $edit->versionID} checked{/if}> <input type="radio" name="newID" value="{@$edit->versionID}"{if $newID == $edit->versionID} checked{/if}>
							{event name='rowButtons'}
						</td>
						<td class="columnID">{#($tpl[foreach][edit][total] - $tpl[foreach][edit][iteration] + 1)}</td>
						<td class="columnText columnUser"><a href="{link controller='User' id=$edit->userID title=$edit->username}{/link}">{$edit->username}</a></td>
						<td class="columnDate columnTime">{@$edit->time|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
			
			{js application='wcf' file='WCF.Message' bundle='WCF.Combined'}
			<script data-relocate="true">
				$(function () {
					new WCF.Message.EditHistory($('input[name=oldID]'), $('input[name=newID]'), '.jsEditRow', undefined, {
						isVersionTracker: true,
						versionTrackerObjectType: '{$objectType}',
						versionTrackerObjectId: {@$objectID},
						redirectUrl: '{$object->getEditLink()}'
					});
				});
			</script>
		</table>
	</section>
	
	<div class="formSubmit">
		<input type="hidden" name="objectID" value="{$objectID}">
		<input type="hidden" name="objectType" value="{$objectType}">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.edit.button.compare{/lang}</button>
	</div>
</form>

{include file='footer'}
