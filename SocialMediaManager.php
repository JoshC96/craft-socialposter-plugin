<?php
/**
 * Social Media Manager plugin for Craft CMS 3.x
 *
 * Manage user's social media accounts. Post and get statuses and see stats
 *
 * @link      https://www.nightfallstudios.com.au/
 * @copyright Copyright (c) 2020 Nightfall Studios
 */

namespace nightfallstudios\socialmediamanager;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\elements\User;
use League\OAuth2\Client\Provider\LinkedIn;

use yii\base\Event;

/**
 * Class SocialMediaManager
 *
 * @author    Nightfall Studios
 * @package   SocialMediaManager
 * @since     1.0.0
 *
 */
class SocialMediaManager extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var SocialMediaManager
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.0';
    public $hasCpSettings = false;
    public $hasCpSection = false;
    public $consumerKey = "1yS3NCvQqkGtngOubDnkdtYfe";
    public $consumerSecret = "cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP";
    public $accessToken = "1144563129646366720-9Ivt5Ybp8DgdD7lUQEWVVGe1vcj1Pe";
    public $accessTokenSecret = "2mus8SpaB5POiNmdrEiCEOET5ahIcGDEbHFZyEcdF3muK";


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        $linkedInProvider = new LinkedIn([
            'clientId'          => '8642tubl28a1jf',
            'clientSecret'      => 'i1oGX4934ohDrVZt',
            'redirectUri'       => 'https://www.nightfallstudios.com.au/confirm-linkedin-login',
        ]);

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('socialmediamanager', $this);
            }
        );


        // Event::on(
        //   UrlManager::class,
        //   UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //   function (RegisterUrlRulesEvent $event) {
        //       $event->rules["GET twitter/getTweets/<uid:[^/]+>/<count:[^/]+>"] = 'social-media-manager/twitter/get-tweets';
        //     }
        // );

        Craft::info(
            Craft::t(
                'social-media-manager',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================


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


    public function makeTweet($tweetString, $userAccessToken, $userAccessTokenSecret){

      $consumer_key = '1yS3NCvQqkGtngOubDnkdtYfe'; // Enter your consumer key.
      $consumer_secret = 'cftpIsvaOYavPiAjonP5Z9Q9S2i0VsYbuyIwBPLlbBqmucDeHP'; // Enter your consumer secret.
      $access_token = $userAccessToken; // Enter your access token.
      $access_token_secret = $userAccessTokenSecret; // Enter your access token secret.
      $oauthSignatureMethod = "HMAC-SHA1";
      $oauthTimestamp = time();
      $nonce = md5(mt_rand());
      $oauthVersion = "1.0";
      $tweet = rawurlencode($tweetString);
      $requestTokenUrl = "https://api.twitter.com/1.1/statuses/update.json?status=".$tweet;

      $paramString = "include_entities=true"
                    . "&oauth_consumer_key=" . rawurlencode($consumer_key)
                    . "&oauth_nonce=" . rawurlencode($nonce)
                    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
                    . "&oauth_timestamp=" . $oauthTimestamp
                    . "&oauth_version=" . rawurlencode($oauthVersion)
                    . "&status=". $tweet;

      $sigBase = "POST&" . rawurlencode("https://api.twitter.com/1.1/statuses/update.json") . "&" . rawurlencode($paramString);

      $sigKey = $consumer_secret . "&" . $access_token_secret;
      $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

      $curl = curl_init();

      $authString ='Authorization: OAuth oauth_consumer_key="'.$consumer_key.'",oauth_token="'.$access_token.'",oauth_signature_method="HMAC-SHA1",oauth_timestamp="'.$oauthTimestamp.'",oauth_nonce="'.$nonce.'",oauth_version="1.0",oauth_signature="'.$oauthSig.'"';

      curl_setopt_array($curl, array(
        CURLOPT_URL => $requestTokenUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
          $authString
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      return $response;

    }


    public function getTweets($username, $count){

    }







}
