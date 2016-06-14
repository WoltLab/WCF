<input type="number" id="{$option->optionName}" name="values[{$option->optionName}][ageFrom]" value="{@$valueAgeFrom}" placeholder="{lang}wcf.user.birthday.age.from{/lang}" min="0" max="120" class="tiny">
<input type="number" id="{$option->optionName}_age_to" name="values[{$option->optionName}][ageTo]" value="{@$valueAgeTo}" placeholder="{lang}wcf.user.birthday.age.to{/lang}" min="0" max="120" class="tiny">

<script data-relocate="true">
//<![CDATA[
$(function() {
	$('#{$option->optionName}').parents('dl:eq(0)').find('> dt > label').text('{lang}wcf.user.birthday.age{/lang}');
});
//]]>
</script>