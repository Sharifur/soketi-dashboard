<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProfileSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile-settings';
    protected static bool $shouldRegisterNavigation = false;

    public $name;
    public $email;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->default($this->name),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', ignorable: auth()->user())
                    ->default($this->email),
            ]);
    }

    public function submit()
    {
        $data = $this->form->getState();

        auth()->user()->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        Notification::make()
            ->success()
            ->title('Profile updated successfully.')
            ->send();
    }
}
