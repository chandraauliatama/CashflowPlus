<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\ComponentContainer;
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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-grid-add';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $modelLabel = 'Kategori';

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
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('type')
                    ->enum(['expense' => 'Pengeluaran', 'income' => 'Pemasukan'])
                    ->colors(['danger' => 'expense', 'success' => 'income'])
                    ->sortable(),
                TextColumn::make('name')->label('Nama Kategori')->prefix('Nama : ')
                    ->searchable()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis Kategori')
                    ->options([
                        'expense' => 'Pengeluaran',
                        'income' => 'Pemasukan'
                    ])
            ])
            ->actions([
                DeleteAction::make('Hapus')->hidden(fn () => auth()->user()->role != 'leader'),
                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->requiresConfirmation()
                    ->mountUsing(fn (ComponentContainer $form, Category $record) => $form->fill([
                        'name' => $record->name,
                    ]))
                    ->action(function (Category $record, array $data): void {
                        $record->name = $data['name'];
                        $record->save();
                        Notification::make()->success()->title('Kategori Berhasil Diubah!')->send();
                    })
                    ->form([
                        TextInput::make('name')
                            ->required()
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Action::make('Tambahkan Kategori')
                    ->button()
                    ->requiresConfirmation()
                    ->modalSubheading()
                    ->form([
                        TextInput::make('name')->label('Nama Kategori')->required(),
                        Select::make('jenis')->required()
                            ->options([
                                'expense' => 'Pengeluaran',
                                'income' => 'Pemasukan',
                            ]),
                    ])
                    ->action(function ($data) {
                        Category::create([
                            'name' => $data['name'],
                            'group_id' => auth()->user()->group_id,
                            'type' => $data['jenis'],
                        ]);

                        return Notification::make()->success()->title('Pengguna Baru Berhasil Ditambahkan!')->send();
                    }),
            ])
             ->contentGrid([
                'xl' => 1
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
