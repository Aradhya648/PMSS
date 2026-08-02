<?php
/**
 * PMSS — *arr Application Installer
 *
 * Installs Sonarr/Radarr/Lidarr/etc. from release archives.
 * Policy: installed binaries are NEVER executed — validation is metadata-only.
 */

declare(strict_types=1);

namespace PMSS;

final class ArrInstaller
{
    private string $installRoot;
    private string $tmpDir;

    public function __construct(string $installRoot)
    {
        $this->installRoot = rtrim($installRoot, '/');
        $this->tmpDir = sys_get_temp_dir() . '/pmssarrinstall' . uniqid();
    }

    /**
     * Install a *arr application from a release archive.
     */
    public function install(string $app, string $version, string $archiveUrl): string
    {
        $targetDir = $this->installRoot . '/' . $app;
        $archivePath = $this->tmpDir . '/bundle-' . $version . '.tar.gz';

        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }

        // Download archive
        $this->download($archiveUrl, $archivePath);

        // Extract
        $this->extract($archivePath, $this->tmpDir);

        // Move to install location
        if (is_dir($targetDir)) {
            $this->rmdirRecursive($targetDir);
        }
        rename($this->tmpDir . '/PackageDir', $targetDir);

        // Validate installation — metadata only, never execute
        $this->validateInstall($targetDir);

        // Cleanup
        unlink($archivePath);

        return $targetDir;
    }

    /**
     * Validate the installation using metadata only.
     * Policy: we never probe or execute installed binaries.
     */
    private function validateInstall(string $targetDir): void
    {
        // Check that the expected entrypoint file exists (metadata check, not execution)
        $entrypoint = $targetDir . '/' . $this->resolveEntrypoint($targetDir);
        if (!file_exists($entrypoint)) {
            throw new \RuntimeException("Installed app entrypoint not found: $entrypoint");
        }

        // Verify the file is readable (permission metadata, not execution trust)
        if (!is_readable($entrypoint)) {
            throw new \RuntimeException("Installed app entrypoint is not readable: $entrypoint");
        }
    }

    private function resolveEntrypoint(string $targetDir): string
    {
        $appName = basename($targetDir);
        $candidates = [$appName, 'Sonarr', 'Radarr', 'Lidarr', 'Prowlarr', 'Readarr', 'Whisparr'];
        foreach ($candidates as $candidate) {
            if (file_exists($targetDir . '/' . $candidate)) {
                return $candidate;
            }
        }
        return $appName;
    }

    private function download(string $url, string $dest): void
    {
        $cmd = sprintf('curl -sSL --fail -o %s %s', escapeshellarg($dest), escapeshellarg($url));
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException("Failed to download $url");
        }
    }

    private function extract(string $archivePath, string $destDir): void
    {
        $cmd = sprintf('tar -xzf %s -C %s', escapeshellarg($archivePath), escapeshellarg($destDir));
        exec($cmd, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException("Failed to extract $archivePath");
        }
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($dir);
    }
}
