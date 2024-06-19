<?php

use App\Models\Image;
use Illuminate\Support\Facades\Cache;

class CalculateUsedDiskSpace
{
    public $user;

    public $totalSize = 0;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $this
            ->calculateImageDiskSize()
            ->calculateFileDiskSize()
            ->calculateTextDiskSize()
            ->calculateUrlDiskSize()
            ->saveTotalSpaceUsed()
            ->deleteStatsCache();
    }

    protected function calculateImageDiskSize()
    {
        foreach ($this->user->images()->cursor() as $image) {
            $this->totalSize += filesize(storage_path('app/images/' . $image->getResourceName()));

            foreach (Image::$supportedSizes as $size) {
                $this->totalSize += filesize(
                    storage_path('app/images/' . $image->getResourceName($size . 'x' . $size))
                );
            }
        }

        return $this;
    }

    protected function calculateFileDiskSize()
    {
        foreach ($this->user->files()->cursor() as $file) {
            $this->totalSize += filesize(storage_path('app/files/' . $file->getResourceName()));
        }

        return $this;
    }

    protected function calculateTextDiskSize()
    {
        foreach ($this->user->texts()->cursor() as $text) {
            $this->totalSize += mb_strlen($text->content);
        }

        return $this;
    }

    protected function calculateUrlDiskSize()
    {
        foreach ($this->user->urls()->cursor() as $url) {
            $this->totalSize += mb_strlen($url->url);

            $this->totalSize += filesize(storage_path('app/urls/' . $url->name . '.jpg'));
        }

        return $this;
    }

    protected function saveTotalSpaceUsed()
    {
        $this->user->update(['disk_space_used' => $this->totalSize]);

        return $this;
    }

    protected function deleteStatsCache()
    {
        Cache::forget('dashboard.stats::' . $this->user->id);
    }
}
