<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Account';

    protected static ?string $modelLabel = 'Anggota Grup';

    protected static ?string $navigationLabel = 'Kelola Anggota Grup';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

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
                BadgeColumn::make('role')
                    ->enum(['leader' => 'Pemimpin', 'user' => 'Anggota'])
                    ->colors(['danger' => 'leader', 'success' => 'user'])
                    ->searchable()->sortable(),
                TextColumn::make('username')->prefix('Username: ')
                    ->searchable()->sortable(),
                TextColumn::make('created_at')->label('Tgl Pembuatan Akun')
                    ->date()->prefix('Dibuat Pada: ')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->label('Tipe Akun')
                    ->options([
                        'leader' => 'Pemimpin',
                        'user' => 'Anggota'
                    ])
            ])
            ->actions([
                DeleteAction::make('Hapus Akun')->hidden(fn($record) => $record->id == auth()->id() || auth()->user()->role != 'leader'),
                Action::make('edit')
                    ->hidden(auth()->user()->role != 'leader')
                    ->icon('heroicon-o-pencil')
                    ->requiresConfirmation()
                    ->mountUsing(fn (ComponentContainer $form, User $record) => $form->fill([
                        'username' => $record->username,
                    ]))
                    ->action(function (User $record, array $data): void {
                        $record->username = $data['username'];
                        $data['password'] ? $record->password = Hash::make($data['password']) : $record->save();
                        $record->save();
                        Notification::make()->success()->title('Data Pengguna Berhasil Diubah!')->send();
                    })
                    ->form([
                        TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->minLength(8),
                    ]),
            ])
            ->bulkActions([
            ])
            ->headerActions([
                Tables\Actions\Action::make('Tambahkan Anggota')
                    ->hidden(auth()->user()->role != 'leader')
                    ->button()
                    ->requiresConfirmation()
                    ->modalSubheading()
                    ->form([
                        TextInput::make('username')->required()->unique()
                    ])
                    ->action(function($data) {
                        User::create([
                            'username' => $data['username'],
                            'group_id' => auth()->user()->group_id,
                            'password' => Hash::make('password'),
                        ]);

                        return Notification::make()->success()->title('Pengguna Baru Berhasil Ditambahkan!')->send();
                    }),
            ])
            ->contentGrid([
                'md' => 2,
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
