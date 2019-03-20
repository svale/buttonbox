<?php
/**
 * Button Box plugin for Craft CMS 3.x
 *
 * Button Box
 *
 * @link      http://supercooldesign.co.uk
 * @copyright Copyright (c) 2017 Supercool
 */

namespace supercool\buttonbox;

use supercool\buttonbox\fields\Stars as StarsField;
use supercool\buttonbox\fields\Colours as ColoursField;
use supercool\buttonbox\fields\TextSize as TextSizeField;
use supercool\buttonbox\fields\Buttons as ButtonsField;
use supercool\buttonbox\fields\Width as WidthField;
use supercool\buttonbox\fields\Triggers as TriggersField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use markhuot\CraftQL\CraftQL;
use markhuot\CraftQL\Events\GetFieldSchema;

use yii\base\Event;

/**
 *
 * @author    Supercool
 * @package   ButtonBox
 * @since     1.0.0
 *
 */

class ButtonBox extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * ButtonBox::$plugin
     *
     * @var ButtonBox
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * ButtonBox::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our fields
        Event::on(
            Fields::className(),
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = StarsField::class;
                $event->types[] = ColoursField::class;
                $event->types[] = TextSizeField::class;
                $event->types[] = ButtonsField::class;
                $event->types[] = WidthField::class;
                $event->types[] = TriggersField::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::className(),
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        // Register with CraftQL
        if (class_exists(CraftQL::class)) {
            Event::on(ColoursField::class, 'craftQlGetFieldSchema', [$this, 'handleGetCraftQLSchema']);
            Event::on(TextSizeField::class, 'craftQlGetFieldSchema', [$this, 'handleGetCraftQLSchema']);
            Event::on(ButtonsField::class, 'craftQlGetFieldSchema', [$this, 'handleGetCraftQLSchema']);
            Event::on(WidthField::class, 'craftQlGetFieldSchema', [$this, 'handleGetCraftQLSchema']);
            Event::on(TriggersField::class, 'craftQlGetFieldSchema', [$this, 'handleGetCraftQLSchema']); // not really sensible to query, but for the sake of completnes
        }

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(Craft::t('buttonbox', '{name} plugin loaded', ['name' => $this->name]), __METHOD__);
    }

    /**
     *  Adds the fieldtype to the craftQL Schema
     *
     * @param GetFieldSchema $event
     */
    public function handleGetCraftQLSchema(GetFieldSchema $event)
    {
        $event->handled = true;
        $field = $event->sender;

        $event->schema
            ->addStringField($field)
            ->resolve(function ($root) use ($field) {
                return $root->{$field->handle}->value;
            });
    }

    // Protected Methods
    // =========================================================================

}
