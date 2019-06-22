{include file='__tabMenuFormContainer'}

{js application='wcf' file='WCF.Message' bundle='WCF.Combined'}
<script data-relocate="true">
	$(function() {
		$('#{@$container->getPrefixedId()}Container').messageTabMenu();
	});
</script>
