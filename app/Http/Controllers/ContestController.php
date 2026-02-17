<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Http\Requests\StoreContestRequest;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    public function index()
    {
        $contests = Contest::latest()->paginate(10);
        return view('contests.index', compact('contests'));
    }

    public function create()
    {
        return view('contests.create');
    }

    public function store(StoreContestRequest $request)
    {
        $contest = Contest::create($request->validated());

        return redirect()
            ->route('contests.show', $contest)
            ->with('success', 'Конкурс успешно создан');
    }

    public function show(Contest $contest)
    {
        $submissions = $contest->submissions()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('contests.show', compact('contest', 'submissions'));
    }

    public function edit(Contest $contest)
    {
        return view('contests.edit', compact('contest'));
    }

    public function update(StoreContestRequest $request, Contest $contest)
    {
        $contest->update($request->validated());

        return redirect()
            ->route('contests.show', $contest)
            ->with('success', 'Конкурс успешно обновлен');
    }

    public function destroy(Contest $contest)
    {
        $contest->delete();

        return redirect()
            ->route('contests.index')
            ->with('success', 'Конкурс успешно удален');
    }
}
