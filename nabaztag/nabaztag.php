#!/opt/local/bin/php
<?php

/**
 * Nabaztag class, contains all Nabaztag interactions
 * 
 * @author Stefan Koopmanschap <left@leftontheweb.com>
 * @license MIT License
 *
 */
class Nabaztag {
  
  protected $token = '';
  protected $serial = '';
  protected $base_url = 'http://api.nabaztag.com/vl/FR/api.jsp';
  
  protected $args;
  protected $client;
  
  protected $actions = array('tts', 'ear', 'stream', 'getVoices', 'getFriends');
  
  /**
   * Constructor. Sets up basic object properties
   *
   */
  public function __construct()
  {
    $this->client = curl_init();
  }
  
  /**
   * Main method. Accepts the commandline parameters as argument
   *
   * @param array $args
   */
  public function run($args)
  {
    $this->args = $args;
    $action = $this->args[1];
    if (in_array($action, $this->actions))
    {
      $result = $this->$action();
    }
    else
    {
      echo "Action not supported\n";
    }
    
  }
  
  /**
   * Internal method to trigger the Text To Speech (TTS) functionality
   *
   */
  protected function tts()
  {
    if (isset($this->args[3]))
    {
      $voice = $this->args[3];
    }
    else
    {
      $voice = 'UK-Leonard';
    }
    $url = $this->getBaseUrl() . '&tts=' . rawurlencode($this->args[2]) . '&voice=' . $voice . '&speed=1&pitch=1';
    $result = $this->send($url);
    $this->echoResult($result);
  }
  
  /**
   * Internal method to trigger the ear movement
   *
   */
  protected function ear()
  {
    if (!isset($this->args[2])) {
      echo "What should the position of the right ear be? [0-16]\n";
      $posright = $this->read();
    }
    else
    {
      $posright = $this->args[2];
    }
    
    if (!isset($this->args[3])) {
      echo "What should the position of the left ear be? [0-16]\n";
      $posleft = $this->read();
    }
    else
    {
      $posleft = $this->args[3];
    }
    $url = $this->getBaseUrl() . '&posright=' . rawurlencode($posright) . '&posleft=' . rawurlencode($posleft);
    $result = $this->send($url);
    $this->echoResult($result);
  }
  
  /**
   * Internal method to trigger the streaming of audio
   *
   */
  protected function stream()
  {
    
  }
  
  /**
   * Get and print out a list of available voices
   *
   */
  protected function getVoices()
  {
    $url = $this->getBaseUrl() . '&action=9';
    $request = $this->send($url);
    
    $voices = simplexml_load_string($request->getBody());
    foreach($voices->voice as $voice) 
    {
      $attributes = $voice->attributes();
      echo $attributes['command'] . ' (' . $attributes['lang'] . ")\n";
    }
    
  }

  /**
   * Get and print out a list of available friends
   *
   */
  protected function getFriends()
  {
    $url = $this->getBaseUrl() . '&action=2';
    $request = $this->send($url);
    
    $friends = simplexml_load_string($request->getBody());
    foreach($friends->friend as $friend)
    {
      $attributes = $friend->attributes();
      echo $attributes['name'] . "\n";
    }
    $attributes = $friends->listfriend->attributes();
    echo $attributes['nb'] . ' friend(s)' . "\n";
  }
  
  /**
   * Send the request to the Nabaztag API
   *
   * @param string $url
   * @return Zend_Http_Response
   */
  protected function send($url)
  {
    echo "Requesting " . $url . "\n";
    curl_setopt($this->client, CURLOPT_URL, $url);
		curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($this->client);
  }
  
  /**
   * Get the base URL to extend for individual requests
   *
   * @return string
   */
  protected function getBaseUrl()
  {
    $url = $this->base_url . 
            '?sn=' . rawurlencode($this->serial) . 
            '&token=' . rawurlencode($this->token);
    return $url;
  }
  
  /**
   * Echo a standard response from the Nabaztag
   *
   * @param Zend_Http_Response $result
   */
  protected function echoResult($result)
  {
      $xml = simplexml_load_string($result);
      echo $xml->message . ': ' . $xml->comment . "\n";
  }
  
  protected function read()
  {
    $fp = fopen("/dev/stdin", "r");
    $input = fgets($fp, 1024);
    fclose($fp);
    return $input; 
  }
  
}

$tag = new Nabaztag();
$tag->run($argv);