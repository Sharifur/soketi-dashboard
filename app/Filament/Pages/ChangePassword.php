<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePassword extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.pages.change-password';
    protected static bool $shouldRegisterNavigation = false;

    // Add these properties
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('current_password')
                    ->password()
                    ->label('Current Password')
                    ->required(),
                Forms\Components\TextInput::make('new_password')
                    ->password()
                    ->label('New Password')
                    ->required()
                    ->minLength(8)
                    ->different('current_password'),
                Forms\Components\TextInput::make('new_password_confirmation')
                    ->password()
                    ->label('Confirm New Password')
                    ->required()
                    ->same('new_password'),
            ]);
    }

    protected function getFormModel(): ?string
    {
        return null;
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            $this->addError('current_password', 'The provided password is incorrect.');
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($data['new_password'])
        ]);

        Notification::make()
            ->success()
            ->title('Password changed successfully.')
            ->send();

        // Reset form
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
    }
}
