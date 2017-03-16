jQuery(function () {

    function money_format(n) {
        var p = n.toFixed(2).split(".");
        return "R$ " + p[0].split("").reverse().reduce(function (acc, n, i) {
            return n + (i && !(i % 3) ? "." : "") + acc;
        }, "") + "," + p[1];
    }

    function set_price_range(a, b) {
        jQuery("#range-min").text(money_format(a));
        jQuery("#range-max").text(money_format(b));
        jQuery("#preco-min").val(a);
        jQuery("#preco-max").val(b);
    }

    function set_year_range(a, b) {
        jQuery("#range-min-ano").text(a);
        jQuery("#range-max-ano").text(b);
        jQuery("#ano-min").val(a);
        jQuery("#ano-max").val(b);
    }

    var pr_min = parseInt(jQuery('input[name=st_preco_min]').val());
    var pr_max = parseInt(jQuery('input[name=st_preco_max]').val());
    var ya_min = parseInt(jQuery('input[name=st_ano_min]').val());
    var ya_max = parseInt(jQuery('input[name=st_ano_max]').val());

    if (pr_max) {
        set_year_range(ya_min, ya_max);
        set_price_range(pr_min, pr_max);
    }

    jQuery("#range-preco").slider({range: true, min: pr_min, max: pr_max, values: [pr_min, pr_max], step: 5000, slide: function (event, ui) {
            set_price_range(ui.values[0], ui.values[1]);
        }});

    jQuery("#range-ano").slider({range: true, min: ya_min, max: ya_max, values: [ya_min, ya_max], step: 1, slide: function (event, ui) {
            set_year_range(ui.values[0], ui.values[1]);
        }});

    jQuery('.select-order').on('change', function () {
        jQuery('#submit').click();
    });
});