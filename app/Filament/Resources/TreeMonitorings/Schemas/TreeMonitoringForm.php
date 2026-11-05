<?php

namespace App\Filament\Resources\TreeMonitorings\Schemas;

use App\Enums\TreeCondition;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeMonitoringForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inspection')->columns(2)->schema([
                Select::make('tree_id')->label('Tree')->relationship('tree', 'tree_code')->searchable()->preload()->required(),
                DateTimePicker::make('observed_at')->label('Observed at')->default(now())->maxDate(now())->required()->seconds(false),
                Select::make('condition')->options(TreeCondition::class)->required()->native(false),
                TextInput::make('trunk_diameter_cm')->label('Trunk diameter')->numeric()->minValue(0)->maxValue(99999)->suffix('cm'),
                TextInput::make('estimated_height_m')->label('Estimated height')->numeric()->minValue(0)->maxValue(9999)->suffix('m'),
                Textarea::make('remarks')->rows(4)->required()->columnSpanFull(),
                Textarea::make('public_notes')->label('Public notes')->rows(3)->columnSpanFull(),
            ]),
            Section::make('Inspection photos')->schema([
                FileUpload::make('photo_paths')->label('Photos')->multiple()->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(4096)->maxFiles(6)
                    ->disk('public')->directory('monitoring')->visibility('public')->visibleOn('create'),
            ]),
        ]);
    }
}
