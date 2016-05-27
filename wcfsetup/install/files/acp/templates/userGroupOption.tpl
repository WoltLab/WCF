{include file='header'}

<script data-relocate="true">
	(function() {
		var container = document.getElementById('optionValueContainer');
		var parent = container.parentNode;
		
		// using a fragment is inefficient, but prevents strange transitions for booleans caused by checked state lost when changing the name
		var fragment = document.createDocumentFragment();
		fragment.appendChild(container);
		
		var dd, groupId, id, inputElements, isBoolean, label, labels = container.getElementsByTagName('label');
		for (var i = 0, length = labels.length; i < length; i++) {
			label = labels[i];
			id = label.getAttribute('for') || '';
			if (id.match(/^userGroupOption(\d+)$/)) {
				groupId = RegExp.$1;
				dd = label.parentNode.nextElementSibling;
				if (dd !== null && dd.nodeName === 'DD') {
					inputElements = dd.querySelectorAll('input, select, textarea');
					isBoolean = (dd.childElementCount === 1 && dd.children[0].classList.contains('optionTypeBoolean'));
					for (var j = 0, innerLength = inputElements.length; j < innerLength; j++) {
						inputElement = inputElements[j];
						inputElement.name = 'values[' + groupId + ']' + (inputElement.name.slice(-2) === '[]' ? '[]' : '');
						
						if (isBoolean) {
							inputElement.checked = (inputElement.getAttribute('checked') === 'checked');
							id += '_' + groupId;
							label.removeAttribute('for');
							inputElement.nextElementSibling.setAttribute('for', id);
						}
						
						inputElement.id = id;
					}
				}
			}
		}
		
		if (parent.childElementCount) {
			parent.insertBefore(fragment, parent.children[0]);
		}
		else {
			parent.appendChild(fragment);
		}
	})();
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.option.editingOption{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<form method="post" action="{link controller='UserGroupOption' id=$userGroupOption->optionID}{/link}">
	<section class="section" id="optionValueContainer">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.group.option.{$userGroupOption->optionName}{/lang}</h2>
			<small class="sectionDescription">{implode from=$parentCategories item=parentCategory glue=' &raquo; '}{lang}wcf.acp.group.option.category.{@$parentCategory->categoryName}{/lang}{/implode}</small>
		</header>
		
		{foreach from=$groups item=group}
			<dl>
				<dt><label for="userGroupOption{@$group->groupID}">{lang}{$group->groupName}{/lang}</label></dt>
				<dd>
					{@$formElements[$group->groupID]}
					
					{if $errorType[$group->groupID]|isset}
						<small class="innerError">
							{lang}wcf.acp.group.option.error.{$errorType[$group->groupID]}{/lang}
						</small>
					{/if}
					{hascontent}<small>{content}{lang __optional=true}wcf.acp.group.option.{@$userGroupOption->optionName}.description{/lang}{/content}</small>{/hascontent}
				</dd>
			</dl>
		{/foreach}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
