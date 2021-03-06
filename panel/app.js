/*
 * This file is generated and updated by Sencha Cmd. You can edit this file as
 * needed for your application, but these edits will have to be merged by
 * Sencha Cmd when upgrading.
 */
Ext.application({
    name: 'djem',

    extend: 'djem.Application',

    autoCreateViewport: 'djem.view.main.Main',

    // -------------------------------------------------------------------------
    // Most customizations should be made to djem.Application. If you need to
    // customize this file, doing so below this section reduces the likelihood
    // of merge conflicts when upgrading to new versions of Sencha Cmd.
    // -------------------------------------------------------------------------

    requires: [
        'fix.overrides.Store', 'fix.overrides.ViewBoundList', 'fix.overrides.Date',

        'djem.overrides.LoadMask', 'djem.overrides.viewAbstractView'
    ]
});
