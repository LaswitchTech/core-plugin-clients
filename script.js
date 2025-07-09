const ClientModalArchive = function(client, table = null, row = null){

    // Create a modal
    builder.Component(
        "modal",
        null,
        {
            onEnter: false,
            destroy: true,
            icon: "archive",
            title: builder.Locale.get("Are you sure you?"),
            body: builder.Locale.get("Your are about to archive this client. Are you sure you want to continue?"),
            cancel: false,
            submit: true,
            callback: {
                submit: function(element,modal){

                    // Create a spinner animate-rotate
                    var spinner = $(document.createElement('div')).attr({
                        "class": "animate-rotate rounded-circle border border-secondary border-4 d-none",
                        "style": "width: 96px; height: 96px; border-top-color: var(--bs-primary)!important;",
                    }).appendTo(element);

                    // Hide the dialog
                    element.dialog.addClass('opacity-0');

                    // Setup a spinner while waiting for the modal to be submitted
                    setTimeout(() => {

                        // Hide the dialog
                        element.dialog.hide();

                        // Add flex to the modal
                        element.addClass('d-flex align-items-center justify-content-center');

                        // Show the spinner
                        spinner.removeClass('d-none');

                        // AJAX Request
                        $.ajax({
                            url: '/api/clients/archive?id='+client.id,
                            type: 'GET',dataType: 'json',
                            success: function(response) {

                                // Remove the item from the list
                                if(table && row){
                                    table.delete(row);
                                }

                                // Hide the modal
                                modal.hide();
                            }
                        });
                    }, 300);
                },
            },
        },
        function(modal,component){

            // Save the component
            const componentModal = component;

            // Style the modal
            component.header.addClass('text-bg-dark');
            component.footer.submit.addClass('btn-dark').removeClass('btn-link').attr({
                "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
            }).text(builder.Locale.get('Archive'));
            component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-archive me-1').prependTo(component.footer.submit);

            // Open the modal
            modal.show();
        },
    );
};

// Create a client
function process_function_ClientCreate(task, value, callback = null){

    // Check if the task is attached to a lead and the lead is loaded
    if(task.targetTable !== 'leads' || typeof task.target === 'undefined'){
        return;
    }

    // Check if the task is already linked to a client
    if(task.target.client.id !== null){

        // Execute Callback
        if(typeof callback === "function"){
            callback(task);
        }

        return;
    }

    // AJAX Request
    $.ajax({
        url: '/api/clients/create',
        headers: {'X-CSRF-Authorization': CSRF_KEY},
        type: 'POST',dataType: 'json',
        data: {lead: task.targetId,vcard: task.target.vcard.id},
        success: function(response) {

            // Execute Callback
            if(typeof callback === "function"){
                callback(task, response);
            }
        }
    });
}
function process_meta_ClientCreate(key = null){
    const metadata = {
        label: "Create a Client Profile",
        description: "Create a Client Profile from a Lead",
        type: "none",
    };
    return metadata[key] ? metadata[key] : metadata;
}
