<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.dashboard.box.systemInfo.software{/lang}</h2>
	
	<dl>
		<dt>{lang}wcf.acp.dashboard.box.systemInfo.software.version{/lang}</dt>
		<dd>{WCF_VERSION}</dd>
	</dl>
	
	{event name='softwareFields'}
	
	<dl>
		<dt>{lang}wcf.acp.dashboard.box.systemInfo.software.databaseName{/lang}</dt>
		<dd>{$databaseName}</dd>
	</dl>

	{if WCF_N != 1}
		<dl>
			<dt>{lang}wcf.acp.dashboard.box.systemInfo.software.databaseNumber{/lang}</dt>
			<dd>{WCF_N}</dd>
		</dl>
	{/if}
</section>

{if !ENABLE_ENTERPRISE_MODE || $__wcf->getUser()->hasOwnerAccess()}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.dashboard.box.systemInfo.server{/lang}</h2>
		
		<dl>
			<dt>{lang}wcf.acp.dashboard.box.systemInfo.os{/lang}</dt>
			<dd>{$server[os]}</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.dashboard.box.systemInfo.webserver{/lang}</dt>
			<dd>{$server[webserver]}</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.dashboard.box.systemInfo.mySQLVersion{/lang}</dt>
			<dd>{$server[mySQLVersion]}</dd>
		</dl>
		
		{if $server[load]}
			<dl>
				<dt>{lang}wcf.acp.dashboard.box.systemInfo.load{/lang}</dt>
				<dd>{$server[load]}</dd>
			</dl>
		{/if}
		
		{if $server[innodbFlushLogAtTrxCommit] !== false}
			<dl>
				<dt>innodb_flush_log_at_trx_commit</dt>
				<dd>{$server[innodbFlushLogAtTrxCommit]}</dd>
			</dl>
		{/if}
		
		{event name='serverFields'}
	</section>

	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.dashboard.box.systemInfo.php{/lang}</h2>
		
		<dl>
			<dt>{lang}wcf.acp.dashboard.box.systemInfo.php.version{/lang}</dt>
			<dd>
				{if $__wcf->session->getPermission('admin.configuration.package.canInstallPackage') && $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage')}
					<a href="{link controller='PHPInfo'}{/link}">{PHP_VERSION}</a>
				{else}
					{PHP_VERSION}
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt>memory_limit</dt>
			<dd>
				{$server[memoryLimit]}
			</dd>
		</dl>
		
		<dl>
			<dt>post_max_size</dt>
			<dd>
				{$server[postMaxSize]}
			</dd>
		</dl>
		
		<dl>
			<dt>upload_max_filesize</dt>
			<dd>
				{$server[upload_max_filesize]}
			</dd>
		</dl>
		
		{event name='phpFields'}
	</section>
{/if}

{event name='systemFieldsets'}
