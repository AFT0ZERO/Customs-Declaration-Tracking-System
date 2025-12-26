<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomDeclarationRequest;
use App\Http\Requests\UpdateCustomDeclarationRequest;
use App\Models\CustomDeclaration;
use App\Models\DeclarationHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomDeclarationController extends Controller
{
    public function index(Request $request)
    {
        $declaration_query = CustomDeclaration::query();

        // Get the search parameter from the request
        $search_param = $request->query('search');

        // Trim the search parameter if it exceeds 17 characters
        if (strlen($search_param) > 17) {
            $search_param = substr($search_param, 17);
        }

        // Apply search logic if the search parameter is not empty
        if (!empty($search_param)) {
            $declaration_query->where(function ($query) use ($search_param) {
                $query->where('declaration_number', '=', $search_param);
            });
        }

        // Handle sorting
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['declaration_number', 'declaration_type', 'status', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'created_at';
        }

        // Apply sorting with proper type casting for declaration_number
        if ($sort === 'declaration_number') {
            $declaration_query->orderByRaw("CAST(declaration_number AS UNSIGNED) " . $direction);
        } else {
            $declaration_query->orderBy($sort, $direction);
        }

        // Paginate the results
        $declarations = $declaration_query->paginate(50);

        // Return the view with the paginated results
        return view('dashboard', ['declarations' => $declarations]);
    }

    public function store(StoreCustomDeclarationRequest $request)
    {
        $declaration_number = $request->declaration_number;
        if (strlen($declaration_number) > 17) {
            $declaration_number = substr($declaration_number, 17);
        }

        $declaration = CustomDeclaration::create([
            'declaration_number' => $declaration_number,
            'declaration_type' => $request->declaration_type,
            'year' => $request->year,
            'status' => $request->status,
        ]);

        DeclarationHistory::create([
            'user_id' => auth()->id(),
            'declaration_id' => $declaration->id,
            'action' => "{$request->status}",
            'description' => $request->description ?? 'لا يوجد'
        ]);

        if ($declaration->status == "العقبة الارشيف") {
            $declaration->delete();
        }

        return redirect()->back()->with('success', 'تم إضافة البيان الجمركي بنجاح!');
    }

    public function updateStatus(UpdateCustomDeclarationRequest $request, $id)
    {
        $declaration = CustomDeclaration::findOrFail($id);

        $hasChanges = false;

        if ($declaration->declaration_number !== $request->editNumber) {
            $declaration->declaration_number = $request->editNumber;
            $hasChanges = true;
        }

        if ($declaration->declaration_type !== $request->declaration_type) {
            $declaration->declaration_type = $request->declaration_type;
            $hasChanges = true;
        }

        if ($declaration->year != $request->year) {
            $declaration->year = $request->year;
            $hasChanges = true;
        }

        if ($declaration->status !== $request->status) {
            $declaration->status = $request->status;
            $hasChanges = true;

            if ($request->status == 'العقبة الارشيف') {
                $declaration->delete();
            }

            DeclarationHistory::create([
                'user_id' => auth()->id(),
                'declaration_id' => $declaration->id,
                'action' => "$request->status ",
                'description' => $request->editDescription ?? 'لا يوجد'
            ]);
        }

        if ($hasChanges) {
            $declaration->save();
            return redirect()->back()->with('success', 'تم تحديث الحالة بنجاح!');
        }

        return redirect()->back()->with('info', 'لم يتم تغيير الحالة');
    }

    public function showHistory($id)
    {
        $declaration = CustomDeclaration::withTrashed()->findOrFail($id);
        $history = $declaration->histories()->orderBy('created_at', 'desc')->get();
        Carbon::setLocale('ar');
        return view('history', compact('history', 'declaration'));
    }



    public function restore($id)
    {
        $declaration = CustomDeclaration::withTrashed()->find($id);
        $declaration->restore();
        session()->flash('success', 'تم استرجاع البيان');
        return to_route('declaration.showRestore');
    }

    public function showRestore(Request $request)
    {
        $query = $request->input('search');

        // Normalize the search query if it's too long
        if ($query && strlen($query) > 17) {
            $query = substr($query, 17);
        }

        $declarations = CustomDeclaration::onlyTrashed()
            ->when($query, function ($q) use ($query) {
                return $q->where('declaration_number', $query);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        return view('restore', [
            'declarations' => $declarations,
            'search' => $query
        ]);
    }



}
