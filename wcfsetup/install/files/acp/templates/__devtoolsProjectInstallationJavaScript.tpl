{if !$project->getPackage() && $project->getPackageArchive()->getOpenRequirements()|empty}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Devtools/Project/Installation/Confirmation'], function(Language, DevtoolsProjectInstallationConfirmation) {
			Language.addObject({
				'wcf.acp.devtools.project.installPackage.confirmMessage': '{jslang __literal=true}wcf.acp.devtools.project.installPackage.confirmMessage{/jslang}',
				'wcf.acp.package.install.title': '{jslang}wcf.acp.package.install.title{/jslang}'
			});
			
			DevtoolsProjectInstallationConfirmation.init({@$project->projectID}, '{@$project->name|encodeJS}');
		});
	</script>
{/if}

{if !$project->getPackageArchive()->getOpenRequirements()|empty}
	<div id="openPackageRequirements" class="jsStaticDialogContent" data-title="{lang}wcf.acp.devtools.project.installPackage.error.openRequirements.title{/lang}">
		<p>{lang}wcf.acp.devtools.project.installPackage.error.openRequirements{/lang}</p>
		
		<ul class="nativeList">
			{foreach from=$project->getPackageArchive()->getOpenRequirements() key=openPackage item=openRequirement}
				<li>{lang}wcf.acp.devtools.project.installPackage.openRequirement{/lang}</li>
			{/foreach}
		</ul>
	</div>
{/if}
