var rentacar = {
    initialize: function (rentacarConfig) {
        if (!jQuery().jGrowl) {
            document.write('<script src="' + rentacarConfig['assetsUrl'] + 'js/lib/jquery.jgrowl.min.js"><\/script>');
        }
        $(document).ready(function () {
            $.jGrowl.defaults.closerTemplate = '<div>[ ' + rentacarConfig['closeMessage'] + ' ]</div>';
        });
        // searchform
        $(rentacarConfig['formSelector']).submit(function(e) {
            e.preventDefault();
            var msg = $(this).serialize();
            var url = rentacarConfig['actionUrl'];
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: url,
                data: msg,
                success:  function(data) {
                    if (data.success){
                        window.location.href = rentacarConfig['carcontainerUrl'];
                    }
                }
            });
        });
        // regions
        $(rentacarConfig['formRegionsSelector']).change(function(){
            var url = rentacarConfig['actionUrl'];
            var region = $(this).val();
            var parent = $(this).parent().parent();
            $.ajax({
                type: "POST",
                url: url,
                data: {region: region, action: 'form/getregions'},
                success:  function(data) {
                    parent.find(rentacarConfig['formPlacesSelector']).html(data);
                    parent.find(rentacarConfig['formPlacesSelector']).removeAttr("disabled");
                }
            });
        });
        // datepickers
        $('.datepickerfrom').datetimepicker({
            locale: rentacarConfig['cultureKey'],
            format: 'DD.MM.YYYY',
            minDate: moment(),
            ignoreReadonly: true
        });
        $('.datepickerto').datetimepicker({
            useCurrent: false,
            locale: rentacarConfig['cultureKey'],
            format: 'DD.MM.YYYY',
            minDate: moment().add(2, 'days'),
            ignoreReadonly: true
        });
        $(".datepickerfrom").on("dp.change", function (e) {
            $('.datepickerto').data("DateTimePicker").minDate(moment(e.date).add(2, 'days'));
        });
        $(".datepickerto").on("dp.change", function (e) {
            $('.datepickerfrom').data("DateTimePicker").maxDate(moment(e.date).subtract(2, 'days'));
        });
    }
};

$(document).ready(function(){
    rentacar.initialize(rentacarConfig);
    // change value
    var url = rentacarConfig['actionUrl'];
    var region = $(rentacarConfig['formRegionsSelector']).val();
    var parent = $(rentacarConfig['formRegionsSelector']).parent().parent();
    $.ajax({
        type: "POST",
        url: url,
        data: {region: region, action: 'form/getregions'},
        success:  function(data) {
            parent.find(rentacarConfig['formPlacesSelector']).html(data);
            parent.find(rentacarConfig['formPlacesSelector']).removeAttr("disabled");
        }
    });
})


//noinspection JSUnusedGlobalSymbols
rentacar.Message = {
    success: function (message, sticky) {
        if (message) {
            if (!sticky) {
                sticky = false;
            }
            $.jGrowl(message, {theme: 'rentacar-message-success', sticky: sticky});
        }
    },
    error: function (message, sticky) {
        if (message) {
            if (!sticky) {
                sticky = false;
            }
            $.jGrowl(message, {theme: 'rentacar-message-error', sticky: sticky});
        }
    },
    info: function (message, sticky) {
        if (message) {
            if (!sticky) {
                sticky = false;
            }
            $.jGrowl(message, {theme: 'rentacar-message-info', sticky: sticky});
        }
    },
    close: function () {
        $.jGrowl('close');
    },
};
