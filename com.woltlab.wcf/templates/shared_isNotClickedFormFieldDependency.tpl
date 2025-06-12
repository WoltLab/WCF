require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/IsNotClicked'], ({ IsNotClicked }) => {
	// dependency '{unsafe:$dependency->getId()}'
	new IsNotClicked(
		'{unsafe:$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{unsafe:$dependency->getField()->getPrefixedId()|encodeJS}'
	);
});
