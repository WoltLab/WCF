<script data-relocate="true">
	require(['Language'], function(Language) {
		Language.addObject({
			'wcf.form.field.className.description.interface': '{jslang __literal=true}wcf.form.field.className.description.interface{/jslang}',
			{implode from=$definitionNames item=definitionName}
				'wcf.acp.pip.objectType.definitionName.{@$definitionName}.description': '{jslang __literal=true __optional=true}wcf.acp.pip.objectType.definitionName.{@$definitionName}.description{/jslang}'
			{/implode}
		});
		
		var definitionInterfaces = {
			{implode from=$definitionInterfaces key=definitionID item=interfaceName}
				{@$definitionID}: '{@$interfaceName|encodeJS}'
			{/implode}
		};
		
		var classNameDescription = elById('className').nextElementSibling;
		var definitionID = elById('definitionID');
		var definitionIDDescription = definitionID.nextElementSibling;
		
		function update() {
			// update description of `definitionName` field
			definitionIDDescription.innerHTML = Language.get('wcf.acp.pip.objectType.definitionName.' + definitionID.options.item(definitionID.selectedIndex).textContent + '.description');
			
			// update description of `className` field with new interface
			if (definitionInterfaces[definitionID.value]) {
				classNameDescription.innerHTML = Language.get('wcf.form.field.className.description.interface', {
					interface: definitionInterfaces[definitionID.value]
				});
			}
		}
		
		definitionID.addEventListener('change', update);
	});
</script>
