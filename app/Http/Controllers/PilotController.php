<?php

namespace App\Http\Controllers;

use App\Http\Requests\PilotRequest;
use App\Models\Pilot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PilotController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Pilot::class);

        $pilots = Pilot::query()
            ->withCount('registrations')
            ->orderBy('name')
            ->paginate(15);

        return view('pilots.index', compact('pilots'));
    }

    public function create(): View
    {
        $this->authorize('create', Pilot::class);

        return view('pilots.form', ['pilot' => new Pilot()]);
    }

    public function store(PilotRequest $request): RedirectResponse
    {
        $this->authorize('create', Pilot::class);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pilots', 'public');
        }

        $pilot = Pilot::query()->create($data);

        return redirect()->route('pilots.edit', $pilot)->with('status', 'Piloto criado com sucesso.');
    }

    public function show(Pilot $pilot): View
    {
        $this->authorize('view', $pilot);

        $pilot->load([
            'registrations.seasonCategory.season',
            'registrations.seasonCategory.category',
            'stageEntries.stageCategory.stage',
            'stageEntries.stageCategory.seasonCategory.category',
            'championshipStandings.seasonCategory.category',
        ]);

        return view('pilots.show', compact('pilot'));
    }

    public function edit(Pilot $pilot): View
    {
        $this->authorize('update', $pilot);

        return view('pilots.form', compact('pilot'));
    }

    public function update(PilotRequest $request, Pilot $pilot): RedirectResponse
    {
        $this->authorize('update', $pilot);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($pilot->photo_path) {
                Storage::disk('public')->delete($pilot->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('pilots', 'public');
        }

        $pilot->update($data);

        return back()->with('status', 'Piloto atualizado com sucesso.');
    }

    public function destroy(Pilot $pilot): RedirectResponse
    {
        $this->authorize('delete', $pilot);

        $hasHistory = $pilot->stageEntries()->exists();

        if ($hasHistory) {
            $pilot->delete();

            return back()->with('status', 'Piloto removido com soft delete para preservar o histórico.');
        }

        if ($pilot->photo_path) {
            Storage::disk('public')->delete($pilot->photo_path);
        }

        $pilot->forceDelete();

        return redirect()->route('pilots.index')->with('status', 'Piloto removido definitivamente.');
    }
}
