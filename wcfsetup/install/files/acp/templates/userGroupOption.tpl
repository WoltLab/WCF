{include file='header'}

<script data-relocate="true">
	$(function() {
		$('#optionValueContainer label').each(function(index, label) {
			var $label = $(label);
			var $id = $label.prop('for');
			if ($id && $id.match(/^userGroupOption/)) {
				var $groupID = $id.replace(/^userGroupOption/, '');
				$label.parents('dl').children('dd').find('input, select, textarea').each(function(index, element) {
					var $element = $(element);
					var $oldName = $element.attr('name');
					
					var $newName = 'values[' + $groupID + ']';
					if ($oldName.substr(-2) == '[]') {
						$newName += '[]';
					}
					
					$element.attr('id', $id).attr('name', $newName);
				});
			}
		});
	});
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.group.option.editingOption{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='UserGroupOption' id=$userGroupOption->optionID}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset id="optionValueContainer">
			<legend>{lang}wcf.acp.group.option.{$userGroupOption->optionName}{/lang}</legend>
			
			<small>{implode from=$parentCategories item=parentCategory glue=' &raquo; '}{lang}wcf.acp.group.option.category.{@$parentCategory->categoryName}{/lang}{/implode}</small>
			
			{foreach from=$groups item=group}
				<dl>
					<dt><label for="userGroupOption{@$group->groupID}">{lang}{$group->groupName}{/lang}</label></dt>
					<dd>
						{@$formElements[$group->groupID]}
						
						{hascontent}<small>{content}{lang __optional=true}wcf.acp.group.option.{@$userGroupOption->optionName}.description{/lang}{/content}</small>{/hascontent}
					</dd>
				</dl>
			{/foreach}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}
