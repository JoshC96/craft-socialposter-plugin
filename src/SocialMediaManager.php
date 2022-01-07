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

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

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

}
