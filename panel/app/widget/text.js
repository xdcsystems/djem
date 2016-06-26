/* global Ext */
Ext.define('djem.widget.text', {
    extend: 'Ext.form.field.Text',
    alias: [ 'widget.djem.text' ],

    labelAlign: 'top',
    labelPad: null,
    labelSeparator: '',

    listeners: {
        change: function(field, newValue) {
            if (newValue) {
                field.addCls('app-field-filled');
            } else {
                field.removeCls('app-field-filled');
            }
        }
    }
});
