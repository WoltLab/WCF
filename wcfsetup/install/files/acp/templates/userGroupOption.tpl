{include file='header'}

<script data-relocate="true">
	$(function() {
		$('#optionValueContainer label').each(function(index, label) {
			var $label = $(label);
			var $id = $label.prop('for');
			var $groupID = $id.replace(/^userGroupOption/, '');
			$label.parents('dl').children('dd').find('input, select, textarea').attr('id', $id).attr('name', 'values[' + $groupID + ']');
		});
	});
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.group.option.editingOption{/lang}</h1>
</header>

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
