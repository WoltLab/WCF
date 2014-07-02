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

<pre>{$diff}</pre>

<form action="{link controller='EditHistory'}{/link}" method="get">
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.edit.versions{/lang} <span class="badge badgeInverse">{#$objects|count}</span></h2>
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
						<input type="radio" name="oldID" value="current"{if $oldID == 'current'} checked="checked"{/if} /> <input type="radio" name="newID" value="current"{if $newID == 'current'} checked="checked"{/if} />
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
							<span class="icon icon16 icon-undo"></span>
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
					$('input[name=newID]').on('change', function (event) {
						var newID = $(this).val();
						
						$('input[name=oldID]').each(function (event) {
							if ($(this).val() >= newID) $(this).disable();
							else $(this).enable();
						});
					});
					
					$('input[name=oldID]').on('change', function (event) {
						var oldID = $(this).val();
						
						$('input[name=newID]').each(function (event) {
							if ($(this).val() <= oldID) $(this).disable();
							else $(this).enable();
						});
					});
					$('input:checked').trigger('change');
				});
				//]]>
			</script>
		</table>
	</div>
	
	<div class="formSubmit">
		{@SID_INPUT_TAG}
		<button class="button" data-type="submit">{lang}wcf.edit.button.compare{/lang}</button>
	</div>
</form>

{include file='footer'}

</body>
</html>
