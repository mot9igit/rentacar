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
            html: '<h2>' + _('rentacar') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('rentacar_cars'),
                layout: 'anchor',
                items: [{
                    html: _('rentacar_cars_intro_msg'),
                    cls: 'panel-desc',
                    }, {
                        xtype: 'rentacar-grid-cars',
                        cls: 'main-wrapper',
                    }]
                },{
                    title: _('rentacar_cars_avaible'),
                    layout: 'anchor',
                    items: [{
                        html: _('rentacar_cars_avaible_intro_msg'),
                        cls: 'panel-desc',
                    }, {
                        //xtype: 'rentacar-cars-avaible-grid-items',
                        cls: 'main-wrapper',
                    }]
                },{
                    title: _('rentacar_cars_options'),
                    layout: 'anchor',
                    items: [{
                        html: _('rentacar_cars_options_intro_msg'),
                        cls: 'panel-desc',
                    }, {
                        xtype: 'rentacar-grid-options',
                        cls: 'main-wrapper',
                    }]
                },{
                title: _('rentacar_cars_warranty'),
                layout: 'anchor',
                items: [{
                    html: _('rentacar_cars_warranty_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'rentacar-grid-warrantys',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    rentacar.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(rentacar.panel.Home, MODx.Panel);
Ext.reg('rentacar-panel-home', rentacar.panel.Home);
