/* global Ext */
Ext.define('djem.view.main.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.main-toolbar',

    requires: [
        'djem.view.main.ToolbarController',
        'djem.view.main.ToolbarModel'
    ],

    controller: 'main-toolbar',
    viewModel: {
        type: 'main-toolbar'
    },

    items: [ {
        text: 'Save',
        scale: 'medium',
        glyph: 'xf0c7@FontAwesome',
        reference: 'save'
    }, {
        text: '?',
        scale: 'medium',
        reference: 'add'
    }, {
        text: 'Undo',
        scale: 'medium',
        glyph: 'xf0e2@FontAwesome',
        reference: 'undo'
    }, {
        text: 'Redo',
        scale: 'medium',
        glyph: 'xf01e@FontAwesome',
        disabled: true,
        reference: 'redo'
    }, {
        text: 'Close',
        scale: 'medium',
        glyph: 'xf00d@FontAwesome',
        reference: 'close'
    }, {
        reference: 'user'
    } ]
});
