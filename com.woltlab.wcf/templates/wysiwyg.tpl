{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value='text'}{/if}

{event name='wysiwyg'}

<script data-relocate="true">
	require([
		"WoltLabSuite/Core/Component/Ckeditor",
		"WoltLabSuite/Core/Component/Ckeditor/Configuration",
		"/wcf/editor/dist/bundle.js",
	], (
		{ setupCkeditor },
		{ createConfiguration },
	) => {
		{jsphrase name='wcf.editor.restoreDraft'}

		const element = document.getElementById('{$wysiwygSelector|encodeJS}');

		const features = {
			attachment: element.dataset.disableAttachments !== "true",
			autosave: element.dataset.autosave || "",
			html: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('html')}true{else}false{/if},
			image: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}true{else}false{/if},
			media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
			mention: element.dataset.supportMention === "true",
			spoiler: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('spoiler')}true{else}false{/if},
			url: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}true{else}false{/if},
		};

		element.dispatchEvent(
			new CustomEvent("ckeditor5:features", {
				detail: features,
			}),
		);

		const woltlabToolbarGroup = {
			format: {
				icon: "ellipsis;false",
				label: "TODO: Format text",
			},
			list: {
				icon: "list;false",
				label: "TODO: Insert list",
			},
		};

		const woltlabBbcode = [
			{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
				{
					icon: '{@$__bbcode->wysiwygIcon|encodeJS}',
					name: '{@$__bbcode->bbcodeTag|encodeJS}',
					label: '{@$__bbcode->getButtonLabel()|encodeJS}',
				},
			{/foreach}
		];
		if (features.media) {
			// TODO: This implicitly causes the button to be present twice, because
			// 		 the bbcode plugin does not check if the button already exists.
			woltlabBbcode.push({
				icon: "file-circle-plus;false",
				name: "media",
				label: "TODO: woltlab media bbcode"
			});
		}

		const config = createConfiguration(element, features);
		config.woltlabBbcode = woltlabBbcode;
		config.woltlabToolbarGroup = woltlabToolbarGroup;

		element.dispatchEvent(
			new CustomEvent("ckeditor5:config", {
				detail: config,
			}),
		);

		void setupCkeditor(element, config, features);
	});
</script>
