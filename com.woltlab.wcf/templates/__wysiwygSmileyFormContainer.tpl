{include file='__tabTabMenuFormContainer'}

<script data-relocate="true">
	$(function() {
		{if $container->children()|count > 1}
			new WCF.Message.SmileyCategories('{@$container->getWysiwygId()}');
		{/if}
		
		new WCF.Message.Smilies('{@$container->getWysiwygId()}');
	});
</script>
