<?php

use League\OAuth2\Client\Provider\Instagram;

class InstagramHandler
{

  public static $instagramProvider;

  public function setInstagramProvider(){
    self::$instagramProvider = new Instagram([
     'clientId'          => '{instagram-client-id}',
     'clientSecret'      => '{instagram-client-secret}',
     'redirectUri'       => 'https://example.com/callback-url',
     'host'              => 'https://api.instagram.com',  // Optional, defaults to https://api.instagram.com
     'graphHost'         => 'https://graph.instagram.com' // Optional, defaults to https://graph.instagram.com
   ]);
  }

  public function makePost(Post $post): void
  {

  }

}