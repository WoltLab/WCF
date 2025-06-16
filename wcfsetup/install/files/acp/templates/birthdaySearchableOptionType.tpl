<input type="number" id="{$option->optionName}_age_from" name="values[{$option->optionName}][ageFrom]" value="{$valueAgeFrom}" placeholder="{lang}wcf.user.birthday.age.from{/lang}" min="0" max="120" class="tiny">
<input type="number" id="{$option->optionName}_age_to" name="values[{$option->optionName}][ageTo]" value="{$valueAgeTo}" placeholder="{lang}wcf.user.birthday.age.to{/lang}" min="0" max="120" class="tiny">

<script data-relocate="true">
	{
		const input = document.getElementById('{unsafe:$option->optionName|encodeJS}_age_from');
		if (input) {
			const label = input.closest('dl').querySelector(':scope > dt > label');
			if (label) {
				label.textContent = '{jslang}wcf.user.birthday.age{/jslang}';
				label.setAttribute('for', '{unsafe:$option->optionName|encodeJS}_age_from');
			}
		}
	}
</script>
