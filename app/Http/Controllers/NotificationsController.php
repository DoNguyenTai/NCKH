<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return response()->json(Notifications::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        return Notifications::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notifications $notifications)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notifications $notifications)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notification = Notifications::findOrFail($id);
        $notification->update($validated);

        return $notification;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $notification = Notifications::findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Đã xóa thành công']);
    }
    public function searchNotifications(Request $request)
    {
        $query = Notifications::with('student');

        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('title', 'like', "%$keyword%")
                ->orWhere('message', 'like', "%$keyword%");
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        return $query->get();
    }
}
