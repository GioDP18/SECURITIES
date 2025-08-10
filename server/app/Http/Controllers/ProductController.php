<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Utils\ObfuscationUtil;

class ProductController extends Controller
{
    private $obfuscationUtil;
    public function __construct(ObfuscationUtil $obfuscationUtil){
        $this->obfuscationUtil = $obfuscationUtil;
    }

    /**
     * Display a listing of the products with their images.
     */
    public function index()
    {
        $products = Product::with('images')->get();
        return response()->json($products);
    }

    /**
     * Store a newly created product (Already working - unchanged)
     */
    public function store(Request $request)
    {
        try {
            $payload = $this->obfuscationUtil->decryptPayload(
                $request->input('data'),
                $request->header('X-Signature')
            );

            if (empty($payload['owner']) || empty($payload['product'])) {
                return response()->json(['error' => 'Invalid payload data'], 400);
            }

            $product = Product::create([
                'owner' => $payload['owner'],
                'product' => $payload['product'],
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('', 'uploaded');
                    $product->images()->create([
                        'path' => $path
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $product->load('images')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Display a specific product with images.
     */
    public function show($id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Update basic fields
        $product->update([
            'owner' => $request->input('owner', $product->owner),
            'product' => $request->input('product', $product->product),
        ]);

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('', 'uploaded');
                $product->images()->create(['path' => $path]);
            }
        }

        // Remove specific images if requested
        if ($request->filled('remove_images')) {
            $removeIds = $request->input('remove_images');
            $images = ProductImage::whereIn('id', $removeIds)->get();
            foreach ($images as $image) {
                Storage::disk('uploaded')->delete($image->path);
                $image->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product->load('images')
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('uploaded')->delete($image->path);
        }

        // Delete product (cascade will remove images from DB)
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
