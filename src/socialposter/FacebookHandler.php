<?php

namespace nightfallstudios\socialmediamanager\socialposter;

use Facebook\Facebook;

class FacebookHandler
{
  public static $facebookProvider;

  public function setFacebookProvider()
  {
    self::$facebookProvider = new Facebook([
      'app_id' => '403090990601881',
      'app_secret' => 'd5e05f03a9b1aaa358cc05f18b2d063c',
      'default_graph_version' => 'v2.10'
    ]);
  }


  public function saveFacebookToken($accessToken, $tokenTimeoutDate,$facebookUserId)
  {
    $myUser = Craft::$app->getUser()->getIdentity();
    $myUser->setFieldValue('facebookUserId', $facebookUserId);

    if (Craft::$app->getElements()->saveElement($myUser)) {
      $myUser->setFieldValue('facebookTokenTimeout', $tokenTimeoutDate);
      if (Craft::$app->getElements()->saveElement($myUser)) {
        $myUser->setFieldValue('facebookAccessToken', $accessToken);
        if (Craft::$app->getElements()->saveElement($myUser)) {
          return true;
        }
      }
    }

  }

  public function getUserFacebookPages()
  {
    $currentUser = Craft::$app->getUser()->getIdentity();
    try {
      $response = self::$facebookProvider->get(
        '/'.$currentUser->facebookUserId.'/accounts',
        $currentUser->facebookAccessToken
      );
    } catch(FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $graphNode = $response->getGraphEdge();
    return $graphNode;
  }

  public function getPagePicture($pageId)
  {
    $currentUser = Craft::$app->getUser()->getIdentity();

    try {
      $response = self::$facebookProvider->get(
        '/'.$pageId.'/picture?redirect=0',
        $currentUser->facebookAccessToken
      );
    } catch(FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $graphNode = $response->getGraphNode();
    return $graphNode;
  }

  public function sendFacebookShare($postText, $pageId)
  {
    $currentUser = Craft::$app->getUser()->getIdentity();
    $pages = $currentUser->facebookPageIds;

    try {
      $pagePostRes = self::$facebookProvider->post(
        '/' . $pageId . '/feed',
        $currentUser->facebookAccessToken
      );
    } catch(FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $response = $pagePostRes->getGraphNode();

    return $response;
  }

}