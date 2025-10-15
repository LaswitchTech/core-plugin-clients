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
