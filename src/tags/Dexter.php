<?php

declare(strict_types=1);

namespace boldminded\dexter\tags;

use boldminded\dexter\services\SearchFactory;
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
        $provider = SearchFactory::create();

        $index = $params['index'] ?? null;

        if (!$index) {
            throw new \Exception('Must specify an index to search');
        }

        $term = $params['term'] ?? '';
        $filter = $params['filter'] ?? [];
        $perPage = $params['perPage'] ?? 50;
        $idsOnly = $params['idsOnly'] ?? false;

        $results = $provider->search($index, $term, $filter, $perPage);

        if ($idsOnly) {
            return array_column($results, 'uid');
        }

        return $results;
    }
}
