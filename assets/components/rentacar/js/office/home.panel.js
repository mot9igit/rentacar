rentacar.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'rentacar-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: false,
            hideMode: 'offsets',
            items: [{
                title: _('rentacar_items'),
                layout: 'anchor',
                items: [{
                    html: _('rentacar_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'rentacar-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    rentacar.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.panel.Home, MODx.Panel);
Ext.reg('rentacar-panel-home', rentacar.panel.Home);
