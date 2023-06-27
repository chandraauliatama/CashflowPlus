<?php

namespace App\Filament\Resources\CashflowResource\Widgets;

use App\Models\Cashflow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $incomes = Cashflow::where('group_id', auth()->user()->group_id)->where('type', 'income')->sum('amount');
        $expenses = Cashflow::where('group_id', auth()->user()->group_id)->where('type', 'expense')->sum('amount');

        $total = $incomes - $expenses;
        return [
            Card::make('Jumlah Uang', 'Rp. '. number_format($total, 0, ',', '.')),
            Card::make('', 'Rp. '. number_format($incomes, 0, ',', '.'))
                ->description('Pemasukan')
                ->descriptionIcon('heroicon-s-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Card::make('', 'Rp. '. number_format($expenses, 0, ',', '.'))
                ->description('Pengeluaran')
                ->descriptionIcon('heroicon-s-trending-down')
                ->chart([17, 4, 15, 3, 10, 4, 2])
                ->color('danger'),
        ];
    }
}
