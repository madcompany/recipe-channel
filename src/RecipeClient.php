<?php
namespace Recipe;

use GuzzleHttp\Client;

class RecipeClient {

    protected $client;

    public function __construct($option)
    {
        $this->client = new Client($option);
    }

    public function createClient($config)
    {

        return $this->client;
    }
}