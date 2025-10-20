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
    API.endpoint('/clients/create').data({lead: task.targetId,vcard: task.target.vcard.id}).execute(function(response){
        // Execute Callback
        if(typeof callback === "function"){
            callback(task, response);
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

// Verify if the client is Allocated
function process_function_ClientIsAllocated(task, value, callback = null){

    // Check if the target is loaded
    if(task.target === 'undefined'){
        return;
    }

    // Initialize clientID
    var clientID = null;

    // Handle different target tables
    switch(task.targetTable){
        case 'clients':
            clientID = task.targetId;
            break;
        case 'leads':
            clientID = task.target.client.id;
            break;
        default:
            return;
    }

    // AJAX Request
    API.endpoint('/clients/fetch?id='+clientID).execute(function(response, endpoint){

        // Check if the Client is Allocated
        if(response.record.task.assignedTo.id !== null){

            // Execute Callback
            if(typeof callback === "function"){
                callback(task, response);
            }
        }
    });
}
function process_meta_ClientIsAllocated(key = null){
    const metadata = {
        label: "Is Client Allocated?",
        description: "Check if the Client is Allocated",
        type: "none",
    };
    return metadata[key] ? metadata[key] : metadata;
}

// Mark as Delegated
function process_function_DelegateClient(task, value, callback = null){

    // Check if the target is loaded
    if(task.target === 'undefined'){
        return;
    }

    // Initialize clientID
    var clientID = null;

    // Handle different target tables
    switch(task.targetTable){
        case 'clients':
            clientID = task.targetId;
            break;
        case 'importers':
        case 'leads':
            clientID = task.target.client.id;
            break;
        default:
            return;
    }

    // AJAX Request
    API.endpoint('/clients/delegate').data({id: clientID}).suppress().execute(function(response, endpoint){

        // Execute Callback
        if(typeof callback === "function"){
            callback(task, response);
        }
    });
}
function process_meta_DelegateClient(key = null){
    const metadata = {
        label: "Mark Client as delegated",
        description: "Mark a Client as delegated",
        type: "none",
    };
    return metadata[key] ? metadata[key] : metadata;
}
