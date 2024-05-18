<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/config/config.php";

class ExolveAPI
{
    private $api_url;
    private $number;
    private $destination;
    private $text;
    private $API_KEY;

    public function __construct($destination, $text)
    {
        $this->api_url = $_ENV["EXOLVE_API_URL"];
        $this->number = $_ENV["EXOLVE_API_NUMBER"];
        $this->API_KEY = $_ENV["EXOLVE_API_KEY"];

        if(str_starts_with(trim($destination), '+')) {
            $destination = substr($destination, 1);
        }
        $this->destination = $destination;
        $this->text = $text;
    }

    public function sendMessage()
    {
        $data = array(
            "number"=>$this->number,
            "destination"=>$this->destination,
            "text"=>$this->text
        );

        $options = array(
            'http'=>array(
                'header'  => "Content-Type: application/json" . PHP_EOL .
                    "Authorization: Bearer {$this->API_KEY}",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );


        $context = stream_context_create($options);
        return file_get_contents($this->api_url, false, $context);
    }
}