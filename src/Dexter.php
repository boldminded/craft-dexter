<?php

namespace boldminded\dexter;

use boldminded\dexter\events\CategoryDelete;
use boldminded\dexter\events\CategorySave;
use boldminded\dexter\events\EntryDelete;
use boldminded\dexter\events\FileDelete;
use boldminded\dexter\events\FileSave;
use boldminded\dexter\events\EntrySave;
use boldminded\dexter\events\Navigation;
use boldminded\dexter\events\UserDelete;
use boldminded\dexter\events\UserSave;
use boldminded\dexter\tags\Dexter as DexterTag;
use Craft;
use craft\base\Plugin;

/**
 * @author BoldMinded <https://boldminded.com/support>
 * @copyright BoldMinded
 */
class Dexter extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;
    public bool $hasCpSection = true;

    public static function config(): array
    {
        return [
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        Craft::$app->onInit(function() {
        });
    }

    private function attachEventHandlers(): void
    {
        (new Navigation)->subscribe();
        (new DexterTag)->subscribe();
        (new CategorySave)->subscribe();
        (new CategoryDelete)->subscribe();
        (new EntrySave)->subscribe();
        (new EntryDelete)->subscribe();
        (new FileSave)->subscribe();
        (new FileDelete)->subscribe();
        (new UserSave)->subscribe();
        (new UserDelete)->subscribe();
    }

    public function getCpNavItem(): ?array
    {
        $nav = parent::getCpNavItem();
        $nav['label'] = Craft::t('dexter', 'Dexter');

        return $nav;
    }
}
