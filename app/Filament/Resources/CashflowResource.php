<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\CashflowResource\Pages;
use App\Filament\Resources\CashflowResource\RelationManagers;
use App\Models\Cashflow;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashflowResource extends Resource
{
    protected static ?string $model = Cashflow::class;

    protected static ?string $navigationIcon = 'heroicon-o-cash';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $modelLabel = 'Catatan Keuangan';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->where('group_id', auth()->user()->group_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('type')->label('Jenis')
                    ->enum(['expense' => 'Pengeluaran', 'income' => 'Pemasukan'])
                    ->colors(['danger' => 'expense', 'success' => 'income'])
                    ->sortable(),
                TextColumn::make('title')->label('Keterangan')->searchable(),
                TextColumn::make('category.name')->label('Kategori')
                    ->searchable()->searchable(),
                TextColumn::make('amount')->label('Jumlah')->sortable()->prefix('Rp. ')
                    ->getStateUsing(fn($record) =>  number_format($record->amount, 0, ',', '.')),
                TextColumn::make('transaction_time')->label('Waktu Transaksi')
                    ->sortable()->dateTime('Y-m-d H:i'),
                TextColumn::make('user.username'),
                TextColumn::make('updated_at')->label('Terakhir Diubah')
                    ->sortable()->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis Kategori')
                    ->options([
                        'expense' => 'Pengeluaran',
                        'income' => 'Pemasukan'
                    ])
            ])
            ->actions([
                DeleteAction::make('Hapus'),
                Action::make('Edit')
                    ->icon('heroicon-o-pencil')
                    ->requiresConfirmation()
                    ->modalSubheading()
                     ->mountUsing(fn (ComponentContainer $form, Cashflow $record) => $form->fill([
                        'title' => $record->title,
                        'amount' => $record->amount,
                        'jenis' => $record->type,
                        'category_id' => $record->category_id,
                        'transaction_time' =>$record->transaction_time,
                    ]))
                    ->action(function (Cashflow $record, array $data): void {
                        $record->title  = $data['title'];
                        $record->amount = $data['amount'];
                        $record->type   = $data['jenis'];
                        $record->category_id    = $data['category_id'];  
                        $record->transaction_time = $data['transaction_time'];
                        $record->user_id = auth()->id();
                        $record->save();
                        Notification::make()->success()->title('Catatan Berhasil Diubah!')->send();
                    })
                    ->form([
                        TextInput::make('title')->label('Keterangan')->required(),
                        Select::make('jenis')->required()->reactive()
                            ->options([
                                'expense' => 'Pengeluaran',
                                'income' => 'Pemasukan',
                            ]),
                        Select::make('category_id')->label('Kategori')->required()
                            ->options(function($get) {
                                return Category::where('group_id', auth()->user()->group_id)
                                            ->where('type', $get('jenis'))
                                            ->get()->pluck('name', 'id');
                            }),
                        TextInput::make('amount')->label('Jumlah')
                            ->minValue(1)->integer()->required(),
                        DateTimePicker::make('transaction_time')->label('Waktu Transaksi')
                            ->required()->default(now())
                    ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                FilamentExportBulkAction::make('export')
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export'),
                Action::make('Masukan Catatan Baru')
                    ->button()
                    ->requiresConfirmation()
                    ->modalSubheading()
                    ->form([
                        TextInput::make('title')->label('Keterangan')->required(),
                        Select::make('jenis')->required()->reactive()
                            ->options([
                                'expense' => 'Pengeluaran',
                                'income' => 'Pemasukan',
                            ]),
                        Select::make('category_id')->label('Kategori')->required()
                            ->options(function($get) {
                                return Category::where('group_id', auth()->user()->group_id)
                                            ->where('type', $get('jenis'))
                                            ->get()->pluck('name', 'id');
                            }),
                        TextInput::make('amount')->label('Jumlah')
                            ->minValue(1)->integer()->required(),
                        DateTimePicker::make('transaction_time')->label('Waktu Transaksi')
                            ->required()->default(now())
                    ])
                    ->action(function ($data) {
                        Cashflow::create([
                            'title' => $data['title'],
                            'amount' => $data['amount'],
                            'type' => $data['jenis'],
                            'category_id' => $data['category_id'],
                            'transaction_time' => $data['transaction_time'],
                            'group_id' => auth()->user()->group_id,
                            'user_id' => auth()->id(),
                        ]);

                        return Notification::make()->success()->title('Catatan Berhasil Ditambahkan!')->send();
                    }),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashflows::route('/'),
            'create' => Pages\CreateCashflow::route('/create'),
            'edit' => Pages\EditCashflow::route('/{record}/edit'),
        ];
    }    
}
