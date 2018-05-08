<script data-relocate="true">
	require(['Language'], function(Language) {
		Language.addObject({
			'wcf.form.field.className.description.interface': '{lang __literal=true}wcf.form.field.className.description.interface{/lang}',
			{implode from=$definitionNames item=definitionName}
				'wcf.acp.pip.objectType.definitionName.{@$definitionName}.description': '{lang __literal=true __optional=true}wcf.acp.pip.objectType.definitionName.{@$definitionName}.description{/lang}'
			{/implode}
		});
		
		var definitionNamesWithInterface = {
			{implode from=$definitionNamesWithInterface key=definitionName item=interfaceName}
				'{@$definitionName}': '{@$interfaceName|encodeJS}'
			{/implode}
		};
		
		var classNameDescription = elById('className').nextElementSibling;
		var definitionName = elById('definitionName');
		var definitionNameDescription = definitionName.nextElementSibling;
		
		function update() {
			// update description of `definitionName` field
			definitionNameDescription.innerHTML = Language.get('wcf.acp.pip.objectType.definitionName.' + definitionName.value + '.description');
			
			// update description of `className` field with new interface
			if (definitionNamesWithInterface[definitionName.value]) {
				classNameDescription.innerHTML = Language.get('wcf.form.field.className.description.interface', {
					interface: definitionNamesWithInterface[definitionName.value]
				});
			}
		}
		
		definitionName.addEventListener('change', update);
		
		update();
	});
</script>