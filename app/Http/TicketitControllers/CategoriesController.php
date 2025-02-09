<?php

namespace App\Http\TicketitControllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Category;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // seconds expected for L5.8<=, minutes before that
        $time = LaravelVersion::min('5.8') ? 60 * 60 : 60;
        $categories = \Cache::remember('ticketit::categories', $time, function () {
            return Category::all();
        });

        return view('ticketit::admin.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('ticketit::admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
        ]);

        $category = new Category;
        $category->create(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', trans('ticketit::lang.category-name-has-been-created', ['name' => $request->name]));

        \Cache::forget('ticketit::categories');

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        return 'All category related agents here';
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $category = Category::findOrFail($id);

        return view('ticketit::admin.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
        ]);

        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', trans('ticketit::lang.category-name-has-been-modified', ['name' => $request->name]));

        \Cache::forget('ticketit::categories');

        return redirect()->action([self::class, 'index']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $category->delete();

        Session::flash('status', trans('ticketit::lang.category-name-has-been-deleted', ['name' => $name]));

        \Cache::forget('ticketit::categories');

        return redirect()->action([self::class, 'index']);
    }
}
