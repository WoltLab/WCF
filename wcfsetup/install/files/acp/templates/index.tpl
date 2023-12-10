{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.global.acp{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li>
				<button
					type="button"
					id="configureDashboard"
					class="button"
					data-url="{$endpointConfigureDashboard}"
				>
					{icon name='gear' type='solid'}
					<span>{lang}wcf.acp.dashboard.configure{/lang}</span>
				</button>
			</li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{event name='userNotice'}

<div class="acpDashboard">
	{foreach from=$dashboard->getVisibleBoxes() item='box'}
		<div class="acpDashboardBox">
			<h2 class="acpDashboardBox__title">{$box->getTitle()}</h2>
			<div class="acpDashboardBox__content">
				{@$box->getContent()}
			</div>
		</div>
	{/foreach}
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Acp/Controller/Dashboard/Configure'], ({ setup }) => {
		setup(document.getElementById('configureDashboard'));
	});
</script>

{include file='footer'}
