<?php

namespace App\Http\Controllers;

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
//            if (strtotime($search_param)) {
//                // Convert the search parameter to a Carbon instance
//                $search_date = \Carbon\Carbon::parse($search_param)->startOfDay();
//
//                // Add a condition to search for records created on the specified date
//                $declaration_query->whereDate('created_at', $search_date);
//            }else{
             $declaration_query->where(function ($query) use ($search_param) {
                    $query->where('declaration_number', '=',  $search_param );
             });
//            }
        }

        // Paginate the results
        $declarations = $declaration_query->paginate(50);


        // Return the view with the paginated results
        return view('dashboard', ['declarations' => $declarations]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'declaration_number' => 'required|unique:custom_declarations',
            'status' => 'required',
        ]);

        $declaration_number =$request->declaration_number;
        if(strlen($declaration_number)>17)
        {
            $declaration_number=substr($declaration_number,17);
        }

        CustomDeclaration::create([
            'declaration_number' => $declaration_number,
            'status' => $request->status,
        ]);
        $declaration = CustomDeclaration::where('declaration_number' , '=',$declaration_number)->first();

        DeclarationHistory::create([
            'user_id' => auth()->id(),
            'declaration_id' => $declaration->id,
            'action' => "{$request->status}",
            'description'=> $request->description ?? 'لا يوجد'
        ]);
        if ($declaration->status == "العقبة الارشيف")
        {
            $declaration->delete();
        }

    return redirect()->back()->with('success', 'تم إضافة البيان الجمركي بنجاح!');
    }

 public function updateStatus(Request $request, $id) {
    $declaration = CustomDeclaration::findOrFail($id);


    if ($declaration->declaration_number !== $request->editNumber)
    {
        $declaration->declaration_number = $request->editNumber;
        $declaration->save();
    }

    if ($declaration->status !== $request->status) {
        $oldStatus = $declaration->status;
        $declaration->status = $request->status;
        $declaration->save();
        if ($request->status == 'العقبة الارشيف')
        {
            $declaration->delete();
        }

        DeclarationHistory::create([
            'user_id' => auth()->id(),
            'declaration_id' => $declaration->id,
            'action' => "$request->status ",
            'description'=> $request->editDescription ?? 'لا يوجد'
        ]);

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



    public function restore( $id)
    {
        $declaration = CustomDeclaration::withTrashed()->find($id);
        $declaration->restore();
        session()->flash('success', 'تم استرجاع البيان');
        return to_route('declaration.showRestore');
    }

    public function showRestore(Request $request)
    {
        $query = $request->input('search');
        if(strlen($query)>17)
        {
            $query=substr($query,17);
        }

        if ($query) {
            $declarations = CustomDeclaration::onlyTrashed()
                ->where('declaration_number', 'like', '%' . $query . '%')
                ->paginate(50);
        } else {
            $declarations = CustomDeclaration::onlyTrashed()->paginate(50);
        }
        return view('restore', ['declarations' => $declarations]);
    }



}
