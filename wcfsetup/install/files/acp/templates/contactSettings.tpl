{include file='header' pageTitle='wcf.acp.contact.settings'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'optionList',
			className: 'wcf\\data\\contact\\option\\ContactOptionAction',
			isSimpleSorting: true,
		});
		new UiSortableList({
			containerId: 'recipientList',
			className: 'wcf\\data\\contact\\recipient\\ContactRecipientAction',
			isSimpleSorting: true
		});
	});
	
	$(function() {
		new WCF.Action.Delete('wcf\\data\\contact\\option\\ContactOptionAction', '.jsOptionRow');
		new WCF.Action.Toggle('wcf\\data\\contact\\option\\ContactOptionAction', $('.jsOptionRow'));
		
		new WCF.Action.Delete('wcf\\data\\contact\\recipient\\ContactRecipientAction', '.jsRecipient');
		new WCF.Action.Toggle('wcf\\data\\contact\\recipient\\ContactRecipientAction', '.jsRecipient');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.contact.settings{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ContactRecipientAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.contact.recipient.add{/lang}</span></a></li>
			<li><a href="{link controller='ContactOptionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.contact.option.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.contact.options{/lang}</h2>
	
	<div id="optionList" class="sortableListContainer">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnOptionID" colspan="2">{lang}wcf.global.objectID{/lang}</th>
					<th class="columnTitle columnOptionTitle">{lang}wcf.global.name{/lang}</th>
					<th class="columnText columnOptionType">{lang}wcf.acp.customOption.optionType{/lang}</th>
					<th class="columnDigits columnShowOrder">{lang}wcf.acp.customOption.showOrder{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="sortableList" data-object-id="0">
				{foreach from=$optionList item=option}
					<tr class="sortableNode jsOptionRow" data-object-id="{@$option->optionID}">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$option->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $option->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$option->optionID}"></span>
							<a href="{link controller='ContactOptionEdit' id=$option->optionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $option->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$option->optionID}" data-confirm-message-html="{lang __encode=true}wcf.acp.customOption.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$option->optionID}</td>
						<td class="columnTitle columnOptionTitle"><a href="{link controller='ContactOptionEdit' id=$option->optionID}{/link}">{$option->getTitle()}</a></td>
						<td class="columnText columnOptionType">{lang}wcf.acp.customOption.optionType.{$option->optionType}{/lang}</td>
						<td class="columnDigits columnShowOrder">{#$option->showOrder}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="formSubmit">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
	</div>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.contact.recipients{/lang}</h2>
	
	<div id="recipientList" class="sortableListContainer">
		<ol class="sortableList" data-object-id="0">
			{foreach from=$recipientList item=recipient}
				<li class="sortableNode sortableNoNesting jsRecipient" data-object-id="{@$recipient->recipientID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}">{$recipient}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							{if $recipient->isAdministrator}
								<span class="icon icon16 fa-check-square-o disabled"></span>
							{else}
								<span class="icon icon16 fa-{if !$recipient->isDisabled}check-square-o{else}square-o{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $recipient->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$recipient->recipientID}"></span>
							{/if}
							<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 fa-pencil"></a>
							{if $recipient->originIsSystem}
								<span class="icon icon16 fa-times disabled"></span>
							{else}
								<span title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon icon16 fa-times" data-object-id="{@$recipient->recipientID}" data-confirm-message-html="{lang __encode=true}wcf.acp.contact.recipient.delete.confirmMessage{/lang}">
							{/if}
							
							{event name='itemButtons'}
						</span>
					</span>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<div class="formSubmit">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
	</div>
</section>

{include file='footer'}
