<?php

namespace Osen\Updater\Events;

use Osen\Updater\Models\Release;

class UpdateSucceeded
{
    /**
     * @var Release
     */
    protected $release;

    public function __construct(Release $release)
    {
        $this->release = $release;
    }

    /**
     * Get the new version.
     *
     * @return string
     */
    public function getVersionUpdatedTo(): ?string
    {
        return $this->release->getVersion();
    }
}
