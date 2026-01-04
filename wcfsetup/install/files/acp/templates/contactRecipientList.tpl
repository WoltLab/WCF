{include file='header' pageTitle='wcf.acp.menu.link.contact.recipients'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.contact.recipients{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			{if $gridView->countRows() > 1}
				<li>
					<button type="button" class="button jsChangeShowOrder">{icon name='up-down'} <span>{lang}wcf.global.changeShowOrder{/lang}</span></button>
				</li>
			{/if}
			<li><a href="{link controller='ContactRecipientAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.contact.recipient.add{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{if $gridView->countRows() > 1}
	<script data-relocate="true">
		require(["WoltLabSuite/Core/Component/ChangeShowOrder"], ({ setup }) => {
			{jsphrase name='wcf.global.changeShowOrder'}

			setup(
				document.querySelector('.jsChangeShowOrder'),
				'core/contact/recipients/show-order',
			);
		});
	</script>
{/if}

{include file='footer'}
