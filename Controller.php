<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Objects;
use \LaswitchTech\Core\Abstracts\Controller;

class ClientsController extends Controller {

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

        // Set Properties
        switch($namespace){
            case "/clients/logo":
                $this->Public = true;
                $this->Level = 0;
                break;
        }
    }

    /**
     * Fetch an Client's Logo
     *
     * @return mixed
     */
    public function logoAction(): array
    {
        // Import Global Variables
        global $CONFIG;

        // Retrieve the parameters
        $id = $this->Request->getParams('GET', 'id') ?? null;
        $size = $this->Request->getParams('GET', 'size') ?? 128;

        // Retrieve the client
        $client = $this->Model->Clients->fetch($id);

        // Check if user was retrieved
        if(!empty($client)){

            // Retrieve the vcard
            $client['vcard'] = $this->Model->Vcards->fetch($client['vcard']['id']);

            // Check if the client has an avatar
            if($client['vcard']['avatar']['uuid']){

                // Retrieve the file content
                $client['vcard']['avatar']['content'] = $this->Helper->Files->get($client['vcard']['avatar']['path'] . DIRECTORY_SEPARATOR . $client['vcard']['avatar']['uuid']);

                // Return the file
                return $client['vcard']['avatar'];
            }

            // Check if the client has a website
            if($client['vcard']['website']){
                $content = $this->Helper->Favicon->content($client['vcard']['website']);
                $logo = [
                    'type' => $this->Helper->Favicon->mimeType($content),
                    'content' => $content
                ];
                // Convert the logo to png format
                $logo = $this->Helper->Favicon->convert($logo, 'png', $size, $size);
                return $logo;
            }
        }

        // Create the default logo from the img folder
        $logo = [
            'type' => mime_content_type($CONFIG->root() . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.png'),
            'content' => file_get_contents($CONFIG->root() . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.png')
        ];

        // Return the default logo
        return $logo;
    }
}
