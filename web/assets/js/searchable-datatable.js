(function ($){
    var table     = null;
    var datatable = null;

    $.fn.SearchableDatatable = function(options){
        var defaultOptions = {
            orderCellsTop: true,
            dom: 'ltipr'
        };
        var datatableOptions = $.extend({}, defaultOptions, options);
        table = $(this);

        buildFilters();
        datatable = $(this).DataTable(datatableOptions);

        table.on('click', "[data-id='datatable-filter-apply']", function() {
            applyFilters();
        });
    };

    /**
     * Set the filters on the second header row
     */
    function buildFilters()
    {
        $("[data-id='datatable-filters'] th", table).each(function(){
            if ($(this).data('type')) {
                var type = $(this).data('type');
                if (type == 'text') {
                    buildInputFilter($(this));
                } else if (type == 'choice') {
                    buildSelectFilter($(this))
                } else if (type == 'search') {
                    buildSubmitButton($(this));
                }
            }
        });
    }

    /**
     * Build an input filter
     *
     * @param cell
     */
    function buildInputFilter(cell)
    {
        var input = $("<input type='text' class='form-control' style='width: 100%;' placeholder='" + cell.text() + "'>");
        cell.html(input);
    }

    /**
     * Build a select filter
     *
     * @param cell
     */
    function buildSelectFilter(cell)
    {
        var select  = $("<select class='form-control' style='width: 100%;'>");
        var choices = cell.data('choices');
        var option = $('<option selected>');
        option.text();
        select.append(option);
        $.each(choices, function(id, name){
            var option = $("<option value='" + id + "'>");
            option.text(name);
            select.append(option);
        });
        cell.html(select);
    }

    /**
     * Build a search button
     *
     * @param cell
     */
    function buildSubmitButton(cell)
    {
        var button = $("<button type='button' data-id='datatable-filter-apply' class='btn btn-default pull-right'>");
        button.html(cell.html());
        cell.html(button);
    }

    /**
     * Apply serach value for each column and redraw the table
     */
    function applyFilters()
    {
        $("[data-type]", table).each(function(index) {
            var val = null;
            if ($(this).data('type') == 'text') {
                val = $('input', this).val();
                if (val !== null) {
                    datatable.columns(index).search(val);
                }
            }
        });
        datatable.draw();
    }
})(jQuery);
