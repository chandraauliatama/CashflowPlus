<?php

namespace App\Filament\FilamentBreezy;

use App\Models\Group;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use JeffGreco13\FilamentBreezy\FilamentBreezy;
use JeffGreco13\FilamentBreezy\Http\Livewire\Auth\Register as FilamentBreezyRegister;

class Register extends FilamentBreezyRegister
{
    // Define the new attributes
    public $team_name;

    public $username;

    public function messages(): array
    {
        return [
            'username.unique' => 'Username ini telah digunakan',
            'password.min' => 'Kata sandi anda terlalu pendek',
            'password' => 'Kata sandi anda terlalu lemah',
            'password_confirm' => 'Kata sandi anda tidak cocok',
        ];
    }

    // Override the getFormSchema method and merge the default fields then add your own.
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('team_name')
                ->label('Nama Catatan Keuangan')
                ->required(),
            TextInput::make('username')
                ->unique(table: config('filament-breezy.user_model'))
                ->label('Username')
                ->required(),
            TextInput::make('password')
                ->label(__('Kata Sandi'))
                ->required()
                ->password()
                ->rules(app(FilamentBreezy::class)->getPasswordRules()),
            TextInput::make('password_confirm')
                ->label(__('Konfirmasi Kata Sandi'))
                ->required()
                ->password()
                ->same('password'),
        ];
    }

    // Use this method to modify the preparedData before the register() method is called.
    protected function prepareModelData($data): array
    {
        $group = Group::create(['name' => $data['team_name']]);
        $preparedData = [
            'username' => $data['username'],
            'role' => 'leader',
            'group_id' => $group->id,
            'password' => Hash::make($data['password']),
        ];

        return $preparedData;
    }
}
