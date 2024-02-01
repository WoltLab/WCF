{include file='header' pageTitle='wcf.acp.group.option.'|concat:$userGroupOption->optionName}

<script data-relocate="true">
	(function() {
		var container = document.getElementById('optionValueContainer');
		var parent = container.parentNode;
		
		// using a fragment is inefficient, but prevents strange transitions for booleans caused by checked state lost when changing the name
		var fragment = document.createDocumentFragment();
		fragment.appendChild(container);
		
		var dd, groupId, id, inputElement, inputElements, isBoolean, label, labels = container.getElementsByTagName('label');
		for (var i = 0, length = labels.length; i < length; i++) {
			label = labels[i];
			id = label.getAttribute('for') || '';
			if (id.match(/^userGroupOption(\d+)$/)) {
				groupId = RegExp.$1;
				dd = label.parentNode.nextElementSibling;
				if (dd !== null && dd.nodeName === 'DD') {
					inputElements = dd.querySelectorAll('input, select, textarea');
					isBoolean = (dd.childElementCount > 0 && dd.children[0].classList.contains('optionTypeBoolean'));
					for (var j = 0, innerLength = inputElements.length; j < innerLength; j++) {
						inputElement = inputElements[j];
						inputElement.name = 'values[' + groupId + ']' + (inputElement.name.slice(-2) === '[]' ? '[]' : '');
						
						if (isBoolean) {
							inputElement.checked = inputElement.hasAttribute('checked');
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
		
		[{@$everyoneGroupID}, {@$guestGroupID}, {@$userGroupID}].forEach(function(groupID) {
			elBySelAll('dl[data-group-id="' + groupID + '"] .jsBbcodeSelectOptionHtml', undefined, function (bbcodeHtml) {
				elBySel('input[type="checkbox"]', bbcodeHtml).checked = true;
				
				elHide(bbcodeHtml);
			});
		});
		
		{if $ownerGroupID}
			{if $userGroupOption->optionName === 'admin.user.accessibleGroups'}
				elBySelAll('dl[data-group-id]', container, function(dl) {
					var groupId = parseInt(elData(dl, 'group-id'), 10);
					
					elBySelAll('input[name="values[' + groupId + '][]"', undefined, function(input) {
						if (groupId === {@$ownerGroupID}) {
							var shadow = elCreate('input');
							shadow.type = 'hidden';
							shadow.name = input.name;
							shadow.value = input.value;
							
							input.parentNode.appendChild(shadow);
							
							input.disabled = true;
						}
						else {
							if (parseInt(input.value, 10) === {@$ownerGroupID}) {
								elRemove(input.closest('label'));
							}
						}
					});
				});
			{elseif $userGroupOption->optionName|in_array:$ownerGroupPermissions}
				elBySelAll('input[name="values[{@$ownerGroupID}]"]', undefined, function (input) {
					if (input.value === '1') {
						input.checked = true;
					}
					else {
						input.disabled = true;
					}
				});
			{/if}
		{/if}
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

{include file='shared_formError'}

{if VISITOR_USE_TINY_BUILD && $guestGroupID}
	<woltlab-core-notice type="warning">{lang}wcf.acp.group.excludedInTinyBuild.notice{/lang}</woltlab-core-notice>
{/if}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

<form method="post" action="{link controller='UserGroupOption' id=$userGroupOption->optionID}{/link}">
	<section class="section" id="optionValueContainer">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.group.option.{$userGroupOption->optionName}{/lang}</h2>
			<p class="sectionDescription">{implode from=$parentCategories item=parentCategory glue=' &raquo; '}{lang}wcf.acp.group.option.category.{@$parentCategory->categoryName}{/lang}{/implode}</p>
		</header>
		
		{foreach from=$groups item=group}
			<dl data-group-id="{@$group->groupID}">
				<dt>
					{if VISITOR_USE_TINY_BUILD && $guestGroupID == $group->groupID && $userGroupOption->excludedInTinyBuild}
						<span class="jsTooltip" title="{lang}wcf.acp.group.excludedInTinyBuild{/lang}">
							{icon name='bolt'}
						</span>
					{/if}
					{if $ownerGroupID == $group->groupID && $userGroupOption->optionName|in_array:$ownerGroupPermissions}
						<span class="jsTooltip" title="{lang}wcf.acp.group.ownerGroupPermission{/lang}">
							{icon name='shield-halved'}
						</span>
					{/if}
					<label for="userGroupOption{@$group->groupID}">{$group->getTitle()}</label>
				</dt>
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
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}">
		{csrfToken}
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
