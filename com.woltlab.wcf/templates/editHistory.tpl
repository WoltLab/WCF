{include file='documentHeader'}

<head>
	<title>{$object->getTitle()} - {lang}wcf.edit.versions{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<header class="boxHeadline">
	<h1>{$object->getTitle()}</h1>
</header>

{include file='userNotice'}

{if $diff}
<div><div>
{assign var='prevType' value=''}
{foreach from=$diff->getRawDiff() item='line'}
{if $line[0] !== $prevType}
	</div>
	
	{* unmodified, after deletion needs a "fake" insertion *}
	{if $line[0] === ' ' && $prevType === '-'}<div class="containerPadding"></div>{/if}
	
	{* unmodified and deleted start a new container *}
	{if $line[0] === ' ' || $line[0] === '-'}</div>{/if}
	
	{* adding, without deleting needs a "fake" deletion *}
	{if $line[0] === '+' && $prevType !== '-'}
		</div>
		<div class="container marginTop sideBySide">
			<div class="containerPadding">
			</div>
	{/if}
	
	{if $line[0] === ' '}
		<div class="container marginTop">
	{/if}
	{if $line[0] === '-'}
		<div class="container marginTop sideBySide">
	{/if}
	<div class="containerPadding"{if $line[0] === '+'} style="color: green;"{elseif $line[0] === '-'} style="color: red;"{/if}>
{/if}
{if $line[0] === ' '}{$line[1]}<br />{/if}
{if $line[0] === '-'}{$line[1]}<br />{/if}
{if $line[0] === '+'}{$line[1]}<br />{/if}
{assign var='prevType' value=$line[0]}
{/foreach}
</div></div>
{/if}

<form action="{link controller='EditHistory'}{/link}" method="get">
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			{assign var='versionCount' value=$objects|count}
			<h2>{lang}wcf.edit.versions{/lang} <span class="badge badgeInverse">{#$versionCount+1}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnEditID" colspan="2">{lang}wcf.global.objectID{/lang}</th>
					<th class="columnText columnUser">{lang}wcf.user.username{/lang}</th>
					<th class="columnText columnTime">{lang}wcf.edit.time{/lang}</th>
					<th class="columnText columnEditReason">{lang}wcf.edit.reason{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td class="columnIcon">
						<span class="icon icon16 icon-undo disabled"></span>
						<input type="radio" name="oldID" value="current"{if $oldID === 'current'} checked="checked"{/if} /> <input type="radio" name="newID" value="current"{if $newID === 'current'} checked="checked"{/if} />
						{event name='rowButtons'}
					</td>
					<td class="columnID"><strong>{lang}wcf.edit.currentVersion{/lang}</strong></td>
					<td class="columnText columnUser"><a href="{link controller='User' id=$object->getUserID() title=$object->getUsername()}{/link}">{$object->getUsername()}</a></td>
					<td class="columnText columnTime">{@$object->getTime()|time}</td>
					<td class="columnText columnEditReason">{$object->getEditReason()}</td>
					
					{event name='columns'}
				</tr>
				{foreach from=$objects item=edit}
					<tr class="jsEditRow">
						<td class="columnIcon">
							<span class="icon icon16 icon-undo pointer jsRevertButton jsTooltip" title="{lang}wcf.edit.revert{/lang}" data-object-id="{@$edit->entryID}" data-confirm-message="{lang}wcf.edit.revert.sure{/lang}"></span>
							<input type="radio" name="oldID" value="{@$edit->entryID}"{if $oldID == $edit->entryID} checked="checked"{/if} /> <input type="radio" name="newID" value="{@$edit->entryID}"{if $newID == $edit->entryID} checked="checked"{/if} />
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$edit->entryID}</td>
						<td class="columnText columnUser"><a href="{link controller='User' id=$edit->userID title=$edit->username}{/link}">{$edit->username}</a></td>
						<td class="columnText columnTime">{@$edit->time|time}</td>
						<td class="columnText columnEditReason">{$edit->editReason}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
			<script data-relocate="true">
				//<![CDATA[
				$(function () {
					new WCF.Message.EditHistory($('input[name=oldID]'), $('input[name=newID]'), '.jsEditRow');
				});
				//]]>
			</script>
		</table>
	</div>
	
	<div class="formSubmit">
		{@SID_INPUT_TAG}
		<input type="hidden" name="objectID" value="{$objectID}" />
		<input type="hidden" name="objectType" value="{$objectType->objectType}" />
		<button class="button" data-type="submit">{lang}wcf.edit.button.compare{/lang}</button>
	</div>
</form>

{include file='footer'}

</body>
</html>
