if (typeof registerPaymentMethod === 'undefined') {
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
}

const muggle_pay_methods_data = window.wc.wcSettings.getSetting("muggle_pay_methods_data");

const Icon = () => {
    return muggle_pay_methods_data.icon ? React.createElement("img", {
        src: muggle_pay_methods_data.icon,
        style: {
            marginRight: '5px',
            width: '24px',
            height: '24px',
        }
    }) : null;
};

const muggle_pay_methods_Label = () => {
    return React.createElement(
        "span",
        { style: { width: '100%', display: 'flex', alignItems: 'center' } },
        React.createElement(Icon),
        React.createElement('span', {}, muggle_pay_methods_data.title)
    );
};

const muggle_pay_methods_Content = () => {
    return wp.htmlEntities.decodeEntities(muggle_pay_methods_data.description || "");
};

registerPaymentMethod({
    name: "muggle_pay_methods",
    title: React.createElement(muggle_pay_methods_Label),
    description: React.createElement(muggle_pay_methods_Content),
    label: React.createElement(muggle_pay_methods_Label),
    content: React.createElement(muggle_pay_methods_Content),
    gatewayId: "muggle_pay_methods",
    edit: React.createElement(muggle_pay_methods_Content),
    canMakePayment: () => true,
    ariaLabel: muggle_pay_methods_data.title || "",
    supports: {
        features: muggle_pay_methods_data.supports,
    },
});
