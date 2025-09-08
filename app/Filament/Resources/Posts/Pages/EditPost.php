<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Models\Genres;
use App\Models\PostMorphable;
use App\Models\PostProducer;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $data = $this->data;
        $record = $this->record;

        // Clear existing genre relationships
        PostMorphable::where('post_id', $record->id)
            ->where('morphable_type', Genres::class)
            ->delete();

        // Handle genre relationships
        $this->syncGenreRelationships($record, $data);

        // Clear existing producer relationships
        PostProducer::where('post_id', $record->id)->delete();

        // Handle producer relationships
        $this->syncProducerRelationships($record, $data);
    }

    private function syncGenreRelationships($record, $data): void
    {
        $genreTypes = [
            'genre_ids' => 'genres',
            'explicit_genre_ids' => 'explicit_genres',
            'theme_ids' => 'themes',
            'demographic_ids' => 'demographics',
        ];

        foreach ($genreTypes as $fieldName => $genreType) {
            if (isset($data[$fieldName]) && is_array($data[$fieldName])) {
                foreach ($data[$fieldName] as $genreId) {
                    PostMorphable::firstOrCreate([
                        'post_id' => $record->id,
                        'morphable_id' => $genreId,
                        'morphable_type' => Genres::class,
                    ]);
                }
            }
        }
    }

    private function syncProducerRelationships($record, $data): void
    {
        $producerTypes = [
            'producer_ids' => 'producer',
            'licensor_ids' => 'licensor',
            'studio_ids' => 'studio',
        ];

        foreach ($producerTypes as $fieldName => $producerType) {
            if (isset($data[$fieldName]) && is_array($data[$fieldName])) {
                foreach ($data[$fieldName] as $producerId) {
                    PostProducer::firstOrCreate([
                        'post_id' => $record->id,
                        'producer_id' => $producerId,
                        'type' => $producerType,
                    ]);
                }
            }
        }
    }
}
