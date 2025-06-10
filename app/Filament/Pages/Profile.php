<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile';
    protected static bool $shouldRegisterNavigation = false;

    public $name;
    public $email;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

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
                Forms\Components\TextInput::make('current_password')
                    ->password()
                    ->label('Current Password')
                    ->required()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('new_password')
                    ->password()
                    ->label('New Password (leave blank to keep current)')
                    ->dehydrated(false),
                Forms\Components\TextInput::make('new_password_confirmation')
                    ->password()
                    ->label('Confirm New Password')
                    ->same('new_password')
                    ->dehydrated(false),
            ]);
    }

    public function submit()
    {
        $data = $this->form->getState();

        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            $this->addError('current_password', 'The provided password is incorrect.');
            return;
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if ($data['new_password']) {
            $updateData['password'] = Hash::make($data['new_password']);
        }

        auth()->user()->update($updateData);

        $this->notify('success', 'Profile updated successfully.');
    }
}
