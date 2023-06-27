<?php

namespace App\Filament\Resources\CashflowResource\Pages;

use App\Filament\Resources\CashflowResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashflows extends ListRecords
{
    protected static string $resource = CashflowResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
