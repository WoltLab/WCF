require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/IsNotClicked'], ({ IsNotClicked }) => {
	// dependency '{@$dependency->getId()}'
	new IsNotClicked(
		'{@$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{@$dependency->getField()->getPrefixedId()|encodeJS}'
	);
});
