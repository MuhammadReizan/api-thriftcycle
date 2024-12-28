<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::all();

        return response()->json([
            'success' => true,
            'message' => 'Items retrieved successfully',
            'data' => $items
        ], 200);
    }

    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item details retrieved successfully',
            'data' => $item
        ], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:5120', // Max 5MB
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:otomotive,clothes,electronic,stationary,toys,sport',
            'item_description' => 'required|string',
            'uploaded_by' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
        ]);

        $imagePath = $request->file('image')->store('items', 'public');

        Item::create([
            'image_path' => $imagePath,
            'item_name' => $validatedData['item_name'],
            'category' => $validatedData['category'],
            'item_description' => $validatedData['item_description'],
            'uploaded_by' => $validatedData['uploaded_by'],
            'address' => $validatedData['address'],
            'phone_number' => $validatedData['phone_number'],
        ]);

        return response()->json(['message' => 'Item successfully uploaded'], 201);
    }
    public function update(Request $request, $id)
    {
    $item = Item::findOrFail($id);

    $validatedData = $request->validate([
        'image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // Opsional
        'item_name' => 'required|string|max:255',
        'category' => 'required|in:otomotive,pakaian,elektronik,lainnya',
        'item_description' => 'required|string',
        'uploaded_by' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone_number' => 'required|string|max:15',
    ]);

    if ($request->hasFile('image')) {
        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }
        $validatedData['image_path'] = $request->file('image')->store('items', 'public');
    } else {
        $validatedData['image_path'] = $item->image_path;
    }

    $item->update($validatedData);

    return response()->json(['message' => 'Item successfully updated'], 200);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json(['message' => 'Item successfully deleted'], 200);
    }
}
