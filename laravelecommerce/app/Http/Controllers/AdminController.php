<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view("admin.index");
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view("admin.brands", compact('brands'));
    }

    public function add_brand()
    {
        return view("admin.brand-add");
    }

    public function add_brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;


        $this->GenerateBrandThumbailImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Record has been added successfully !');
    }

    public function edit_brand($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function update_brand(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = $request->slug;
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;


            $this->GenerateBrandThumbailImage($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Record has been updated successfully !');
    }



    public function delete_brand($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Record has been deleted successfully !');
    }


    public function GenerateBrandThumbailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }





    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view("admin.categories", compact('categories'));
    }


    public function add_category()
    {
        return view("admin.category-add");
    }


    public function add_category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateCategoryThumbailImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Record has been added successfully !');
    }

    public function edit_category($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }


    public function delete_category($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Record has been deleted successfully !');
    }






    public function update_category(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = $request->slug;
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;


            $this->GenerateCategoryThumbailImage($image, $file_name);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Record has been updated successfully !');
    }


    public function GenerateCategoryThumbailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }





    public function add_product()
    {
        $categories = Category::Select('id', 'name')->orderBy('name')->get();
        $brands = Brand::Select('id', 'name')->orderBy('name')->get();


        return view("admin.product-add", compact('categories', 'brands'));
    }


    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }
            if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
            }

            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();

            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $oldGImages = explode(",", $product->images);
            foreach ($oldGImages as $gimage) {
                if (File::exists(public_path('uploads/products') . '/' . trim($gimage))) {
                    File::delete(public_path('uploads/products') . '/' . trim($gimage));
                }

                if (File::exists(public_path('uploads/products/thumbails') . '/' . trim($gimage))) {
                    File::delete(public_path('uploads/products/thumbails') . '/' . trim($gimage));
                }
            }
            $allowedfileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $check = in_array($gextension, $allowedfileExtension);
                if ($check) {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfilename);
                    array_push($gallery_arr, $gfilename);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Record has been added successfully !');
    }

    public function products()
    {
        $products = Product::OrderBy('created_at', 'DESC')->paginate(10);
        return view("admin.products", compact('products'));
    }


    public function edit_product($id)
    {
        $product = Product::find($id);
        $categories = Category::Select('id', 'name')->orderBy('name')->get();
        $brands = Brand::Select('id', 'name')->orderBy('name')->get();


        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }


    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPathThumbnails = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540, 689, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnails . '/' . $imageName);
    }


    public function delete_product($id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Record has been deleted successfully !');
    }


    public function update_product(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $request->id,
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'sometimes|mimes:png,jpg,jpeg|max:2048' // image is not always required
        ]);

        $product = Product::findOrFail($request->id);

        $product->name = $request->name;
        $product->slug = $request->slug; // using slug from request
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {
            // Delete the old image
            if ($product->image) {
                Storage::disk('public_uploads')->delete($product->image);
            }

            $file_extension = $request->file('image')->extension();
            $file_name = $current_timestamp . '.' . $file_extension;
            $path = $request->image->storeAs('products', $file_name, 'public_uploads');
            $product->image = $path;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            // Delete old gallery images
            if ($product->images) {
                $old_gallery_images = explode(', ', $product->images);
                foreach ($old_gallery_images as $old_image) {
                    Storage::disk('public_uploads')->delete($old_image);
                }
            }

            $allowedfileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $check = in_array($gextension, $allowedfileExtension);
                if ($check) {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $gpath = $file->storeAs('products', $gfilename, 'public_uploads');
                    array_push($gallery_arr, $gpath);
                    $counter++;
                }
            }
            $gallery_images = implode(', ', $gallery_arr);
        }
        $product->images = $gallery_images;

        $product->save();
        return redirect()->route('admin.products')->with('status', 'Record has been updated successfully!');
    }
}
