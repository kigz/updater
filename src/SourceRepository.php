<?php

namespace Osen\Updater;

use Osen\Updater\Contracts\SourceRepositoryTypeContract;
use Osen\Updater\Models\Release;
use Osen\Updater\Models\UpdateExecutor;
use Osen\Updater\Traits\SupportPrivateAccessToken;
use Osen\Updater\Traits\UseVersionFile;
use Illuminate\Support\Facades\Artisan;

/**
 * SourceRepository.
 *
 * @author Osen Concepts < hi@osen.co.ke >
 * @copyright See LICENSE file that was distributed with this source code.
 */
final class SourceRepository implements SourceRepositoryTypeContract
{
    use UseVersionFile, SupportPrivateAccessToken;

    /**
     * @var SourceRepositoryTypeContract
     */
    protected $sourceRepository;

    /**
     * @var UpdateExecutor
     */
    protected $updateExecutor;

    /**
     * SourceRepository constructor.
     *
     * @param SourceRepositoryTypeContract $sourceRepository
     */
    public function __construct(SourceRepositoryTypeContract $sourceRepository, UpdateExecutor $updateExecutor)
    {
        $this->sourceRepository = $sourceRepository;
        $this->updateExecutor = $updateExecutor;
    }

    /**
     * Fetches the latest version. If you do not want the latest version, specify one and pass it.
     *
     * @param string $version
     *
     * @return Release
     */
    public function fetch($version = ''): Release
    {
        $version = $version ?: $this->getVersionAvailable();

        return $this->sourceRepository->fetch($version);
    }

    /**
     * @param Release $release
     *
     * @return bool
     * @throws \Exception
     */
    public function update(Release $release): bool
    {
        return $this->updateExecutor->run($release);
    }

    /**
     * Check repository if a newer version than the installed one is available.
     *
     * @param string $currentVersion
     *
     * @return bool
     */
    public function isNewVersionAvailable($currentVersion = ''): bool
    {
        return $this->sourceRepository->isNewVersionAvailable($currentVersion);
    }

    /**
     * Get the version that is currenly installed.
     * Example: 1.1.0 or v1.1.0 or "1.1.0 version".
     *
     * @param string $prepend
     * @param string $append
     *
     * @return string
     */
    public function getVersionInstalled($prepend = '', $append = ''): string
    {
        return $this->sourceRepository->getVersionInstalled($prepend, $append);
    }

    /**
     * Get the latest version that has been published in a certain repository.
     * Example: 2.6.5 or v2.6.5.
     *
     * @param string $prepend Prepend a string to the latest version
     * @param string $append  Append a string to the latest version
     *
     * @return string
     */
    public function getVersionAvailable($prepend = '', $append = ''): string
    {
        return $this->sourceRepository->getVersionAvailable($prepend, $append);
    }

    /**
     * Run pre update artisan commands from config.
     */
    public function preUpdateArtisanCommands(): int
    {
        $commands = collect(config('updater.artisan_commands.pre_update'));

        $commands->each(function ($commandParams, $commandKey) {
            Artisan::call($commandKey, $commandParams['params']);
        });

        return $commands->count();
    }

    /**
     * Run post update artisan commands from config.
     */
    public function postUpdateArtisanCommands(): int
    {
        $commands = collect(config('updater.artisan_commands.post_update'));

        $commands->each(function ($commandParams, $commandKey) {
            Artisan::call($commandKey, $commandParams['params']);
        });

        return $commands->count();
    }
}
