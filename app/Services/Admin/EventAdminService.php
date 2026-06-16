<?php

namespace App\Services\Admin;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventAdminService
{
    public function __construct(private readonly MediaUploadService $mediaUploadService)
    {
    }

    public function store(
        array $eventData,
        array $galleryImages,
        array $tickets,
        int $organizerId,
        ?UploadedFile $bannerImage = null,
        array $galleryFiles = []
    ): Event
    {
        return DB::transaction(function () use ($eventData, $galleryImages, $tickets, $organizerId, $bannerImage, $galleryFiles) {
            if ($bannerImage instanceof UploadedFile) {
                $eventData['banner_image_url'] = $this->mediaUploadService->storeImage($bannerImage, 'events/banners');
            }

            $event = Event::create($eventData + [
                'organizer_id' => $organizerId,
                'slug' => $this->generateSlug($eventData['title']),
            ]);

            $this->syncImages($event, array_merge(
                $galleryImages,
                $this->mediaUploadService->storeMany($galleryFiles, 'events/gallery')
            ));
            $this->syncTickets($event, $tickets);

            return $event->fresh(['images', 'tickets']);
        });
    }

    public function update(
        Event $event,
        array $eventData,
        array $galleryImages,
        array $tickets,
        array $existingGalleryImages = [],
        ?UploadedFile $bannerImage = null,
        array $galleryFiles = [],
        bool $removeBannerImage = false
    ): Event
    {
        return DB::transaction(function () use ($event, $eventData, $galleryImages, $tickets, $existingGalleryImages, $bannerImage, $galleryFiles, $removeBannerImage) {
            $currentBannerImage = $event->banner_image_url;

            if ($removeBannerImage) {
                $this->mediaUploadService->deleteIfManaged($currentBannerImage);
                $eventData['banner_image_url'] = null;
            }

            if ($bannerImage instanceof UploadedFile) {
                $this->mediaUploadService->deleteIfManaged($currentBannerImage);
                $eventData['banner_image_url'] = $this->mediaUploadService->storeImage($bannerImage, 'events/banners');
            }

            $event->update($eventData);

            $finalGalleryImages = array_merge(
                $existingGalleryImages,
                $galleryImages,
                $this->mediaUploadService->storeMany($galleryFiles, 'events/gallery')
            );

            $this->syncImages($event, $finalGalleryImages);
            $this->syncTickets($event, $tickets);

            return $event->fresh(['images', 'tickets']);
        });
    }

    private function syncImages(Event $event, array $galleryImages): void
    {
        $currentImages = $event->images()->get();

        foreach ($currentImages as $image) {
            if (! in_array($image->image_url, $galleryImages, true)) {
                $this->mediaUploadService->deleteIfManaged($image->image_url);
            }
        }

        $event->images()->delete();

        foreach ($galleryImages as $index => $imageUrl) {
            $event->images()->create([
                'image_url' => $imageUrl,
                'alt_text' => $event->title.' '.($index + 1),
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function syncTickets(Event $event, array $tickets): void
    {
        $event->tickets()->delete();

        foreach ($tickets as $index => $ticket) {
            $event->tickets()->create($ticket + [
                'sort_order' => $ticket['sort_order'] ?: $index + 1,
            ]);
        }
    }

    private function generateSlug(string $title): string
    {
        return Str::slug($title).'-'.Str::lower(Str::random(5));
    }
}
