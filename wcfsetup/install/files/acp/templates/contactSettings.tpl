{include file='header' pageTitle='wcf.acp.contact.settings'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Sortable/List'], function (UiSortableList) {
		new UiSortableList({
			containerId: 'recipientList',
			className: 'wcf\\data\\contact\\recipient\\ContactRecipientAction'
		});
	});
	
	$(function() {
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
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.contact.recipients{/lang}</h2>
	
	<div id="recipientList" class="sortableListContainer">
		<ol class="sortableList" data-object-id="0">
			{foreach from=$recipientList item=recipient}
				<li class="sortableNode sortableNoNesting jsRecipient" data-object-id="{@$recipient->recipientID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}">{lang}{$recipient}{/lang}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
							{if $recipient->isAdministrator}
								<span class="icon icon16 fa-check-square-o disabled"></span>
							{else}
								<span class="icon icon16 fa-{if !$recipient->isDisabled}check-square-o{else}square-o{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $recipient->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$recipient->recipientID}"></span>
							{/if}
							<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 fa-pencil"></a>
							<span title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon icon16 fa-times" data-object-id="{@$recipient->recipientID}" data-confirm-message-html="{lang __encode=true}wcf.acp.contact.recipient.delete.confirmMessage{/lang}">
							
							{event name='itemButtons'}
						</span>
					</span>
				</li>
			{/foreach}
		</ol>
		<div class="formSubmit">
			<button class="button" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
		</div>
	</div>
</section>

{include file='footer'}
