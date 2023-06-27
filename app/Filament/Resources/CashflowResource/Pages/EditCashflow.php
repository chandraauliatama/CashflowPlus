<?php

namespace App\Filament\Resources\CashflowResource\Pages;

use App\Filament\Resources\CashflowResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashflow extends EditRecord
{
    protected static string $resource = CashflowResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
