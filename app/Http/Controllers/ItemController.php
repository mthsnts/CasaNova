<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\ItemRequest;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = Item::with(['category', 'user'])
            ->where('user_id', auth()->id())
            ->get();
        return view('items.index', compact('items'));
    }

    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('items.create', compact('categories'));
    }

    public function store(ItemRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $this->handleImageUpload($request->file('image'));
        }

        $data['user_id'] = auth()->id();
        Item::create($data);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        $categories = \App\Models\Category::all();
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(ItemRequest $request, Item $item)
    {
        $this->authorize('update', $item);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $this->handleImageUpload($request->file('image'));
        }

        $item->update($data);

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully');
    }

    private function handleImageUpload($imageFile)
    {
        // Create a unique filename
        $filename = uniqid() . '.jpg';
        
        // Create an instance of Intervention Image
        $image = Image::make($imageFile)
            ->encode('jpg', 80); // Convert to JPEG with 80% quality

        // Resize the image while maintaining aspect ratio
        $image->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Store the processed image
        $path = 'items/' . $filename;
        Storage::disk('public')->put($path, $image->stream());

        return $path;
    }

    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);
        
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }
        
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully');
    }
} 