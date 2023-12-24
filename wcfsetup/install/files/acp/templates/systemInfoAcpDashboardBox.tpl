<div class="acpDashboardBox__keyValueGroup">
	<dl class="plain acpDashboardBox__keyValue">
		<dt class="acpDashboardBox__keyValue__key">{lang}wcf.acp.dashboard.box.systemInfo.software.version{/lang}</dt>
		<dd class="acpDashboardBox__keyValue__value">{WCF_VERSION}</dd>
	</dl>
	
	{event name='softwareFields'}
	
	<dl class="plain acpDashboardBox__keyValue">
		<dt class="acpDashboardBox__keyValue__key">{lang}wcf.acp.dashboard.box.systemInfo.software.databaseName{/lang}</dt>
		<dd class="acpDashboardBox__keyValue__value">{$databaseName}</dd>
	</dl>

	{if WCF_N != 1}
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">{lang}wcf.acp.dashboard.box.systemInfo.software.databaseNumber{/lang}</dt>
			<dd class="acpDashboardBox__keyValue__value">{WCF_N}</dd>
		</dl>
	{/if}
</div>

{if !ENABLE_ENTERPRISE_MODE || $__wcf->getUser()->hasOwnerAccess()}
	<div class="acpDashboardBox__keyValueGroup">
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">{lang}wcf.acp.dashboard.box.systemInfo.webserver{/lang}</dt>
			<dd class="acpDashboardBox__keyValue__value">{$server[webserver]} ({$server[os]})</dd>
		</dl>
		
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">{lang}wcf.acp.dashboard.box.systemInfo.mySQLVersion{/lang}</dt>
			<dd class="acpDashboardBox__keyValue__value">{$server[mySQLVersion]}</dd>
		</dl>
		
		{if $server[innodbFlushLogAtTrxCommit] !== false}
			<dl class="plain acpDashboardBox__keyValue">
				<dt class="acpDashboardBox__keyValue__key">innodb_flush_log_at_trx_commit</dt>
				<dd class="acpDashboardBox__keyValue__value">{$server[innodbFlushLogAtTrxCommit]}</dd>
			</dl>
		{/if}
		
		{event name='serverFields'}
	</div>

	<div class="acpDashboardBox__keyValueGroup">
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">{lang}wcf.acp.dashboard.box.systemInfo.php.version{/lang}</dt>
			<dd class="acpDashboardBox__keyValue__value">
				{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage') && $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
					<a href="{link controller='PHPInfo'}{/link}">{PHP_VERSION}</a>
				{else}
					{PHP_VERSION}
				{/if}
			</dd>
		</dl>
		
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">memory_limit</dt>
			<dd class="acpDashboardBox__keyValue__value">
				{$server[memoryLimit]}
			</dd>
		</dl>
		
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">post_max_size</dt>
			<dd class="acpDashboardBox__keyValue__value">
				{$server[postMaxSize]}
			</dd>
		</dl>
		
		<dl class="plain acpDashboardBox__keyValue">
			<dt class="acpDashboardBox__keyValue__key">upload_max_filesize</dt>
			<dd class="acpDashboardBox__keyValue__value">
				{$server[upload_max_filesize]}
			</dd>
		</dl>
		
		{event name='phpFields'}
	</div>
{/if}

{event name='systemFieldsets'}
