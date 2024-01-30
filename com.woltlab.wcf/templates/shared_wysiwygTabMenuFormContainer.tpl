{include file='shared_tabMenuFormContainer' __tabMenuCSSClassName='messageTabMenuNavigation'}

<script data-relocate="true">
	$(function() {
		$('#{@$container->getPrefixedId()|encodeJS}Container').messageTabMenu();
	});
</script>
