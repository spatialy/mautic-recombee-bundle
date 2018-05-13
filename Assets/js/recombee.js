Mautic.recombeeOnLoad = function (container, response) {

    //initialize ajax'd modals
    mQuery("button[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
        event.preventDefault();
        var obj = mQuery(this);
        console.log(mQuery('#recombee_template').val());
        Mautic.ajaxifyModal(obj, event);
    });

    Mautic.showRecombeeExample = function (id) {
        Mautic.ajaxActionRequest('plugin:recombee:generateExample', {'recombeeId': id}, function (response) {
            console.log(response);
        });
    }

}


