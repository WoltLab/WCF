{hascontent}
	<div class="acpDashboardBox__keyValueGroup">
		<p class="acpDashboardBox__explanation">
			{lang}wcf.acp.dashboard.box.expiringLicenses.expiringSoon{/lang}
		</p>
		<p class="acpDashboardBox__explanation acpDashboardBox__explanation--cta">
			{lang}wcf.acp.dashboard.box.expiringLicenses.expiringSoon.cta{/lang}
		</p>
		{content}
			{foreach from=$expiringLicenses item=date key=packageName}
				<dl class="plain acpDashboardBox__keyValue">
					<dd class="acpDashboardBox__keyValue__key">{$packages[$packageName]}</dd>
					<dt class="acpDashboardBox__keyValue__value" title="{$date|plainTime}">{dateInterval end=$date}</dt>
				</dl>
			{/foreach}
		{/content}
	</div>
{/hascontent}
{hascontent}
	<div class="acpDashboardBox__keyValueGroup">
		<p class="acpDashboardBox__explanation">
			{lang}wcf.acp.dashboard.box.expiringLicenses.expired{/lang}
		</p>
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

<div class="acpDashboardBox__cta">
	<a href="{$ctaLink}" class="button buttonPrimary" rel="nofollow noopener">{lang}wcf.acp.dashboard.box.expiringLicenses.cta{/lang}</a>
</div>
