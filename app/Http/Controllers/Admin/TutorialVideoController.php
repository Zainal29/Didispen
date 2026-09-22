<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorialVideo;
use Illuminate\Http\Request;

class TutorialVideoController extends Controller
{
    public function index()
    {
        $videos = TutorialVideo::orderBy('role')->orderBy('title')->get();
        return view('admin.tutorial-videos.index', compact('videos'));
    }

    public function create()
    {
        $roles = ['siswa', 'guru', 'satpam', 'admin'];
        return view('admin.tutorial-videos.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:siswa,guru,satpam,admin',
            'title' => 'required|string|max:255',
            'youtube_url' => 'required|url',
        ]);

        $videoId = TutorialVideo::extractVideoId($validated['youtube_url']);

        TutorialVideo::create([
            'role' => $validated['role'],
            'title' => $validated['title'],
            'youtube_url' => $validated['youtube_url'],
            'video_id' => $videoId,
            'is_active' => true,
        ]);

        return redirect()->route('admin.tutorial-videos.index')
            ->with('success', 'Video tutorial berhasil ditambahkan!');
    }

    public function edit(TutorialVideo $tutorialVideo)
    {
        $roles = ['siswa', 'guru', 'satpam', 'admin'];
        return view('admin.tutorial-videos.edit', compact('tutorialVideo', 'roles'));
    }

    public function update(Request $request, TutorialVideo $tutorialVideo)
    {
        $validated = $request->validate([
            'role' => 'required|in:siswa,guru,satpam,admin',
            'title' => 'required|string|max:255',
            'youtube_url' => 'required|url',
            'is_active' => 'boolean',
        ]);

        $videoId = TutorialVideo::extractVideoId($validated['youtube_url']);

        $tutorialVideo->update([
            'role' => $validated['role'],
            'title' => $validated['title'],
            'youtube_url' => $validated['youtube_url'],
            'video_id' => $videoId,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.tutorial-videos.index')
            ->with('success', 'Video tutorial berhasil diperbarui!');
    }

    public function destroy(TutorialVideo $tutorialVideo)
    {
        $tutorialVideo->delete();
        return redirect()->route('admin.tutorial-videos.index')
            ->with('success', 'Video tutorial berhasil dihapus!');
    }
}
