<?php

namespace App\Http\Controllers;

use App\Models\JobGroup;
use App\Models\MinijobGroup;
use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {

        return Inertia::render('Users/Index', [
            'users' => User::all()
                ->transform(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'eingestellt_am' => $user->eingestellt_am ? $user->eingestellt_am->format('d.m.Y') : null,
                    'druck_sort' => $user->druck_sort,
                    'role' => $user->readable_role,
                    'sort' => $user->sort,
                    'minijob' => $user->minijob,
                ])->sortBy('sort')->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        $user = User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'chip_id' => ['nullable', 'string', 'max:255'],
            'eingestellt_am' => ['nullable', 'date'],
            'druck_sort' => ['nullable', 'integer'],
            'password' => ['required', Password::min(8)],
            'arbeitszeit_admin' => ['nullable', 'boolean'],
            'arbeitszeit_teamleader' => ['nullable', 'boolean'],
            'keine_arbeitszeit' => ['nullable', 'boolean'],
            'minijob' => ['nullable', 'boolean'],
        ]));

        return Redirect::route('users.edit', $user->id)->with('success', 'Benutzer erfolgreich erstellt.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function edit(User $user)
    {
        $other_users = User::all()
            ->except($user->id)
            ->sortBy('name')
            ->transform(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->readable_role,
            ])->values();

        $minijobGroups = MinijobGroup::all()
            ->sortBy('name')
            ->transform(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values();

        $jobGroups = JobGroup::all()
            ->sortBy('name')
            ->transform(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values();

        $user->append(['readable_role', 'minijob_group'])->load(['arbeitszeitenTeam', 'jobGroups']);

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'other_users' => $other_users,
            'minijobGroups' => $minijobGroups,
            'jobGroups' => $jobGroups,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telegram_id' => ['nullable', 'string', 'max:255'],
            'chip_id' => ['nullable', 'string', 'max:255'],
            'eingestellt_am' => ['nullable', 'date'],
            'druck_sort' => ['nullable', 'integer'],
            'password' => ['nullable'],
            'arbeitszeit_admin' => ['nullable', 'boolean'],
            'arbeitszeit_teamleader' => ['nullable', 'boolean'],
            'keine_arbeitszeit' => ['nullable', 'boolean'],
            'minijob' => ['nullable', 'boolean'],
            'job_groups' => ['nullable', 'array'],
            'job_groups.*' => ['exists:job_groups,id'],
        ]);


        $user->update(
            $request->only(
                'name',
                'email',
                'telegram_id',
                'chip_id',
                'eingestellt_am',
                'druck_sort',
                'arbeitszeit_admin',
                'arbeitszeit_teamleader',
                'keine_arbeitszeit',
                'minijob'
            )
        );

        if ($request->get('password') != null) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->arbeitszeitenTeam()->sync($request->get('arbeitszeitenTeam'));

        $user->minijobGroups()->sync($user->minijob ? $request->get('minijob_group') : null);

        $user->jobGroups()->sync($request->get('job_groups', []));

        return Redirect::back()->with('success', 'Benutzer aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return Redirect::route('users.index')->with('success', 'Benutzer gelöscht.');
    }


    public function order(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
        ]);

        User::setNewOrder($request->ids);

        return Redirect::back()->with('success', 'Benutzer sortiert.');
    }

    /**
     * Allows To Login as Different User
     * @param  Request  $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, $id)
    {

        if (Gate::allows('login-as-user', Auth::user())) {
            $request->session()->put('original_user_id', Auth::user()->id);
            Auth::loginUsingId($id);
        }

        return Redirect::route('dashboard')->with('success', 'Benutzer gewechselt');

    }

    /**
     * Login Back as Original User after Switch
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchBack(Request $request)
    {

        $original_user_id = $request->session()->get('original_user_id');

        Auth::loginUsingId($original_user_id);

        $request->session()->forget('original_user_id');

        return Redirect::route('dashboard')->with('success', 'Benutzer zurück gewechselt');

    }

    /**
     * Generate API Token for User
     * @param  User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateToken(User $user)
    {
        // Delete old tokens
        $user->tokens()->whereName("api-access")->delete();

        // Create new token
        $token = $user->createToken('api-access');

        // Generate QR Code data
        $qrData = json_encode([
            'url' => url('/api'),
            'token' => $token->plainTextToken,
            'user' => $user->name,
        ]);

        // Save QR data to user
        $user->update([
            'api_qr_data' => $qrData,
        ]);

        return Redirect::back()->with([
            'success' => 'API-Token wurde generiert und gespeichert',
        ]);
    }
}
