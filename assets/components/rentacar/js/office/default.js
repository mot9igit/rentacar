Ext.onReady(function () {
    rentacar.config.connector_url = OfficeConfig.actionUrl;

    var grid = new rentacar.panel.Home();
    grid.render('office-rentacar-wrapper');

    var preloader = document.getElementById('office-preloader');
    if (preloader) {
        preloader.parentNode.removeChild(preloader);
    }
});