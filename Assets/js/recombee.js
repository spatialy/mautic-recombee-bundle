Mautic.recombeeOnLoad = function (container, response) {

}
mQuery('.recombee-preview .editor-basic').on('froalaEditor.contentChanged', function(){
        Mautic.recombeeUpdatePreview();
});

mQuery(document).on('blur', '.recombee-preview input:text', function(){
    Mautic.recombeeUpdatePreview();
});

mQuery(document).on('change', '.recombee-preview select', function(){
    Mautic.recombeeUpdatePreview();
});

mQuery(document).on('change', '.recombee-preview input:radio', function(){
    Mautic.recombeeUpdatePreview();
});

Mautic.recombeeUpdatePreview = function () {
    mQuery('#recombee-preview').fadeTo('normal', 0.4);
    mQuery('#recombee-preview-loader').show();
    var data = mQuery('form[name=recombee]').formToArray();
    Mautic.ajaxActionRequest('plugin:recombee:generatePreview', data, function (response) {
        if(response.content) {
            mQuery('#recombee-preview').html(response.content);
        }
        mQuery('#recombee-preview').fadeTo('normal', 1);
        mQuery('#recombee-preview-loader').hide();
    });
}


