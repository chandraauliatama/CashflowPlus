<?php

namespace App\Filament\FilamentBreezy;

use Filament\Forms\Components\TextInput;
use JeffGreco13\FilamentBreezy\Pages\MyProfile as BaseProfile;

class MyProfile extends BaseProfile
{
    public $groupName;

    protected function getUpdateProfileFormSchema(): array
    {
        return array_merge([parent::getUpdateProfileFormSchema()[1]->label('Username')], [
            TextInput::make('groupName')->placeholder(auth()->user()->group->name)
                ->disabled(auth()->user()->role !== 'leader')
                ->label('Nama Catatan Keuangan'),
        ]);
    }

    public function updateProfile()
    {
        parent::updateProfile();

        if ($this->userData['groupName'] && auth()->user()->role == 'leader') {
            auth()->user()->group->update([
                'name' => $this->userData['groupName'],
            ]);
        }
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return config('filament-breezy.show_profile_page_in_navbar');
    }
}
