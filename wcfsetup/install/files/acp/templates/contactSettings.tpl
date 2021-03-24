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
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\contact\option\ContactOptionAction">
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
					<tr class="sortableNode jsOptionRow jsObjectActionObject" data-object-id="{@$option->optionID}">
						<td class="columnIcon">
							{objectAction action="toggle" isDisabled=$option->isDisabled}
							<a href="{link controller='ContactOptionEdit' id=$option->optionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $option->canDelete()}
								{objectAction action="delete" objectTitle=$option->getTitle()}
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
		<ol class="sortableList jsObjectActionContainer" data-object-id="0" data-object-action-class-name="wcf\data\contact\recipient\ContactRecipientAction">
			{foreach from=$recipientList item=recipient}
				<li class="sortableNode sortableNoNesting jsRecipient jsObjectActionObject" data-object-id="{@$recipient->recipientID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}">{$recipient}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							{objectAction action="toggle" isDisabled=$recipient->isDisabled}
							<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 fa-pencil"></a>
							{if $recipient->originIsSystem}
								<span class="icon icon16 fa-times disabled"></span>
							{else}
								{objectAction action="delete" objectTitle=$recipient->getName()}
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
