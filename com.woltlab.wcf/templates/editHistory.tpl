{capture assign='pageTitle'}{$object->getTitle()} - {lang}wcf.edit.versions{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.edit.versions{/lang}: {$object->getTitle()}{/capture}

{capture assign='contentHeaderNavigation'}<li><a href="{$object->getLink()}" class="button buttonPrimary">{icon name='arrow-right'} <span>{lang}wcf.edit.button.goToContent{/lang}</span></a></li>{/capture}

{capture assign='contentInteractionButtons'}
	<a href="{link controller='EditHistory' objectType=$objectType->objectType objectID=$objectID newID=$newID oldID=$oldID mode='html'}{/link}" class="contentInteractionButton button small{if $mode == 'html'} active{/if}">{icon name='align-left' type='solid'} <span>{lang}wcf.edit.mode.html{/lang}</span></a>
	<a href="{link controller='EditHistory' objectType=$objectType->objectType objectID=$objectID newID=$newID oldID=$oldID mode='raw'}{/link}" class="contentInteractionButton button small{if $mode == 'raw'} active{/if}">{icon name='code' type='solid'} <span>{lang}wcf.edit.mode.raw{/lang}</span></a>
{/capture}

{include file='header'}

{if $mode == 'html'}
<template id="oldMessage"><div>{unsafe:$old->getMessage()}<div></template>
<template id="newMessage"><div>{unsafe:$new->getMessage()}<div></template>
<div class="section editHistoryDiff">
	<div class="htmlContent" id="results"></div>
</div>
<script data-relocate="true">
	require(['@woltlab/visual-dom-diff'], ({ visualDomDiff }) => {
		const fragment = visualDomDiff(document.getElementById('oldMessage').content.cloneNode(true).firstChild, document.getElementById('newMessage').content.cloneNode(true).firstChild);
		document.getElementById('results').append(fragment);
	});
</script>
{elseif $diff}
<div class="section editHistoryDiff">
	<table class="table">
		<thead>
			<tr>
				<th>{lang}wcf.edit.headline.old{/lang}</th>
				<th>{lang}wcf.edit.headline.new{/lang}</th>
			</tr>
		</thead>
		
		<tbody>
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
		</tbody>
	</table>
</div>
{/if}

<form action="{link controller='EditHistory'}{/link}" method="post">
	<section class="section tabularBox editHistoryVersionList">
		{assign var='versionCount' value=$objects|count}
		<h2 class="sectionTitle">
			{lang}wcf.edit.versions{/lang} <span class="badge">{#$versionCount+1}</span>
		</h2>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnEditID" colspan="2">{lang}wcf.edit.version{/lang}</th>
					<th class="columnText columnUser">{lang}wcf.user.username{/lang}</th>
					<th class="columnText columnEditReason">{lang}wcf.edit.reason{/lang}</th>
					<th class="columnDate columnTime">{lang}wcf.edit.time{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td class="columnIcon">
						<span class="disabled">
							{icon name='rotate-left'}
						</span>
						<input type="radio" name="oldID" value="current"{if $oldID === 'current'} checked{/if}> <input type="radio" name="newID" value="current"{if $newID === 'current'} checked{/if}>
						{event name='rowButtons'}
					</td>
					<td class="columnID"><strong>{lang}wcf.edit.currentVersion{/lang}</strong></td>
					<td class="columnText columnUser"><a href="{link controller='User' id=$object->getUserID() title=$object->getUsername()}{/link}">{$object->getUsername()}</a></td>
					<td class="columnText columnEditReason">{$object->getEditReason()}</td>
					<td class="columnDate columnTime">{@$object->getTime()|time}</td>
					
					{event name='columns'}
				</tr>
				{foreach from=$objects item=edit name=edit}
					<tr class="jsEditRow">
						<td class="columnIcon">
							<button type="button" class="jsRevertButton jsTooltip" title="{lang}wcf.edit.revert{/lang}" data-object-id="{@$edit->entryID}" data-confirm-message="{lang __encode=true}wcf.edit.revert.sure{/lang}">
								{icon name='rotate-left'}
							</button>
							<input type="radio" name="oldID" value="{$edit->entryID}"{if $oldID == $edit->entryID} checked{/if}> <input type="radio" name="newID" value="{$edit->entryID}"{if $newID == $edit->entryID} checked{/if}>
							{event name='rowButtons'}
						</td>
						<td class="columnID">{#($tpl[foreach][edit][total] - $tpl[foreach][edit][iteration] + 1)}</td>
						<td class="columnText columnUser"><a href="{link controller='User' id=$edit->userID title=$edit->username}{/link}">{$edit->username}</a></td>
						<td class="columnText columnEditReason">{$edit->editReason}</td>
						<td class="columnDate columnTime">{@$edit->time|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
			<script data-relocate="true">
				$(function () {
					new WCF.Message.EditHistory($('input[name=oldID]'), $('input[name=newID]'), '.jsEditRow');
				});
			</script>
		</table>
	</section>
	
	<div class="formSubmit">
		<input type="hidden" name="objectID" value="{$objectID}">
		<input type="hidden" name="objectType" value="{$objectType->objectType}">
		<input type="hidden" name="mode" value="{$mode}">
		<button type="submit" class="button buttonPrimary">{lang}wcf.edit.button.compare{/lang}</button>
	</div>
</form>

{include file='footer'}
