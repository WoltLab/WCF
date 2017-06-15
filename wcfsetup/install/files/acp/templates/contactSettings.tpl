{include file='header' pageTitle='wcf.acp.contact.settings'}

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
		<ol class="sortableList">
			{foreach from=$recipientList item=recipient}
				<li class="sortableNode sortableNoNesting" data-object-id="{@$recipient->recipientID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='ContactRecipientEdit' id=$recipient->recipientID}{/link}">{lang}{$recipient}{/lang}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
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
