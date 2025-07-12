if( registerPaymentMethod == undefined ) 
{
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
}

const usdt_methods_data = window.wc.wcSettings.getSetting("usdt_methods_data");

const usdt_methods_label = wp.htmlEntities.decodeEntities(usdt_methods_data.title || "");

const usdt_methods_icon = () => {
    return usdt_methods_data.icon ? React.createElement("img", {
        src: usdt_methods_data.icon,
        style: {
            marginRight: '5px',
            width: '24px',
            height: '24px',
        }
    }) : null;
};

const usdt_methods_Content = () => {
	return wp.htmlEntities.decodeEntities(usdt_methods_data.description || "");
}

const usdt_methods_Label = (props) => {
    return React.createElement(
        "span",
        { style: { width: '100%', display: 'flex', alignItems: 'center' } },
        React.createElement(usdt_methods_icon),
        React.createElement('span', {}, usdt_methods_label)
    );
}

registerPaymentMethod({
	name: "usdt_methods",
	title: React.createElement(usdt_methods_Label),
	description: React.createElement(usdt_methods_Content),
	label: React.createElement(usdt_methods_Label),
	content: React.createElement(usdt_methods_Content),
	gatewayId : "usdt_methods",
	edit: React.createElement(usdt_methods_Content),
	canMakePayment: () => true,
	ariaLabel: usdt_methods_label,
	supports: {
		features: usdt_methods_data.supports,
	}
});