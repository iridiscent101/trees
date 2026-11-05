<?php

namespace App\Filament\Resources\Trees\Schemas;

use App\Models\Tree;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tree identity')->columns(3)->schema([
                TextEntry::make('tree_code')->label('Tree ID')->badge()->copyable(),
                TextEntry::make('species.common_name')->label('Common name')->weight('bold'),
                TextEntry::make('species.scientific_name')->label('Scientific name')->extraAttributes(['class' => 'italic']),
                TextEntry::make('species.family_name')->label('Family'),
                TextEntry::make('campusArea.name')->label('Campus area'),
                TextEntry::make('descriptive_location')->label('Location'),
                TextEntry::make('nearby_landmark')->label('Nearby landmark')->placeholder('Not recorded'),
            ]),
            Section::make('Condition and measurements')->columns(4)->schema([
                TextEntry::make('current_condition')->label('Condition')->badge(),
                TextEntry::make('status')->badge(),
                TextEntry::make('trunk_diameter_cm')->label('Trunk diameter')->suffix(' cm')->placeholder('Not measured'),
                TextEntry::make('estimated_height_m')->label('Estimated height')->suffix(' m')->placeholder('Not measured'),
                TextEntry::make('estimated_age_years')->label('Estimated age')->suffix(' years')->placeholder('Not estimated'),
                TextEntry::make('date_planted')->label('Date planted')->date()->placeholder('Unknown'),
                IconEntry::make('is_featured')->label('Featured')->boolean(),
            ]),
            Section::make('Map location')->columns(2)->collapsible()->schema([
                TextEntry::make('latitude')->placeholder('Not recorded')->copyable(),
                TextEntry::make('longitude')->placeholder('Not recorded')->copyable(),
            ]),
            Section::make('Notes')->columns(2)->schema([
                TextEntry::make('public_notes')->label('Public notes')->placeholder('No public notes')->columnSpanFull(),
                TextEntry::make('internal_remarks')->label('Internal remarks')->placeholder('No internal remarks')->columnSpanFull(),
            ]),
            Section::make('Verification')->columns(3)->schema([
                TextEntry::make('verification_status')->label('Status')->badge(),
                TextEntry::make('verifier.name')->label('Verified by')->placeholder('Not assigned'),
                TextEntry::make('verified_at')->label('Verified at')->dateTime()->placeholder('Not verified'),
                TextEntry::make('published_at')->label('Published at')->dateTime()->placeholder('Not published'),
                TextEntry::make('recorder.name')->label('Recorded by')->placeholder('Unknown'),
                TextEntry::make('created_at')->label('Registered at')->dateTime(),
                TextEntry::make('deleted_at')->label('Archived at')->dateTime()->visible(fn (Tree $record): bool => $record->trashed()),
            ]),
        ]);
    }
}
