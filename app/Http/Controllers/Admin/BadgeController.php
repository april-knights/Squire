<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Knight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{
    private const BADGE_IMG_PATH = 'public/static/img/badges';
    private const BADGE_IMG_URL  = 'static/img/badges';
    private const TYPECDS = ['gm', 'position', 'rank', 'role', 'title', 'event', 'batt_div', 'misc'];

    /**
     * List all badges.
     */
    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'orderid');
        $direction = $request->get('direction', 'asc');
        $typeFilter = $request->get('typcd', '');

        $allowed_sorts = ['pkey', 'bdg_title', 'typcd', 'orderid', 'activeflg', 'delflg'];
        if (!in_array($sort, $allowed_sorts)) $sort = 'orderid';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'asc';

        $query = DB::table('badge')->orderBy($sort, $direction)->orderBy('bdg_title');
        if ($typeFilter) {
            $query->where('typcd', $typeFilter);
        }
        $badges = $query->get();

        // Knight counts per badge
        $counts = DB::table('knightbadge')
            ->select('fkeybadge', DB::raw('COUNT(*) as cnt'))
            ->where('delflg', 0)
            ->groupBy('fkeybadge')
            ->pluck('cnt', 'fkeybadge');

        $typecds = self::TYPECDS;

        return view('admin.badges.index', compact('badges', 'counts', 'sort', 'direction', 'typecds', 'typeFilter'));
    }

    /**
     * Show a single badge.
     */
    public function show($pkey)
    {
        $badge = DB::table('badge')->where('pkey', $pkey)->first();
        if (!$badge) abort(404);

        $knight_count = DB::table('knightbadge')->where('fkeybadge', $pkey)->where('delflg', 0)->count();

        $lstmdby_name = $badge->lstmdby
            ? Knight::withoutGlobalScopes()->where('pkey', $badge->lstmdby)->value('dname')
            : null;

        return view('admin.badges.show', compact('badge', 'knight_count', 'lstmdby_name'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $typecds     = self::TYPECDS;
        $imgDirs     = $this->getImageDirs();
        $imgFiles    = $this->getImageFiles();

        return view('admin.badges.create', compact('typecds', 'imgDirs', 'imgFiles'));
    }

    /**
     * Store a new badge.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bdg_title' => 'required|string|max:255',
            'typcd'     => 'required|in:' . implode(',', self::TYPECDS),
            'bdgdesc'   => 'nullable|string|max:500',
            'orderid'   => 'required|integer',
            'roleid'    => 'nullable|integer',
            'imgurl'    => 'nullable|string|max:150',
            'new_image' => 'nullable|file|image|max:2048',
            'img_subdir'=> 'nullable|string|max:50',
        ]);

        $imgurl  = $request->input('imgurl') ?: null;
        $adminId = auth()->user()->pkey;
        $now     = now();

        // Handle new image upload
        if ($request->hasFile('new_image') && $request->file('new_image')->isValid()) {
            $imgurl = $this->handleImageUpload($request);
        }

        DB::table('badge')->insert([
            'bdg_title'  => $request->input('bdg_title'),
            'typcd'      => $request->input('typcd'),
            'bdgdesc'    => $request->input('bdgdesc'),
            'orderid'    => $request->input('orderid'),
            'roleid'     => $request->input('roleid') ?: null,
            'imgurl'     => $imgurl,
            'crtsetdt'   => $now,
            'crtsetid'   => $adminId,
            'lstmdby'    => $adminId,
            'lstmdts'    => $now,
            'activeflg'  => 1,
            'delflg'     => 0,
        ]);

        $newPkey = DB::getPdo()->lastInsertId();
        session()->flash('success', "Badge '{$request->input('bdg_title')}' created.");
        return redirect("/admin/badges/{$newPkey}");
    }

    /**
     * Show edit form.
     */
    public function edit($pkey)
    {
        $badge    = DB::table('badge')->where('pkey', $pkey)->first();
        if (!$badge) abort(404);

        $typecds  = self::TYPECDS;
        $imgDirs  = $this->getImageDirs();
        $imgFiles = $this->getImageFiles();

        return view('admin.badges.edit', compact('badge', 'typecds', 'imgDirs', 'imgFiles'));
    }

    /**
     * Update a badge.
     */
    public function update(Request $request, $pkey)
    {
        $badge = DB::table('badge')->where('pkey', $pkey)->first();
        if (!$badge) abort(404);

        $request->validate([
            'bdg_title'  => 'required|string|max:255',
            'typcd'      => 'required|in:' . implode(',', self::TYPECDS),
            'bdgdesc'    => 'nullable|string|max:500',
            'orderid'    => 'required|integer',
            'roleid'     => 'nullable|integer',
            'imgurl'     => 'nullable|string|max:150',
            'new_image'  => 'nullable|file|image|max:2048',
            'img_subdir' => 'nullable|string|max:50',
            'clear_image'=> 'nullable|boolean',
        ]);

        $imgurl = $badge->imgurl;

        if ($request->boolean('clear_image')) {
            $imgurl = null;
        } elseif ($request->hasFile('new_image') && $request->file('new_image')->isValid()) {
            $imgurl = $this->handleImageUpload($request);
        } elseif ($request->filled('imgurl')) {
            $imgurl = $request->input('imgurl');
        }

        DB::table('badge')->where('pkey', $pkey)->update([
            'bdg_title' => $request->input('bdg_title'),
            'typcd'     => $request->input('typcd'),
            'bdgdesc'   => $request->input('bdgdesc'),
            'orderid'   => $request->input('orderid'),
            'roleid'    => $request->input('roleid') ?: null,
            'imgurl'    => $imgurl,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', "Badge updated.");
        return redirect("/admin/badges/{$pkey}");
    }

    /**
     * Soft delete — blocked if knights have been awarded this badge.
     */
    public function destroy($pkey)
    {
        $badge = DB::table('badge')->where('pkey', $pkey)->first();
        if (!$badge) abort(404);

        $knight_count = DB::table('knightbadge')->where('fkeybadge', $pkey)->where('delflg', 0)->count();
        if ($knight_count > 0) {
            session()->flash('error', "Cannot delete — {$knight_count} knight(s) have been awarded this badge.");
            return redirect("/admin/badges/{$pkey}");
        }

        DB::table('badge')->where('pkey', $pkey)->update([
            'delflg'  => 1,
            'lstmdby' => auth()->user()->pkey,
            'lstmdts' => now(),
        ]);

        session()->flash('success', 'Badge deleted.');
        return redirect('/admin/badges');
    }

    /**
     * Toggle activeflg.
     */
    public function toggle($pkey)
    {
        $badge = DB::table('badge')->where('pkey', $pkey)->first();
        if (!$badge) abort(404);

        DB::table('badge')->where('pkey', $pkey)->update([
            'activeflg' => $badge->activeflg ? 0 : 1,
            'lstmdby'   => auth()->user()->pkey,
            'lstmdts'   => now(),
        ]);

        session()->flash('success', 'Badge status updated.');
        return redirect("/admin/badges/{$pkey}");
    }

    /**
     * Handle image upload — saves to badges subdir, returns imgurl.
     */
    private function handleImageUpload(Request $request): string
    {
        $subdir  = trim($request->input('img_subdir', 'misc'), '/');
        $subdir  = preg_replace('/[^a-zA-Z0-9_\-]/', '', $subdir) ?: 'misc';
        $file    = $request->file('new_image');
        $name    = $file->getClientOriginalName();
        $destDir = base_path(self::BADGE_IMG_PATH . '/' . $subdir);

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $file->move($destDir, $name);
        return self::BADGE_IMG_URL . '/' . $subdir . '/' . $name;
    }

    /**
     * Get list of subdirectories in badges image folder.
     */
    private function getImageDirs(): array
    {
        $base = base_path(self::BADGE_IMG_PATH);
        if (!is_dir($base)) return [];

        return array_values(array_filter(
            scandir($base),
            fn($d) => $d !== '.' && $d !== '..' && is_dir($base . '/' . $d)
        ));
    }

    /**
     * Get all image files grouped by subdirectory.
     */
    private function getImageFiles(): array
    {
        $base  = base_path(self::BADGE_IMG_PATH);
        $dirs  = $this->getImageDirs();
        $files = [];

        foreach ($dirs as $dir) {
            $dirPath = $base . '/' . $dir;
            $images  = array_values(array_filter(
                scandir($dirPath),
                fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif', 'webp'])
            ));
            if ($images) {
                $files[$dir] = array_map(
                    fn($f) => self::BADGE_IMG_URL . '/' . $dir . '/' . $f,
                    $images
                );
            }
        }

        // Root level images (like NoArtYet.jpg)
        $rootImages = array_values(array_filter(
            scandir($base),
            fn($f) => is_file($base . '/' . $f) &&
                      in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif', 'webp'])
        ));
        if ($rootImages) {
            $files['(root)'] = array_map(
                fn($f) => self::BADGE_IMG_URL . '/' . $f,
                $rootImages
            );
        }

        return $files;
    }
}