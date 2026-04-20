<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    private const IMG_BASE_PATH = 'public/static/img';
    private const IMG_BASE_URL  = '/static/img';
    private const ALLOWED_EXT   = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    /**
     * Browse all images grouped by directory.
     */
    public function index(Request $request)
    {
        $basePath = base_path(self::IMG_BASE_PATH);
        $filter   = $request->get('dir', '');

        $structure = $this->scanDirectory($basePath);
        $dirs      = array_keys($structure);

        // Filter to a specific directory if requested
        if ($filter && isset($structure[$filter])) {
            $structure = [$filter => $structure[$filter]];
        }

        return view('admin.images.index', compact('structure', 'dirs', 'filter'));
    }

    /**
     * Show upload form.
     */
    public function create()
    {
        $basePath = base_path(self::IMG_BASE_PATH);
        $dirs     = $this->getDirectoryList($basePath);
        return view('admin.images.create', compact('dirs'));
    }

    /**
     * Handle image upload.
     */
	public function store(Request $request)
	{
		$request->validate([
			'image'      => 'required|file|image|max:10240',
			'subdir'     => 'nullable|string|max:50',
			'new_subdir' => 'nullable|string|max:50',
		]);

		$subdir = trim($request->input('new_subdir') ?: $request->input('subdir', ''), '/');
		$subdir = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $subdir);

		$basePath = base_path(self::IMG_BASE_PATH);
		$destDir  = $subdir ? $basePath . '/' . $subdir : $basePath;

		if (!is_dir($destDir)) {
			mkdir($destDir, 0755, true);
		}

		$file      = $request->file('image');
		$filename  = $file->getClientOriginalName();
		$ext       = pathinfo($filename, PATHINFO_EXTENSION);
		$base      = pathinfo($filename, PATHINFO_FILENAME);
		$destPath  = $destDir . '/' . $filename;

		$backedUp = false;
		if (file_exists($destPath)) {
			// Find next available backup slot: filename-bk1.ext, filename-bk2.ext, etc.
			$i = 1;
			do {
				$backupName = "{$base}-bk{$i}.{$ext}";
				$backupPath = $destDir . '/' . $backupName;
				$i++;
			} while (file_exists($backupPath));

			rename($destPath, $backupPath);
			$backedUp = $backupName;
		}

		$file->move($destDir, $filename);

		if ($backedUp) {
			session()->flash('success', "Image uploaded — previous '{$filename}' backed up as '{$backedUp}'.");
		} else {
			session()->flash('success', "Image '{$filename}' uploaded successfully.");
		}

		$filterParam = $subdir ?: '';
		return redirect("/admin/images?dir=" . urlencode($filterParam));
	}

    /**
     * Confirm delete page — checks DB references first.
     */
    public function confirmDelete(Request $request)
    {
        $path = $request->input('path');
        if (!$path) abort(422);

        // Sanitize — must be within img base
        $fullPath = base_path(self::IMG_BASE_PATH) . '/' . ltrim($path, '/');
        $realBase = realpath(base_path(self::IMG_BASE_PATH));
        $realFull = realpath($fullPath);

        if (!$realFull || strpos($realFull, $realBase) !== 0) {
            abort(403, 'Invalid path.');
        }

        if (!file_exists($fullPath)) abort(404);

        // Check DB references — badge.imgurl and link.imgurl
        $filename = basename($path);
        $urlPath  = self::IMG_BASE_URL . '/' . ltrim($path, '/');

        $badgeRefs = DB::table('badge')
            ->where('imgurl', 'LIKE', '%' . $filename . '%')
            ->where('delflg', 0)
            ->select('pkey', 'bdg_title', 'imgurl')
            ->get();

        $linkRefs = DB::table('link')
            ->where('imgurl', 'LIKE', '%' . $filename . '%')
            ->where('delflg', 0)
            ->select('pkey', 'linknm', 'imgurl')
            ->get();

        return view('admin.images.confirm-delete', compact('path', 'urlPath', 'filename', 'badgeRefs', 'linkRefs'));
    }

    /**
     * Delete an image file.
     */
    public function destroy(Request $request)
    {
        $path = $request->input('path');
        if (!$path) abort(422);

        $fullPath = base_path(self::IMG_BASE_PATH) . '/' . ltrim($path, '/');
        $realBase = realpath(base_path(self::IMG_BASE_PATH));
        $realFull = realpath($fullPath);

        if (!$realFull || strpos($realFull, $realBase) !== 0) {
            abort(403, 'Invalid path.');
        }

        if (!file_exists($fullPath)) abort(404);

        $filename = basename($fullPath);
        unlink($fullPath);

        session()->flash('success', "Image '{$filename}' deleted.");

        // Redirect back to the directory
        $subdir = dirname($path);
        $subdir = $subdir === '.' ? '' : $subdir;
        return redirect("/admin/images?dir=" . urlencode($subdir));
    }

    /**
     * Scan the img directory recursively, returning grouped structure.
     */
    private function scanDirectory(string $basePath): array
    {
        $structure = [];

        // Root level files
        $rootFiles = $this->getImagesInDir($basePath);
        if ($rootFiles) {
            $structure['(root)'] = $rootFiles;
        }

        // Subdirectories
        $dirs = glob($basePath . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            $files   = $this->getImagesInDir($dir);
            if ($files !== false) {
                $structure[$dirName] = $files;
            }

            // One level deeper (e.g. badges/Grandmaster/)
            $subDirs = glob($dir . '/*', GLOB_ONLYDIR);
            foreach ($subDirs as $subDir) {
                $subName = $dirName . '/' . basename($subDir);
                $subFiles = $this->getImagesInDir($subDir);
                if ($subFiles !== false) {
                    $structure[$subName] = $subFiles;
                }
            }
        }

        ksort($structure);
        return $structure;
    }

    /**
     * Get image files in a directory.
     */
    private function getImagesInDir(string $dir): array
    {
        $files = [];
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $dir . '/' . $file;
            if (!is_file($fullPath)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, self::ALLOWED_EXT)) {
                $files[] = $file;
            }
        }
        sort($files);
        return $files;
    }

    /**
     * Get flat list of directories for upload form.
     */
    private function getDirectoryList(string $basePath): array
    {
        $dirs = ['(root)'];
        foreach (glob($basePath . '/*', GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            $dirs[] = $name;
            foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subDir) {
                $dirs[] = $name . '/' . basename($subDir);
            }
        }
        return $dirs;
    }
}