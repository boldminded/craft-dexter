<?php

declare(strict_types=1);

namespace boldminded\dexter\tags;

use boldminded\dexter\services\MultiSearch;
use boldminded\dexter\services\Search;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

class Dexter
{
    public function subscribe()
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('dexter', Dexter::class);
            }
        );
    }

    public function search(array $params = [])
    {
        return (new Search)($params);
    }

    public function multiSearch(array $params = [])
    {
        return (new MultiSearch)($params);
    }
}
