var rentacar = function (config) {
    config = config || {};
    rentacar.superclass.constructor.call(this, config);
};
Ext.extend(rentacar, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('rentacar', rentacar);

rentacar = new rentacar();