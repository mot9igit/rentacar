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
        // searchform
        $(rentacarConfig['formOfferSelector']).submit(function(e) {
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
                        if(data.location){
                            window.location.href = data.location;
                        }
                    }else{
                        // показать ошибку
                        miniShop2.Message.error(data.data.error);
                        //$.jGrowl(data.data.error,{ theme: 'ms2-message-error'});
                    }
                }
            });
        });
        $(rentacarConfig['formFormSelector']).submit(function(e) {
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
                        if(data.location){
                            window.location.href = data.location;
                        }
                        AjaxForm.Message.success(data.data.success);
                        $(".cart_" + data.data.data.order_id).html(data.data.html);
                        $(".order_cost_" + data.data.data.order_id).text(data.data.data.order_cost);
                        $("html, body, .modal").animate({ scrollTop: 0 }, "slow");
                    }else{
                        // показать ошибку
                        AjaxForm.Message.error(data.data.error);
                        //$.jGrowl(data.data.error,{ theme: 'ms2-message-error'});
                    }
                }
            });
        });
        // regions
        $(rentacarConfig['formOfferSelector']+" input, "+rentacarConfig['formOfferSelector']+" select").change(function(){
            var url = rentacarConfig['actionUrl'];
            var value = $(this).val();
            var name = $(this).attr("name");
            var option = $(this).val();
            var action = "formoffer/add";
            var parent = $(this).parent().parent();
            var count = parent.find("input[name=count]").val();
            var resource = $(rentacarConfig['formOfferSelector']+" input#resource").val();
            if($(this).attr("type") == "checkbox"){
                if($(this).prop("checked")){
                    action = "formoffer/add";
                }else{
                    action = "formoffer/remove";
                }
            }
            if($(this).attr("name") == "count"){
                name = "count";
                count = parent.find("input[name=count]").val();
                option = $(this).data("option");
            }
            if(!count){
                count = 1;
            }
            $.ajax({
                type: "POST",
                url: url,
                data: {value: value, name: name, count: count, action: action, id: resource, option: option, cultureKey: rentacarConfig['cultureKey'], ctx: rentacarConfig['ctx']},
                success:  function(data) {
                    $(rentacarConfig['priceDataSelector']).html(data);
                }
            });
        });
        // regions
        $(rentacarConfig['formRegionsSelector']).change(function(){
            var url = rentacarConfig['actionUrl'];
            var region = $(this).val();
            var parent = $(this).closest('form');
            $.ajax({
                type: "POST",
                url: url,
                data: {region: region, action: 'form/getregions', cultureKey: rentacarConfig['cultureKey'], ctx: rentacarConfig['ctx']},
                success:  function(data) {
                    parent.find(rentacarConfig['formPlacesSelector']).html(data);
                    parent.find(rentacarConfig['formPlacesSelector']).prop("disabled", false);
                }
            });
        });
        // datepickers
        if(rentacarConfig['cultureKey'] == 'gr'){
            var lang = 'el';
        }else{
            var lang = rentacarConfig['cultureKey'];
        }
        if ($("input.time").length) {
            $('input.time').datetimepicker({
                format: 'HH:mm',
                ignoreReadonly: true
            });
            $("input.time").on("dp.change", function (e) {
                $(this).trigger("change");
            });
        }
        if ($("input.datepickerfr").length) {
            $('input.datepickerfr').datetimepicker({
                locale: lang,
                format: 'DD.MM.YYYY',
                minDate: moment(),
                ignoreReadonly: true
            });
        }
        if ($("input.datepickerfromn").length) {
            $('input.datepickerfromn').datetimepicker({
                locale: lang,
                format: 'DD.MM.YYYY',
                ignoreReadonly: true
            });
            $("input.datepickerfromn").on("dp.change", function (e) {
                $('input.datepickerton').data("DateTimePicker").minDate(moment(e.date).add(2, 'days'));
                $(this).trigger("change");
            });
        }
        if ($("input.datepickerton").length) {
            $('input.datepickerton').datetimepicker({
                useCurrent: false,
                locale: lang,
                format: 'DD.MM.YYYY',
                ignoreReadonly: true
            });
            $("input.datepickerton").on("dp.change", function (e) {
                $('input.datepickerfromn').data("DateTimePicker").maxDate(moment(e.date).subtract(2, 'days'));
                $(this).trigger("change");
            });
        }
        if ($("input.datepickerfrom").length) {
            $('input.datepickerfrom').datetimepicker({
                locale: lang,
                format: 'DD.MM.YYYY',
                minDate: moment(),
                ignoreReadonly: true
            });
            $("input.datepickerfrom").on("dp.change", function (e) {
                $('input.datepickerto').data("DateTimePicker").minDate(moment(e.date).add(2, 'days'));
                $(this).trigger("change");
            });
        }
        if ($("input.datepickerto").length) {
            $('input.datepickerto').datetimepicker({
                useCurrent: false,
                locale: lang,
                format: 'DD.MM.YYYY',
                minDate: moment().add(2, 'days'),
                ignoreReadonly: true
            });
            $("input.datepickerto").on("dp.change", function (e) {
                $('input.datepickerfrom').data("DateTimePicker").maxDate(moment(e.date).subtract(2, 'days'));
                $(this).trigger("change");
            });
        }
    }
};

$(document).ready(function(){
    rentacar.initialize(rentacarConfig);
    // change value
    var url = rentacarConfig['actionUrl'];
    $(rentacarConfig['formRegionsSelector']).each(function(){
        var region = $(this).val();
        var parent = $(this).closest('form');
        if(!parent.hasClass("editbron")) {
            $.ajax({
                type: "POST",
                url: url,
                data: {region: region, action: 'form/getregions', cultureKey: rentacarConfig['cultureKey']},
                success:  function(data) {
                    parent.find(rentacarConfig['formPlacesSelector']).html(data);
                    parent.find(rentacarConfig['formPlacesSelector']).removeAttr("disabled");
                }
            });
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
