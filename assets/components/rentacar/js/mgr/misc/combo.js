rentacar.combo.Search = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'twintrigger',
        ctCls: 'x-field-search',
        allowBlank: true,
        msgTarget: 'under',
        emptyText: _('search'),
        name: 'query',
        triggerAction: 'all',
        clearBtnCls: 'x-field-search-clear',
        searchBtnCls: 'x-field-search-go',
        onTrigger1Click: this._triggerSearch,
        onTrigger2Click: this._triggerClear,
    });
    rentacar.combo.Search.superclass.constructor.call(this, config);
    this.on('render', function () {
        this.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
            this._triggerSearch();
        }, this);
    });
    this.addEvents('clear', 'search');
};
Ext.extend(rentacar.combo.Search, Ext.form.TwinTriggerField, {

    initComponent: function () {
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
                {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
            ]
        };
    },

    _triggerSearch: function () {
        this.fireEvent('search', this);
    },

    _triggerClear: function () {
        this.fireEvent('clear', this);
    },

});
Ext.reg('rentacar-combo-search', rentacar.combo.Search);
Ext.reg('rentacar-field-search', rentacar.combo.Search);

var typeitems = new Ext.data.ArrayStore({
        id: 'type-items'
        ,fields: ['type',{name: 'display', type: 'string'}]
        ,data: [
            [1, _('rentacar_option_type_1')],
            [2, _('rentacar_option_type_2')],
            [3, _('rentacar_option_type_3')]
        ]
});
rentacar.combo.type = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: typeitems
        ,displayField: 'display'
        ,valueField: 'type'
        ,hiddenName: 'type'
        ,mode: 'local'
    });
    rentacar.combo.type.superclass.constructor.call(this,config);
};
Ext.extend(rentacar.combo.type,MODx.combo.ComboBox);
Ext.reg('combo-type',rentacar.combo.type);