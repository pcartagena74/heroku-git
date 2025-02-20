<?php

namespace App\Http\TicketitControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Setting;

class AdministratorsController extends Controller
{
    public function index(): View
    {
        $administrators = Agent::admins();

        return view('ticketit::admin.administrator.index', compact('administrators'));
    }

    public function create(): View
    {
        $users = Agent::paginate(Setting::grab('paginate_items'));

        return view('ticketit::admin.administrator.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $administrators_list = $this->addAdministrators($request->input('administrators'));
        $administrators_names = implode(',', $administrators_list);

        Session::flash('status', trans('ticketit::lang.administrators-are-added-to-administrators', ['names' => $administrators_names]));

        return redirect()->action([self::class, 'index']);
    }

    public function update($id, Request $request): RedirectResponse
    {
        $this->syncAdministratorCategories($id, $request);

        Session::flash('status', trans('ticketit::lang.administrators-joined-categories-ok'));

        return redirect()->action([self::class, 'index']);
    }

    public function destroy($id): RedirectResponse
    {
        $administrator = $this->removeAdministrator($id);

        Session::flash('status', trans('ticketit::lang.administrators-is-removed-from-team', ['name' => $administrator->name]));

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Assign users as administrators.
     *
     *
     * @return array
     */
    public function addAdministrators($user_ids)
    {
        $users = Agent::find($user_ids);
        foreach ($users as $user) {
            $user->ticketit_admin = true;
            $user->save();
            $users_list[] = $user->name;
        }

        return $users_list;
    }

    /**
     * Remove user from the administrators.
     *
     *
     * @return mixed
     */
    public function removeAdministrator($id)
    {
        $administrator = Agent::find($id);
        $administrator->ticketit_admin = false;
        $administrator->save();

        // Remove him from tickets categories as well
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $administrator_cats = $administrator->categories->pluck('id')->toArray();
        } else { // if Laravel 5.1
            $administrator_cats = $administrator->categories->lists('id')->toArray();
        }

        $administrator->categories()->detach($administrator_cats);

        return $administrator;
    }

    /**
     * Sync Administrator categories with the selected categories got from update form.
     */
    public function syncAdministratorCategories($id, Request $request)
    {
        $form_cats = ($request->input('administrator_cats') == null) ? [] : $request->input('administrator_cats');
        $administrator = Agent::find($id);
        $administrator->categories()->sync($form_cats);
    }
}
