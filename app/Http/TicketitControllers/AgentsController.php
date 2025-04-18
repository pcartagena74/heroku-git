<?php

namespace App\Http\TicketitControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Setting;

class AgentsController extends Controller
{
    public function index(): View
    {
        $agents = Agent::agents()->get();

        return view('ticketit::admin.agent.index', compact('agents'));
    }

    public function create(): View
    {
        $users = Agent::paginate(Setting::grab('paginate_items'));

        return view('ticketit::admin.agent.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'agents' => 'required|array|min:1',
        ];

        if (LaravelVersion::min('5.2')) {
            $rules['agents.*'] = 'integer|exists:users,id';
        }

        $this->validate($request, $rules);

        $agents_list = $this->addAgents($request->input('agents'));
        $agents_names = implode(',', $agents_list);

        Session::flash('status', trans('ticketit::lang.agents-are-added-to-agents', ['names' => $agents_names]));

        return redirect()->action([self::class, 'index']);
    }

    public function update($id, Request $request): RedirectResponse
    {
        $this->syncAgentCategories($id, $request);

        Session::flash('status', trans('ticketit::lang.agents-joined-categories-ok'));

        return redirect()->action([self::class, 'index']);
    }

    public function destroy($id): RedirectResponse
    {
        $agent = $this->removeAgent($id);

        Session::flash('status', trans('ticketit::lang.agents-is-removed-from-team', ['name' => $agent->name]));

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Assign users as agents.
     *
     *
     * @return array
     */
    public function addAgents($user_ids)
    {
        $users = Agent::find($user_ids);
        foreach ($users as $user) {
            $user->ticketit_agent = true;
            $user->save();
            $users_list[] = $user->name;
        }

        return $users_list;
    }

    /**
     * Remove user from the agents.
     *
     *
     * @return mixed
     */
    public function removeAgent($id)
    {
        $agent = Agent::find($id);
        $agent->ticketit_agent = false;
        $agent->save();

        // Remove him from tickets categories as well
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $agent_cats = $agent->categories->pluck('id')->toArray();
        } else { // if Laravel 5.1
            $agent_cats = $agent->categories->lists('id')->toArray();
        }

        $agent->categories()->detach($agent_cats);

        return $agent;
    }

    /**
     * Sync Agent categories with the selected categories got from update form.
     */
    public function syncAgentCategories($id, Request $request)
    {
        $form_cats = ($request->input('agent_cats') == null) ? [] : $request->input('agent_cats');
        $agent = Agent::find($id);
        $agent->categories()->sync($form_cats);
    }
}
