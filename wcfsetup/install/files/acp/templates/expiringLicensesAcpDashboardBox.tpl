{hascontent}
	<div class="acpDashboardBox__keyValueGroup">
		<h2 class="acpDashboardBox__keyValueGroup_title">{lang}wcf.acp.dashboard.box.expiringLicenses{/lang}</h2>
		{content}
			{foreach from=$expiringLicenses item=date key=packageName}
				<dl class="plain acpDashboardBox__keyValue">
					<dd class="acpDashboardBox__keyValue__key">{$packages[$packageName]}</dd>
					<dt class="acpDashboardBox__keyValue__value">{time time=$date}</dt>
				</dl>
			{/foreach}
		{/content}
	</div>
{/hascontent}
{hascontent}
	<div class="acpDashboardBox__keyValueGroup">
		<h2 class="acpDashboardBox__keyValueGroup_title">{lang}wcf.acp.dashboard.box.expiredLicences{/lang}</h2>
		{content}
			{foreach from=$expiredLicenses item=date key=packageName}
				<dl class="plain acpDashboardBox__keyValue">
					<dd class="acpDashboardBox__keyValue__key">{$packages[$packageName]}</dd>
					<dt class="acpDashboardBox__keyValue__value">{time time=$date}</dt>
				</dl>
			{/foreach}
		{/content}
	</div>
{/hascontent}
