<?php

/**
 * Core Framework - ClientsEndpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Endpoint;

class ClientsEndpoint extends Endpoint {

    /**
     * Constructor
     */
    public function __construct()
    {

        // Call Parent Constructor
        parent::__construct();

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Set Global access
        $this->Public = false;

        // Set Level
        switch($namespace){
            case "/clients/index":
            case "/clients/assigned":
            case "/clients/details":
                $this->Level = 1;
                break;
            case "/clients/create":
                $this->Level = 2;
                break;
            case "/clients/archive":
            case "/clients/recover":
                $this->Level = 4;
                break;
        }
    }

    /**
     * Retrieve Clients
     */
    public function indexAction(): array
    {
        return ["status" => 200, "message" => "OK", "data" => $this->Model->Clients->list($this->Auth->user()->organization()->id)];
    }

    /**
     * Retrieve Assigned Clients
     */
    public function assignedAction(): array
    {
        return ["status" => 200, "message" => "OK", "data" => $this->Model->Clients->assigned($this->Auth->user()->organization()->id, $this->Auth->user()->id)];
    }

    /**
     * Retrieve Client's Details
     */
    public function detailsAction(): array
    {
        $message = ["status" => 200, "message" => "OK", "data" => []];
        $client = $this->Model->Clients->get(intval($this->Request->getParams('GET','id')));
        if(empty($client)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested client."];
        } else {
            if($client['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this client."];
            }
            if(($client['assignedTo']['id'] != $this->Auth->user()->id) && !$this->Auth->isAuthorized("AccountManager", 1)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this client."];
            }
        }
        if($message['status'] == 200){
            $client['task'] = $this->Model->Tasks->fetch(intval($client['task']['id']));
            $relationships = $this->Model->Relationship->get('clients', $client['id']);
            foreach($this->Model->Relationship->get('vcards', $client['vcard']['id']) as $table => $relations){
                foreach($relations as $id => $record){
                    $relationships[$table][$id] = $record;
                }
            }
            $message['data'] = [
                "record" => $client,
                "relationships" => $relationships,
            ];
        }
        return $message;
    }

    /**
     * Create a Client
     */
    public function createAction(): array
    {
        // Import Global Variables
        global $CSRF;

        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Check the request method
        if($this->Request->getMethod() == "POST"){
            $message["data"]["CSRF"] = [
                "token" => $CSRF->token(),
                "key" => $CSRF->key()
            ];
        }

        // Check if the task is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the parameters
                $lead = $this->Request->getParams('POST','lead') ?? null;

                // Check if the lead has been set
                if($lead){

                    // Retrieve the lead
                    $lead = $this->Model->Leads->get(intval($lead));

                    // Check if the lead is accessible
                    if(!empty($lead)){

                        // Check if the lead is in the same organization
                        if($lead['organization']['id'] === $this->Auth->user()->organization()->id){

                            // Check if the lead is assigned to the user or if the user is an Account Manager
                            if(($lead['assignedTo']['id'] == $this->Auth->user()->id) || $this->Auth->isAuthorized("AccountManager", 1)){

                                // Check if the lead is not already a client
                                if(is_null($lead['client']['id'])){

                                    // Initialize the Events
                                    $message['data']['events'] = [];

                                    // Create a Client
                                    $client = [
                                        'owner' => $this->Auth->user()->username,
                                        'organization' => $this->Auth->user()->organization()->id,
                                        'vcard' => $lead['vcard']['id'],
                                        'lead' => $lead['id'],
                                    ];
                                    $clientId = $this->Model->Clients->create($client);

                                    // Update the Lead
                                    $affectedRows = $this->Model->Leads->update($lead['id'], ['client' => $clientId]);

                                    // Retrieve the client process
                                    $process = $this->Model->Process->get('Client');

                                    // Create a Task
                                    $task = [
                                        'label' => 'Progress on <vcard>'.$lead['vcard']['id'].':'.$lead['vcard']['name'].'</vcard>',
                                        'category' => 'Client',
                                        'progress' => 0,
                                        'scale' => count($process['process']),
                                        'color' => 'primary',
                                        'link' => '/plugin/clients/details?id='.$clientId."&name=".urlencode($lead['vcard']['name']),
                                        'owner' => $this->Auth->user()->username,
                                        'process' => $process['process'],
                                        'isActive' => 0,
                                        'targetTable' => 'clients',
                                        'targetId' => $clientId,
                                    ];
                                    $taskId = $this->Model->Tasks->create($task);

                                    // Create the relationship
                                    $this->Model->Relationship->create('clients', $clientId, 'leads', $lead['id']);
                                    $this->Model->Relationship->create('leads', $lead['id'], 'clients', $clientId);

                                    // Update the Client
                                    $affectedRows = $this->Model->Clients->update($clientId, ['task' => $taskId]);

                                    // Create the related events
                                    $message['data']['events'][] = $this->Model->Event->create($this->Auth->user()->username, 'tasks', $taskId, 'Task', 'New Task Created for <vcard>'.$lead['vcard']['id'].':'.$lead['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>', '/plugin/tasks/index?id='.$taskId);
                                    $message['data']['events'][] = $this->Model->Event->create($this->Auth->user()->username, 'clients', $clientId, 'Client', 'New Client Created about <vcard>'.$lead['vcard']['id'].':'.$lead['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>', '/plugin/clients/details?id='.$clientId);

                                    // Count the affected rows
                                    $count = $affectedRows + ($taskId > 0 ? 1 : 0) + ($clientId > 0 ? 1 : 0);

                                    // Check if the client was created
                                    if($count == 3){

                                        // Retrieve the final client
                                        $message['data']['record'] = $this->Model->Clients->get($clientId);
                                    } else {
                                        $message['status'] = 500;
                                        $message['message'] = "Internal Server Error";
                                        $message['data']['error'] = "An error occurred while creating the client.";
                                    }
                                } else {
                                    $message = ["status" => 400, "message" => "Bad Request", "data" => "The lead is already a client."];
                                }
                            } else {
                                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this lead."];
                            }
                        } else {
                            $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this lead."];
                        }
                    } else {
                        $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested lead."];
                    }
                } else {
                    $message = ["status" => 400, "message" => "Bad Request", "data" => "Missing required parameters."];
                }
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        return $message;
    }

    /**
     * Archive a Client
     */
    public function archiveAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the Client
        $client = $this->Model->Clients->get(intval($this->Request->getParams('GET','id')));

        // Check if the Client is accessible
        if(empty($client)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested client."];
        } else {
            if($client['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this client."];
            }
            if(!$this->Auth->isAuthorized("AccountManager", 4)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to archive this client."];
            }
        }

        // Check if the Note is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Update the Client
                $this->Model->Clients->update($client['id'], ["isArchived" => 1]);

                // Update the Task
                $this->Model->Tasks->update($client['task']['id'], ["isActive" => 0]);

                // Retrieve the Updated Client
                $message["data"]["record"] = $this->Model->Clients->get($client['id']);
            } else {
                $message = ["status" => 400, "message" => "Bad Request", "data" => "Invalid Request Method"];
            }
        }

        return $message;
    }

    /**
     * Recover a Client
     */
    public function recoverAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Retrieve the Client
        $client = $this->Model->Clients->get(intval($this->Request->getParams('GET','id')));

        // Check if the Client is accessible
        if(empty($client)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested client."];
        } else {
            if($client['organization']['id'] != $this->Auth->user()->organization()->id){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this client."];
            }
            if(!$this->Auth->isAuthorized("AccountManager", 4)){
                $message = ["status" => 403, "message" => "Forbidden", "data" => "You are not allowed to access this client."];
            }
        }

        // Check if the Note is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "GET"){

                // Update the Client
                $affectedRows = $this->Model->Clients->update($client['id'], ["isArchived" => 0]);

                // Retrieve the Updated Client
                $message["data"]["record"] = $this->Model->Clients->get($client['id']);
            } else {
                $message = ["status" => 400, "message" => "Bad Request", "data" => "Invalid Request Method"];
            }
        }

        return $message;
    }
}
