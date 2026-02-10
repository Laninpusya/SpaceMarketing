<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomainsController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $domains = $user->domains()->latest()->get();
        return view('domains.index', compact('domains'));
    }

    public function create()
    {
        return view('domains.create');
    }

    // Пока оставим пустыми, потом добавим
    public function store(Request $request)
    {

        $data = $request->only('domain_name', 'check_interval', 'timeout', 'method');
        $validated = $request->validate([
            'domain_name'    => 'required|url|unique:domains,domain_name',
            'check_interval' => 'required|integer|min:1',
            'timeout'        => 'required|integer|min:5',
            'method'         => 'required|in:HEAD,GET',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $user->domains()->create($validated);

        return redirect()->route('domains.index')
            ->with('success', 'Домен добавлен!');
    }
    public function show(Domain $domain) {}
    public function edit(Domain $domain) {}
    public function update(Request $request, Domain $domain) {}
    public function destroy(Domain $domain)
    {
        // Проверяем, что домен принадлежит текущему пользователю
        if ($domain->user_id !== Auth::id()) {
            abort(403);
        }

        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', 'Домен удалён!');
    }
}