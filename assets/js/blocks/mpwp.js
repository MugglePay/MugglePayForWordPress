const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const mpwp_data = window.wc.wcSettings.getSetting("mpwp_data");

const mpwp_label = wp.htmlEntities.decodeEntities(mpwp_data.title || "");

const mpwp_Content = () => {
	return wp.htmlEntities.decodeEntities(mpwp_data.description || "");
}

const mpwp_Label = (props) => {
	const { PaymentMethodLabel } = props.components;
	return React.createElement(PaymentMethodLabel, { text: mpwp_label });
}

registerPaymentMethod({
	name: "mpwp",
	title: React.createElement(mpwp_Label),
	description: React.createElement(mpwp_Content),
	label: React.createElement(mpwp_Label),
	content: React.createElement(mpwp_Content),
	gatewayId : "mpwp",
	edit: React.createElement(mpwp_Content),
	canMakePayment: () => true,
	ariaLabel: mpwp_label,
	supports: {
		features: mpwp_data.supports,
	}
});