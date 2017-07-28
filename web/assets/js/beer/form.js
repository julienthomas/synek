$(function(){
    var typeNew    = $("[data-id='type-new']");
    var breweryNew = $("[data-id='brewery-new']");

    $("[name^='beer_type['], [name^='brewery[']").attr('required', false);

    $("[data-id='type-new-show']").click(function(){
        typeNew.slideDown();
    });

    $("[data-id='brewery-new-show']").click(function(){
        breweryNew.slideDown();
    });

    $("[data-id='beer-type-cancel']").click(function(){
        typeNew.slideUp();
    });

    $("[data-id='brewery-cancel']").click(function(){
        breweryNew.slideUp();
    });

    $("[data-id='beer-type-submit']").click(function(){
        createNewType();
    });

    $("[data-id='brewery-submit']").click(function(){
        createNewBrewery();
    });
});

function createNewType()
{
    var fields   = $("[name^='beer_type[']");
    var formData = new FormData();
    fields.each(function(){
        if ($(this).val() !== null) {
            var parent = $(this).parents('.form-group').first();
            formData.append($(this).attr('name'), $(this).val());
            parent.removeClass('has-error');
            $("[data-role='form-error']", parent).remove();
        }
    });

    $.ajax({
        type: 'POST',
        url: $("[data-id='beer-type-submit']").data('url'),
        dataType: 'json',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data, textStatus, xhr){
            if (xhr.status === 200) {
                $("[data-id='type-choice']").val(data.id).selectpicker('refresh');
                $("[data-id='type-new']").slideUp();
            } else if (xhr.status === 201) {
                refreshTypes(data.id, data.name);
            }
        },
        error: function(xhr){
            if (xhr.status === 400) {
                showTypeErrors(xhr.responseJSON);
            }
        }
    });
}

function createNewBrewery()
{
    var fields   = $("[name^='brewery[']");
    var formData = new FormData();
    fields.each(function(){
        if ($(this).val() !== null) {
            var parent = $(this).parents('.form-group').first();
            formData.append($(this).attr('name'), $(this).val());
            parent.removeClass('has-error');
            $("[data-role='form-error']", parent).remove();
        }
    });

    $.ajax({
        type: 'POST',
        url: $("[data-id='brewery-submit']").data('url'),
        dataType: 'json',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data, textStatus, xhr){
            if (xhr.status === 200) {
                $("[data-id='brewery-choice']").val(data.id).selectpicker('refresh');
                $("[data-id='brewery-new']").slideUp();
            } else if (xhr.status === 201) {
                refreshBreweries(data.id, data.name);
            }
        },
        error: function(xhr){
            if (xhr.status === 400) {
                showBreweryErrors(xhr.responseJSON);
            }
        }
    });
}

/**
 * @param jsonErrors
 */
function showTypeErrors(jsonErrors)
{
    $.each(jsonErrors, function(name, msg){
        var field  = $("[name='beer_type[" + name + "]'");
        var error  = $("[data-id='form-error-template']").clone();
        var parent = field.parents('.form-group').first();
        error.removeAttr('data-id');
        error.removeClass('form-error-template');
        $("[data-role='form-error-message']", error).html(msg);
        parent.addClass('has-error');
        field.after(error);
    });
}

/**
 * @param jsonErrors
 */
function showBreweryErrors(jsonErrors)
{
    $.each(jsonErrors, function(name, msg){
        var field  = $("[name='brewery[" + name + "]'");
        var error  = $("[data-id='form-error-template']").clone();
        var parent = field.parents('.form-group').first();
        error.removeAttr('data-id');
        error.removeClass('form-error-template');
        $("[data-role='form-error-message']", error).html(msg);
        parent.addClass('has-error');
        field.after(error);
    });
}

/**
 * @param id
 * @param name
 */
function refreshTypes(id, name)
{
    var select = $("[data-id='type-choice']");
    var option = $("<option value='" + id + "'>");

    option.text(name);
    select.append(option);

    var allOptions = $("option", select);
    sortOptions(allOptions);
    select.empty().append(allOptions);
    select.val(id);
    select.selectpicker('refresh');
    $("[data-id='type-new']").slideUp();
}

/**
 * @param id
 * @param name
 */
function refreshBreweries(id, name)
{
    var select = $("[data-id='brewery-choice']");
    var option = $("<option value='" + id + "'>");

    option.text(name);
    select.append(option);

    var allOptions = $("option", select);
    sortOptions(allOptions);
    select.empty().append(allOptions);
    select.val(id);
    select.selectpicker('refresh');
    $("[data-id='brewery-new']").slideUp();
}

/**
 *
 * @param options
 * @returns array
 */
function sortOptions(options)
{
    options.sort(function(option1, option2){
        if ($(option1).val() && $(option2).val()) {
            var text1 = option1.text;
            var text2 = option2.text;
            for (var i = 0; i < text1.length; i++) {
                if (i >= text2.length || text1[i] > text2[i]) {
                    return 1;
                } else if (text1[i] < text2[i]) {
                    return -1;
                }
            }
            return 0;
        }
    });
    return options;
}
