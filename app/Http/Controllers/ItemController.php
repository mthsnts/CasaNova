<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;  // Add this import

class ItemController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with(['category', 'user'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('items.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        $categories = Category::all();
        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
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

    /**
     * Handle image upload and processing.
     */
    private function handleImageUpload($image)
    {
        $filename = time() . '_' . $image->getClientOriginalName();
        
        // Create image instance
        $img = Image::make($image->getRealPath());
        
        // Resize if width is greater than 800px
        if ($img->width() > 800) {
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Convert to JPEG and compress
        $img->encode('jpg', 80);
        
        // Save the processed image
        $path = 'items/' . $filename;
        Storage::disk('public')->put($path, $img->stream());
        
        return $path;
    }
} 