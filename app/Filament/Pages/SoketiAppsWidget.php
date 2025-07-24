<?php

namespace App\Filament\Pages;

use Filament\Widgets\Widget;

class SoketiAppsWidget extends Widget
{
    protected static string $view = 'filament.widgets.soketi-apps';
    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        $page = $this->getPage();
        return [
            'apps' => $page->getApps(),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
}
