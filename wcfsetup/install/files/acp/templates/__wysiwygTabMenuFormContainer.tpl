{include file='__tabMenuFormContainer' __tabMenuCSSClassName='messageTabMenuNavigation'}

<script data-relocate="true">
	$(function() {
		$('#{@$container->getPrefixedId()}Container').messageTabMenu();
	});
</script>
