<?php

namespace App\Filament\Resources\Trees\Schemas;

use App\Enums\PermissionName;
use App\Enums\TreeCondition;
use App\Enums\TreeStatus;
use App\Enums\VerificationStatus;
use App\Models\Species;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tree identity')
                ->description('The tree ID is assigned automatically when a record is created.')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->schema([
                    TextInput::make('tree_code')->label('Tree ID')->placeholder('Assigned automatically')->disabled()->dehydrated(false),
                    Select::make('species_id')
                        ->label('Species')
                        ->relationship('species', 'scientific_name', modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('common_name'))
                        ->getOptionLabelFromRecordUsing(fn (Species $record): string => "{$record->common_name} ({$record->scientific_name})")
                        ->searchable(['common_name', 'scientific_name', 'family_name'])->preload()->required(),
                    Select::make('campus_area_id')->label('Campus area')->relationship('campusArea', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('name'))->searchable()->preload()->required(),
                    TextInput::make('descriptive_location')->label('Descriptive location')->placeholder('e.g. Behind the Administration Building')->required()->maxLength(255),
                    TextInput::make('nearby_landmark')->label('Nearby landmark')->maxLength(255)->columnSpanFull(),
                ]),
            Section::make('Map coordinates')
                ->description('Use decimal-degree coordinates for the physical tree location.')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->collapsible()
                ->schema([
                    TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90)->step(0.0000001)->placeholder('16.4806960'),
                    TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180)->step(0.0000001)->placeholder('121.1407710'),
                ]),
            Section::make('Measurements and condition')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 3,
                ])
                ->schema([
                    TextInput::make('trunk_diameter_cm')->label('Trunk diameter')->numeric()->minValue(0)->maxValue(99999)->suffix('cm'),
                    TextInput::make('estimated_height_m')->label('Estimated height')->numeric()->minValue(0)->maxValue(9999)->suffix('m'),
                    TextInput::make('estimated_age_years')->label('Estimated age')->integer()->minValue(0)->maxValue(2000)->suffix('years'),
                    DatePicker::make('date_planted')->label('Date planted')->maxDate(now()),
                    Select::make('current_condition')->label('Current condition')->options(TreeCondition::class)->default(TreeCondition::Healthy)->required()->native(false),
                    Select::make('status')->options(TreeStatus::class)->default(TreeStatus::Existing)->required()->native(false),
                ]),
            Section::make('Record notes')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->schema([
                    Textarea::make('public_notes')->label('Public notes')->helperText('May appear on the public tree profile after verification.')->rows(4),
                    Textarea::make('internal_remarks')->label('Internal remarks')->helperText('Visible only to authorized users.')->rows(4),
                    Toggle::make('is_featured')->label('Feature on public homepage')->default(false)->columnSpanFull(),
                    Hidden::make('recorded_by')->default(fn () => auth()->id()),
                ]),
            Section::make('Verification and publication')
                ->description('Only users with verification permission can change these fields.')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->visible(fn (): bool => auth()->user()?->can(PermissionName::ManageVerifications->value) ?? false)
                ->schema([
                    Select::make('verification_status')->label('Verification status')->options(VerificationStatus::class)->default(VerificationStatus::Pending)->required()->native(false),
                    Select::make('verified_by')->label('Verified by')->relationship('verifier', 'name')->searchable()->preload(),
                    DateTimePicker::make('verified_at')->label('Verified at')->seconds(false),
                    DateTimePicker::make('published_at')->label('Publish publicly at')->seconds(false),
                ]),
        ]);
    }
}
