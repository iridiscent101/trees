<?php

namespace App\Filament\Resources\TreeSurveyPoints\Schemas;

use App\Models\CampusArea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeSurveyPointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Imported survey information')->compact()->columns(3)->schema([
                TextInput::make('source_file')->label('Source file')->disabled()->dehydrated(false),
                TextInput::make('source_row')->label('Source row')->disabled()->dehydrated(false),
                TextInput::make('raw_species_name')->label('Recorded species name')->maxLength(255),
                TextInput::make('height_m')->label('Recorded height')->numeric()->minValue(0)->suffix('m'),
                Select::make('coordinate_status')->label('Coordinate status')->options([
                    'valid' => 'Valid',
                    'outlier' => 'Outlier',
                    'invalid' => 'Invalid',
                ])->required()->native(false),
            ]),
            Section::make('Coordinates')->compact()->description('Correct these only when the original survey coordinates have been confirmed.')->columns(2)->schema([
                TextInput::make('easting')->numeric()->step(0.001),
                TextInput::make('northing')->numeric()->step(0.001),
                TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90)->step(0.000000001),
                TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180)->step(0.000000001),
            ]),
            Section::make('Review assignment')->compact()->columns(2)->schema([
                Select::make('campus_area_id')
                    ->label('Campus area')
                    ->options(fn (): array => CampusArea::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->preload()->native(false),
                TextInput::make('approved_species_name')
                    ->label('Approved species name')
                    ->helperText('Enter one cleaned inventory name; do not retain combined labels.')
                    ->maxLength(255),
                Textarea::make('review_notes')->label('Reviewer notes / rejection reason')->rows(3)->columnSpanFull()
                    ->helperText('Use the review actions to change the workflow status.'),
            ]),
        ]);
    }
}
