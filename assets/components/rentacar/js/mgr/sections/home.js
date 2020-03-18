rentacar.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'rentacar-panel-home',
            renderTo: 'rentacar-panel-home-div'
        }]
    });
    rentacar.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.page.Home, MODx.Component);
Ext.reg('rentacar-page-home', rentacar.page.Home);