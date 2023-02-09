<?php

namespace nightfallstudios\socialmediamanager;


class SocialPoster
{

  private $instagram;
  private $twitter;
  private $linkedin;
  private $facebook;

  public function __construct()
  {
    $this->instagram = new InstagramHandler();
    $this->twitter = new TwitterHandler();
    $this->linkedin = new LinkedInHandler();
    $this->facebook = new FacebookHandler();
  }

  public function platformSetup(): void
  {

  }

  public function post(Post $post): void
  {
    foreach ($post->getPlatform() as $platform) {

      switch ($platform->getName()) {
        case "Instagram":
          $this->instagram->makePost($post);
          break;
        case "Twitter":
          $this->twitter->makeTweet($post);
          break;
        case "LinkedIn":
          $this->linkedin->shareLinkedIn($post);
          break;
        case "Facebook":
          $this->facebook->sendFacebookShare($post, null);
          break;
      }

    }
  }

}
