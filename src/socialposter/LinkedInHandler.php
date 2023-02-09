<?php

namespace nightfallstudios\socialmediamanager\socialposter;

use craft\test\Craft;
use League\OAuth2\Client\Provider\LinkedIn;
use League\OAuth2\Client\Token\LinkedInAccessToken;

class LinkedInHandler
{

  public static $linkedInProvider;

  public function setLinkedInProvider()
  {
    self::$linkedInProvider = new LinkedIn([
     'clientId'          => '8642tubl28a1jf',
     'clientSecret'      => 'i1oGX4934ohDrVZt',
     'redirectUri'       => '',
    ]);
  }

  public function loginLinkedIn()
  {
    if (!isset($_GET['code'])) {
      // If we don't have an authorization code then get one
      $options = [
        'scope' => [
          'r_emailaddress',
          'r_ads',
          'rw_ads',
          'r_basicprofile',
          'r_liteprofile',
          'r_ads_reporting',
          'r_organization_social',
          'rw_organization_admin',
          'w_organization_social',
          // 'r_member_social',
          'w_member_social',
          'r_1st_connections_size'
        ] // array or string
      ];

      $authUrl = self::$linkedInProvider->getAuthorizationUrl($options);
      $_SESSION['oauth2state'] = self::$linkedInProvider->getState();
      header('Location: '.$authUrl);
      exit;
      // Check given state against previously stored one to mitigate CSRF attack
    }

    header("/social-platform-setup");
  }


  public function checkLinkedInToken()
  {
    if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

      unset($_SESSION['oauth2state']);
      exit('Invalid state');

    }

    // Try to get an access token (using the authorization code grant)
    $token = self::$linkedInProvider->getAccessToken('authorization_code', [
      'code' => $_GET['code']
    ]);
    $expiry = date('Y-m-d', strtotime("+3 months", strtotime(date('Y-m-d'))));
    $currentUser = Craft::$app->getUser()->getIdentity();

    $currentUser->setFieldValue('linkedinAccessToken', $token->getToken());
    if (Craft::$app->getElements()->saveElement($currentUser)) {
      $currentUser->setFieldValue('linkedinTokenExpiry', $expiry );
      if (Craft::$app->getElements()->saveElement($currentUser)) {
        return true;
      }
    }
    return false;
  }

  public function getLinkedInUsername($tokenCode)
  {
    $token = new LinkedInAccessToken([
      'access_token' => $tokenCode
    ]);

    $fields = [
      'id', 'firstName', 'lastName', 'maidenName',
      'headline', 'vanityName', 'birthDate', 'educations'
    ];

    $member = self::$linkedInProvider->withFields($fields)->getResourceOwner($token);

    try {
      $currentUser = Craft::$app->getUser()->getIdentity();
      $currentUser->setFieldValue('linkedinUsername', $member->getAttribute('vanityName'));
      if (Craft::$app->getElements()->saveElement($currentUser)) {
        $currentUser->setFieldValue('linkedinId', $member->getAttribute('id'));
        if (Craft::$app->getElements()->saveElement($currentUser)) {
          return true;
        }
      }
    } catch (Exception $e) {
      // Failed to get user details
      exit('Oh dear... can\'t save your username');
    }
  }

  public function shareLinkedIn($post)
  {
    $currentUser = Craft::$app->getUser()->getIdentity();

    $postFields = json_encode(
      array("author"=>"urn:li:person:".$currentUser->linkedinId,
        "lifecycleState"=>"PUBLISHED",
        "specificContent"=>array(
          "com.linkedin.ugc.ShareContent"=>array("shareCommentary"=>array("text"=> $post),"shareMediaCategory"=>"NONE")
        ),
        "visibility"=>array(
          "com.linkedin.ugc.MemberNetworkVisibility"=>"PUBLIC"
        )
      )
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.linkedin.com/v2/ugcPosts",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$postFields,
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ". $currentUser->linkedinAccessToken,
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

  }


  public function shareLinkedInAsset($post, $postImageUrl, $postImageName, $postImageType)
  {
    $currentUser = Craft::$app->getUser()->getIdentity();
    $assetUploadData = self::uploadLinkedinAsset($post, $postImageUrl, $postImageName, $postImageType);

    $postFields = json_encode(
      array("author"=>"urn:li:person:".$currentUser->linkedinId,
        "lifecycleState"=>"PUBLISHED",
        "specificContent"=>array(
          "com.linkedin.ugc.ShareContent"=>array(
            "shareCommentary"=>array(
              "text"=> $post
            ),
            "shareMediaCategory"=>"IMAGE",
            "media"=>array(
              array(
                "status"=>"READY",
                "description"=>array(
                  "text" => "Center stage!"
                ),
                "media" => $assetUploadData[0][1],
                "title"=>array(
                  "text"=> "LinkedIn Talent Connect 2018"
                )
              )
            )
          )
        ),
        "visibility"=>array(
          "com.linkedin.ugc.MemberNetworkVisibility"=>"PUBLIC"
        )
      )
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.linkedin.com/v2/ugcPosts",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$postFields,
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ". $currentUser->linkedinAccessToken,
        "Content-Type: application/json"
      ),
    ));

    $serverResponse = curl_exec($curl);

    curl_close($curl);

    return $serverResponse;

  }

  public function uploadLinkedinAsset($post, $postImageUrl, $postImageName, $postImageType)
  {

    $registrationDetails = self::getLinkedInAssetRegistration();
    $currentUser = Craft::$app->getUser()->getIdentity();

    $postImageData = file_get_contents($postImageUrl);

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $registrationDetails[0],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$postImageData,
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ". $currentUser->linkedinAccessToken,
        'x-li-format: json',
        'Content-Type: application/binary'
      ),
    ));
    $serverResponse = curl_exec($curl);
    curl_close($curl);

    return array($registrationDetails,$serverResponse);

  }

  public function getLinkedInAssetRegistration()
  {

    $currentUser = Craft::$app->getUser()->getIdentity();
    $postFields = json_encode(
      array(
        "registerUploadRequest"=> array(
          "recipes"=> array(
            "urn:li:digitalmediaRecipe:feedshare-image"
          ),
          "owner"=> "urn:li:person:".$currentUser->linkedinId,
          "serviceRelationships"=> array(
            array(
              "relationshipType"=>"OWNER",
              "identifier"=>"urn:li:userGeneratedContent"
            )
          )
        )
      )
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.linkedin.com/v2/assets?action=registerUpload",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$postFields,
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ". $currentUser->linkedinAccessToken,
        "Content-Type: application/json"
      ),
    ));

    $serverResponse = curl_exec($curl);

    curl_close($curl);

    $uploadUrl =  "";
    $assetCode = "";
    $serverResponse = json_decode($serverResponse);

    try {
      $uploadUrl =  $serverResponse->value->uploadMechanism;
      $assetCode = $serverResponse->value->asset;
      foreach ($uploadUrl as $name => $value) {
        $uploadUrl = $value;
      }
      $uploadUrl = $uploadUrl->uploadUrl;

    } catch (\Exception $e) {
      echo "error registering image" . $serverResponse;
    }
    $response = array($uploadUrl, $assetCode);

    return $response;

  }

  public function getUserLinkedinCompanies()
  {

    $currentUser = Craft::$app->getUser()->getIdentity();

    return $currentUser->linkedinCompanyIds;

  }

  public function shareLinkedInCompany($post, $pageId)
  {
    $currentUser = Craft::$app->getUser()->getIdentity();

    $postFields = json_encode(
      array("author"=>"urn:li:organization:".$pageId,
        "lifecycleState"=>"PUBLISHED",
        "specificContent"=>array(
          "com.linkedin.ugc.ShareContent"=>array("shareCommentary"=>array("text"=> $post),"shareMediaCategory"=>"NONE")
        ),
        "visibility"=>array(
          "com.linkedin.ugc.MemberNetworkVisibility"=>"PUBLIC"
        )
      )
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.linkedin.com/v2/ugcPosts",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$postFields,
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ". $currentUser->linkedinAccessToken,
        "Content-Type: application/json",
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

  }


}