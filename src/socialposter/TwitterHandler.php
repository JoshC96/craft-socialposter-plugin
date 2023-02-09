<?php

namespace nightfallstudios\socialmediamanager\socialposter;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterHandler
{
  public static $twitterProvider;

  public $consumerKey = "1yS3NCvQqkGtngOubDnkdtYfe";
  public $consumerSecret = "cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP";
  public $accessToken = "1144563129646366720-9Ivt5Ybp8DgdD7lUQEWVVGe1vcj1Pe";
  public $accessTokenSecret = "2mus8SpaB5POiNmdrEiCEOET5ahIcGDEbHFZyEcdF3muK";

  public function setTwitterProvider(){
    self::$twitterProvider = new TwitterOAuth(
      $this->consumerKey,
      $this->consumerSecret,
      $this->accessToken,
      $this->accessTokenSecret
    );
  }

  public function loginTwitter(){

    // request token
    $requestTokenResponse = $this->twitterRequestToken();
    // redirect to allow user to authenticate
    $this->twitterUserAllowAccess($requestTokenResponse);

  }

  public function twitterAccessToken(){
    $consumerKey = "1yS3NCvQqkGtngOubDnkdtYfe";
    $consumerSecret = "cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP";
    $accessToken = "1144563129646366720-9Ivt5Ybp8DgdD7lUQEWVVGe1vcj1Pe";
    $accessTokenSecret = "2mus8SpaB5POiNmdrEiCEOET5ahIcGDEbHFZyEcdF3muK";
    $oauthVersion = "1.0";
    $oauthSignatureMethod = "HMAC-SHA1";
    $accessTokenUrl = "https://api.twitter.com/oauth/access_token";
    $nonce = md5(mt_rand());
    $oauthTimestamp = time();
    $oauthVerifier = $_GET["oauth_verifier"];

    $sigBase = "GET&" . rawurlencode($accessTokenUrl) . "&"
      . rawurlencode("oauth_consumer_key=" . rawurlencode($consumerKey)
                     . "&oauth_nonce=" . rawurlencode($nonce)
                     . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
                     . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
                     . "&oauth_token=" . rawurlencode($_SESSION["requestToken"])
                     . "&oauth_verifier=" . rawurlencode($oauthVerifier)
                     . "&oauth_version=" . rawurlencode($oauthVersion));
    $sigKey = $consumerSecret . "&";
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    $requestUrl = $accessTokenUrl . "?"
      . "oauth_consumer_key=" . rawurlencode($consumerKey)
      . "&oauth_nonce=" . rawurlencode($nonce)
      . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
      . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
      . "&oauth_token=" . rawurlencode($_SESSION["requestToken"])
      . "&oauth_verifier=" . rawurlencode($oauthVerifier)
      . "&oauth_version=". rawurlencode($oauthVersion)
      . "&oauth_signature=" . rawurlencode($oauthSig);

    $response = file_get_contents($requestUrl);
    parse_str($response, $values);

    $_SESSION["accessToken"] = $values["oauth_token"];
    $_SESSION["accessTokenSecret"] = $values["oauth_token_secret"];


    return $response;
  }

  public function twitterUserAllowAccess($requestTokenResponse){
    parse_str($requestTokenResponse, $values);
    $_SESSION["requestToken"] = $values["oauth_token"];
    $_SESSION["requestTokenSecret"] = $values["oauth_token_secret"];
    $authorizeUrl = "https://api.twitter.com/oauth/authorize";

    $redirectUrl = $authorizeUrl . "?oauth_token=" . $_SESSION["requestToken"];
    header("Location: " . $redirectUrl);

  }

  public function twitterRequestToken(){

    $consumerKey = "1yS3NCvQqkGtngOubDnkdtYfe";
    $consumerSecret = "cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP";
    $accessToken = "1144563129646366720-9Ivt5Ybp8DgdD7lUQEWVVGe1vcj1Pe";
    $accessTokenSecret = "2mus8SpaB5POiNmdrEiCEOET5ahIcGDEbHFZyEcdF3muK";

    $requestTokenUrl = "https://api.twitter.com/oauth/request_token";
    $oauthTimestamp = time();
    $nonce = md5(mt_rand());
    $oauthSignatureMethod = "HMAC-SHA1";
    $oauthVersion = "1.0";

    $sigBase = "GET&" . rawurlencode($requestTokenUrl) . "&"
      . rawurlencode("oauth_consumer_key=" . rawurlencode($consumerKey)
                     . "&oauth_nonce=" . rawurlencode($nonce)
                     . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
                     . "&oauth_timestamp=" . $oauthTimestamp
                     . "&oauth_version=" . $oauthVersion);
    $sigKey = $consumerSecret . "&";
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    $requestUrl = $requestTokenUrl . "?"
      . "oauth_consumer_key=" . rawurlencode($consumerKey)
      . "&oauth_nonce=" . rawurlencode($nonce)
      . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
      . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
      . "&oauth_version=" . rawurlencode($oauthVersion)
      . "&oauth_signature=" . rawurlencode($oauthSig);

    $response = file_get_contents($requestUrl);

    return $response;
  }

  public function saveTwitterDetails($twitterId, $twitterUsername){
    $myUser = Craft::$app->getUser()->getIdentity();
    $myUser->setFieldValue('twitterUserId', $twitterId);

    if (Craft::$app->getElements()->saveElement($myUser)) {
      $myUser->setFieldValue('twitterUsername', $twitterUsername);
      if (Craft::$app->getElements()->saveElement($myUser)) {
        $myUser->setFieldValue('twitterAccessToken', $_SESSION["accessToken"]);
        if (Craft::$app->getElements()->saveElement($myUser)) {
          $myUser->setFieldValue('twitterAccessSecret', $_SESSION["accessTokenSecret"]);
          if (Craft::$app->getElements()->saveElement($myUser)) {
            return true;
          }
        }
      }
    }

  }

  public function checkTwitterToken(){

    $accessDetails = $this->twitterAccessToken();
    parse_str($accessDetails, $parsedDetails);

    $savedDetails = $this->saveTwitterDetails($parsedDetails['user_id'], $parsedDetails['screen_name']);

    return $savedDetails;
  }


  public function makeTweet($tweetString){

    $currentUser = Craft::$app->getUser()->getIdentity();
    $connection = new TwitterOAuth("1yS3NCvQqkGtngOubDnkdtYfe", "cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP", $currentUser->twitterAccessToken, $currentUser->twitterAccessSecret);

    return $connection->post("statuses/update", ["status" => $tweetString]);;
  }

  public function makeMediaTweet($tweetString, $assets){
    $currentUser = Craft::$app->getUser()->getIdentity();
    $connection = new TwitterOAuth("1yS3NCvQqkGtngOubDnkdtYfe", "cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP", $currentUser->twitterAccessToken, $currentUser->twitterAccessSecret);

    $mediaIds = [];
    $mediaIdArr = [];

    // CAREFUL OF ASSET FILE PATH - IT MAY BE ASSET LOCATION ON CRAFT
    foreach ($assets as $value){
      $mediaIds[] = $connection->upload('media/upload', ['media' => "/home/nightfa1/craft/web/assets/".strval($value->filename)]);
    }

    foreach ($mediaIds as $value){
      $mediaIdArr[] = $value->media_id_string;
    }

    $parameters = [
      'status' => strval($tweetString),
      'media_ids' => implode(',', $mediaIdArr)
    ];

    $result = $connection->post('statuses/update', $parameters);

    return $result;
  }
}