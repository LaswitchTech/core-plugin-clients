<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Base\BaseEndpoint;

class ClientsEndpoint extends BaseEndpoint {

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Initialize the Endpoint
        $this->init('clients');

        // Set Properties
        $this->required = ['lead','vcard'];
    }

    /**
     * Retrieve a record
     */
    public function fetchAction(): array
    {
        // Call the parent constructor
        $message = parent::fetchAction();

        // Check if the records is accessible
        if($message['status'] == 200){

            // Check if the vCards Plugin is accessible
            if($this->Helper->Core->isInstalled('vcards')){
                $message['data']['record']['vcard'] = $this->Model->Vcards->fetch(intval($message['data']['record']['vcard']['id']));
            }

            // Check if the Tasks Plugin is accessible
            if($this->Helper->Core->isInstalled('tasks')){
                $message['data']['record']['task'] = $this->Model->Tasks->fetch(intval($message['data']['record']['task']['id']));
            }

            // // Check if the Delegations Plugin is accessible
            // if($this->Helper->Core->isInstalled('delegations')){
            //     $message['data']['record']['delegation'] = $this->Model->Delegations->fetch(intval($message['data']['record']['delegation']['id']));
            // }

            // // Check if the Firms Plugin is accessible
            // if($this->Helper->Core->isInstalled('firms')){
            //     $message['data']['record']['firm'] = $this->Model->Firms->fetch(intval($message['data']['record']['firm']['id']));
            // }

            // Check if the Relationship Plugin is accessible
            if($this->Helper->Core->isInstalled('relationship')){
                $message['data']['dependencies']['relationship'] = $this->Model->Relationship->get($this->basename, $message['data']['record']['id']);
                if($this->Helper->Core->isInstalled('vcards') && array_key_exists('vcard', $message['data']['record'])){
                    $message['data']['dependencies']['relationship'] = array_merge(
                        $message['data']['dependencies']['relationship'],
                        $this->Model->Relationship->get('vcards', $message['data']['record']['vcard']['id'])
                    );
                }
            }

            // Check if the Contacts is accessible
            if($this->Helper->Core->isInstalled('contacts')){
                $message['data']['dependencies']['contacts'] = $this->Model->Contacts->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
                if($this->Helper->Core->isInstalled('leads') && !is_null($message['data']['record']['lead']['id'])){
                    $message['data']['dependencies']['contacts'] = array_merge($message['data']['dependencies']['contacts'], $this->Model->Contacts->fetchAll([
                        ["key" => "targetTable", "operator" => "=", "value" => "leads"],
                        ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['lead']['id']],
                        ["key" => "isArchived", "operator" => "<>", "value" => 1],
                    ]));
                }
            }

            // Check if the Documents is accessible
            if($this->Helper->Core->isInstalled('documents')){
                $message['data']['dependencies']['documents'] = $this->Model->Documents->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
                if($this->Helper->Core->isInstalled('leads') && !is_null($message['data']['record']['lead']['id'])){
                    $message['data']['dependencies']['documents'] = array_merge($message['data']['dependencies']['documents'], $this->Model->Documents->fetchAll([
                        ["key" => "targetTable", "operator" => "=", "value" => "leads"],
                        ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['lead']['id']],
                        ["key" => "isArchived", "operator" => "<>", "value" => 1],
                    ]));
                }
            }

            // Check if the Events is accessible
            if($this->Helper->Core->isInstalled('event')){
                $message['data']['dependencies']['event'] = $this->Model->Event->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
            }

            // Check if the Files is accessible
            if($this->Helper->Core->isInstalled('files')){
                $message['data']['dependencies']['files'] = $this->Model->Files->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
            }

            // Check if the Followups is accessible
            if($this->Helper->Core->isInstalled('followups')){
                $message['data']['dependencies']['followups'] = $this->Model->Followups->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
            }

            // Check if the Notes is accessible
            if($this->Helper->Core->isInstalled('notes')){
                $message['data']['dependencies']['notes'] = $this->Model->Notes->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
            }

            // Check if the Services is accessible
            if($this->Helper->Core->isInstalled('services')){
                $message['data']['dependencies']['services'] = $this->Model->Services->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
                if($this->Helper->Core->isInstalled('leads') && !is_null($message['data']['record']['lead']['id'])){
                    $message['data']['dependencies']['services'] = array_merge($message['data']['dependencies']['services'], $this->Model->Services->fetchAll([
                        ["key" => "targetTable", "operator" => "=", "value" => "leads"],
                        ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['lead']['id']],
                        ["key" => "isArchived", "operator" => "<>", "value" => 1],
                    ]));
                }
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Create a record
     */
    public function createAction(): array
    {
        // Call the parent constructor
        $message = parent::createAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Retrieve the parameters
            $parameters = $message['data']['parameters'];

            // Initialize the fields array
            $fields = [];

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Client',
                    'message' => 'New Client Created for <vcard>'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                    'targetTable' => 'clients',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }

            // Check if the Leads Plugin is accessible
            if($this->Helper->Core->isInstalled('leads')){

                // Add the client to the lead
                $this->Model->Leads->update($message['data']['record']['lead']['id'],['client' => $message['data']['record']['id']]);
            }

            // Check if the Tasks Plugin is accessible
            if($this->Helper->Core->isInstalled('tasks')){

                // Initialize the record
                $record = [];

                // Retrieve the client process
                $process = $this->Model->Process->fetchByTable('clients');
                $record['process'] = $process['process'];

                // Complete the task record
                $record['label'] = 'Progress on <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard>';
                $record['category'] = 'Client';
                $record['progress'] = 0;
                $record['scale'] = count($record['process']);
                $record['color'] = 'primary';
                $record['link'] = '/plugin/clients/details?id='.$message['data']['record']['vcard']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']);
                $record['isActive'] = 0;
                $record['targetTable'] = 'clients';
                $record['targetId'] = $message['data']['record']['id'];

                // Create the task
                $fields['task'] = $this->Model->Tasks->create($record);

                // Check if the Event Plugin is accessible
                if($this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Task',
                        'message' => 'New Task Created for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the task
                    $event['link'] = '/plugin/tasks/index?id='.$fields['task'];
                    $event['targetTable'] = 'tasks';
                    $event['targetId'] = $fields['task'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }
            }

            // Check if the Relationship Plugin is accessible
            if($this->Helper->Core->isInstalled('relationship')){

                // Create the relationship with the lead
                $this->Model->Relationship->create(
                    'leads',
                    $message['data']['record']['lead']['id'],
                    $this->basename,
                    $message['data']['record']['id'],
                );
                $this->Model->Relationship->create(
                    $this->basename,
                    $message['data']['record']['id'],
                    'leads',
                    $message['data']['record']['lead']['id'],
                );
            }

            // Check if $fields is empty
            if(!empty($fields)){
                $affectedRows = $this->Model->{$this->name}->update($message['data']['record']['id'], $fields);

                // Check if we send out the notification
                if($affectedRows){

                    // Retrieve the updated record
                    $message['data']['record'] = $this->Model->{$this->name}->fetch($message['data']['record']['id']);
                }
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Update a record
     */
    public function updateAction(): array
    {
        // Call the parent constructor
        $message = parent::updateAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Client',
                    'message' => 'Client Updated for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                    'targetTable' => 'clients',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Delete a record
     */
    public function deleteAction(): array
    {
        // Call the parent constructor
        $message = parent::deleteAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Client',
                    'message' => 'Client Deleted for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                    'targetTable' => 'clients',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }

            // Check if the Leads Plugin is accessible
            if($this->Helper->Core->isInstalled('leads')){

                // Delete the lead
                $affectedRows = $this->Model->Leads->delete($message['data']['record']['lead']['id']);

                // Check if the Event Plugin is accessible
                if($affectedRows && $this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Lead',
                        'message' => 'Lead Deleted for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the lead
                    $event['link'] = '/plugin/leads/index?id='.$message['data']['record']['lead']['id'];
                    $event['targetTable'] = 'leads';
                    $event['targetId'] = $message['data']['record']['lead']['id'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }

                // Check if the Tasks Plugin is accessible
                if($this->Helper->Core->isInstalled('tasks')){

                    // Delete the task
                    $affectedRows = $this->Model->Tasks->delete($message['data']['record']['lead']['task']);

                    // Check if the Event Plugin is accessible
                    if($affectedRows && $this->Helper->Core->isInstalled('event')){

                        // Setup a new event
                        $event = [
                            'category' => 'Task',
                            'message' => 'Task Deleted for <vcard>'.$fields['vcard'].':'.$parameters['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                            'icon' => 'circle',
                            'color' => 'secondary',
                            'link' => '/plugin/leads/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                            'targetTable' => 'leads',
                            'targetId' => $message['data']['record']['id'],
                        ];

                        // Create the event
                        $message['data']['event'][] = $this->Model->Event->create($event);

                        // Setup a new event for the task
                        $event['link'] = '/plugin/tasks/index?id='.$message['data']['record']['lead']['task'];
                        $event['targetTable'] = 'tasks';
                        $event['targetId'] = $message['data']['record']['lead']['task'];

                        // Create the event
                        $message['data']['event'][] = $this->Model->Event->create($event);
                    }
                }
            }

            // Check if the vCards Plugin is accessible
            if($this->Helper->Core->isInstalled('vcards')){

                // Delete the vCard
                $affectedRows = $this->Model->Vcards->delete($message['data']['record']['vcard']['id']);

                // Check if the Event Plugin is accessible
                if($affectedRows && $this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'vCard',
                        'message' => 'vCard Deleted for <vcard>'.$fields['vcard'].':'.$parameters['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }
            }

            // Check if the Tasks Plugin is accessible
            if($this->Helper->Core->isInstalled('tasks')){

                // Delete the task
                $affectedRows = $this->Model->Tasks->delete($message['data']['record']['task']['id']);

                // Check if the Event Plugin is accessible
                if($affectedRows && $this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Task',
                        'message' => 'Task Deleted for <vcard>'.$fields['vcard'].':'.$parameters['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the task
                    $event['link'] = '/plugin/tasks/index?id='.$message['data']['record']['task']['id'];
                    $event['targetTable'] = 'tasks';
                    $event['targetId'] = $message['data']['record']['task']['id'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Archive a record
     */
    public function archiveAction(): array
    {
        // Call the parent constructor
        $message = parent::archiveAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Client',
                    'message' => 'Client Archived for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                    'targetTable' => 'clients',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }

            // Check if the Leads Plugin is accessible
            if($this->Helper->Core->isInstalled('leads')){

                // Archive the lead
                $affectedRows = $this->Model->Leads->archive($message['data']['record']['lead']['id']);

                // Check if the Event Plugin is accessible
                if($affectedRows && $this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Lead',
                        'message' => 'Lead Archived for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the lead
                    $event['link'] = '/plugin/leads/index?id='.$message['data']['record']['lead']['id'];
                    $event['targetTable'] = 'leads';
                    $event['targetId'] = $message['data']['record']['lead']['id'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }

                // Check if the Tasks Plugin is accessible
                if($this->Helper->Core->isInstalled('tasks')){

                    // Archive the task
                    $affectedRows = $this->Model->Tasks->archive($message['data']['record']['lead']['task']);

                    // Check if the Event Plugin is accessible
                    if($affectedRows && $this->Helper->Core->isInstalled('event')){

                        // Setup a new event
                        $event = [
                            'category' => 'Task',
                            'message' => 'Task Archived for <vcard>'.$fields['vcard'].':'.$parameters['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                            'icon' => 'circle',
                            'color' => 'secondary',
                            'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                            'targetTable' => 'clients',
                            'targetId' => $message['data']['record']['id'],
                        ];

                        // Create the event
                        $message['data']['event'][] = $this->Model->Event->create($event);

                        // Setup a new event for the task
                        $event['link'] = '/plugin/tasks/index?id='.$message['data']['record']['lead']['task'];
                        $event['targetTable'] = 'tasks';
                        $event['targetId'] = $message['data']['record']['lead']['task'];

                        // Create the event
                        $message['data']['event'][] = $this->Model->Event->create($event);
                    }
                }
            }

            // Check if the Tasks Plugin is accessible
            if($this->Helper->Core->isInstalled('tasks')){

                // Update the record
                $this->Model->Tasks->archive($message['data']['record']['task']['id']);

                // Check if the Event Plugin is accessible
                if($this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Task',
                        'message' => 'Task Archived for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the task
                    $event['link'] = '/plugin/tasks/index?id='.$message['data']['record']['task']['id'];
                    $event['targetTable'] = 'tasks';
                    $event['targetId'] = $message['data']['record']['task']['id'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Recover a record
     */
    public function recoverAction(): array
    {
        // Call the parent constructor
        $message = parent::recoverAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Client',
                    'message' => 'Client Recovered for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                    'targetTable' => 'clients',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }

            // Check if the Leads Plugin is accessible
            if($this->Helper->Core->isInstalled('leads')){

                // Recover the lead
                $affectedRows = $this->Model->Leads->recover($message['data']['record']['lead']['id']);

                // Check if the Event Plugin is accessible
                if($affectedRows && $this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Lead',
                        'message' => 'Lead Recovered for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($message['data']['record']['vcard']['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the lead
                    $event['link'] = '/plugin/leads/index?id='.$message['data']['record']['lead']['id'];
                    $event['targetTable'] = 'leads';
                    $event['targetId'] = $message['data']['record']['lead']['id'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }

                // Check if the Tasks Plugin is accessible
                if($this->Helper->Core->isInstalled('tasks')){

                    // Recover the task
                    $affectedRows = $this->Model->Tasks->recover($message['data']['record']['lead']['task']);

                    // Check if the Event Plugin is accessible
                    if($affectedRows && $this->Helper->Core->isInstalled('event')){

                        // Setup a new event
                        $event = [
                            'category' => 'Task',
                            'message' => 'Task Recovered for <vcard>'.$fields['vcard'].':'.$parameters['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                            'icon' => 'circle',
                            'color' => 'secondary',
                            'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                            'targetTable' => 'clients',
                            'targetId' => $message['data']['record']['id'],
                        ];

                        // Create the event
                        $message['data']['event'][] = $this->Model->Event->create($event);

                        // Setup a new event for the task
                        $event['link'] = '/plugin/tasks/index?id='.$message['data']['record']['lead']['task'];
                        $event['targetTable'] = 'tasks';
                        $event['targetId'] = $message['data']['record']['lead']['task'];

                        // Create the event
                        $message['data']['event'][] = $this->Model->Event->create($event);
                    }
                }
            }

            // Check if the Tasks Plugin is accessible
            if($this->Helper->Core->isInstalled('tasks')){

                // Update the record
                $this->Model->Tasks->recover($message['data']['record']['task']['id']);

                // Check if the Event Plugin is accessible
                if($this->Helper->Core->isInstalled('event')){

                    // Setup a new event
                    $event = [
                        'category' => 'Task',
                        'message' => 'Task Recovered for <vcard>'.$message['data']['record']['vcard']['id'].':'.$message['data']['record']['vcard']['name'].'</vcard> by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                        'icon' => 'circle',
                        'color' => 'secondary',
                        'link' => '/plugin/clients/details?id='.$message['data']['record']['id'].'&name='.urlencode($parameters['name']),
                        'targetTable' => 'clients',
                        'targetId' => $message['data']['record']['id'],
                    ];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);

                    // Setup a new event for the task
                    $event['link'] = '/plugin/tasks/index?id='.$message['data']['record']['task']['id'];
                    $event['targetTable'] = 'tasks';
                    $event['targetId'] = $message['data']['record']['task']['id'];

                    // Create the event
                    $message['data']['event'][] = $this->Model->Event->create($event);
                }
            }
        }

        // Return the message
        return $message;
    }
}
