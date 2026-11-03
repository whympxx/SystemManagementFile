<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $folder = $request->user()->folders()->create($data);

        return redirect()->route('documents.index', ['folder' => $folder->id])->with('success', 'Folder dibuat.');
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 404);
        $folder->update($request->validate(['name' => ['required', 'string', 'max:100']]));

        return back()->with('success', 'Folder diganti nama.');
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 404);
        $folder->delete(); // berkas di dalamnya tetap ada, hanya keluar dari folder

        return redirect()->route('documents.index')->with('success', 'Folder dihapus. Berkas di dalamnya tetap tersimpan.');
    }
}
