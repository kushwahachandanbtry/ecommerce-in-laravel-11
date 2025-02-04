<?php

namespace App\Http\Controllers;

use App\Models\Brands;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\OrderItem;
use App\Models\Slide;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

use function Illuminate\Log\log;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = DB::select("SELECT sum(total) As TotalAmount,
                                            sum(if(status='ordered', total, 0)) AS TotalOrderedAmount,
                                            sum(if(status='delivered', total, 0)) AS TotalDeliveredAmount,
                                            sum(if(status='canceled', total, 0)) As TotalCanceledAmount,
                                            Count(*) AS Total,
                                            sum(if(status='ordered', 1, 0)) AS TotalOrdered,
                                            sum(if(status='delivered', 1, 0)) AS TotalDelivered,
                                            sum(if(status='canceled', 1, 0)) AS TotalCanceled
                                            FROM Orders
                                            ");

        $monthlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
                                            IFNULL(D.TotalAmount,0) AS TotalAmount,
                                            IFNULL(D.TotalOrderedAmount,0) AS TotalOrderedAmount,
                                            IFNULL(D.TotalDeliveredAmount,0) AS TotalDeliveredAmount,
                                            IFNULL(D.TotalCanceledAmount,0) AS TotalCanceledAmount FROM month_names M
                                            LEFT JOIN (SELECT DATE_FORMAT(created_at, '%b') AS MonthName,
                                            MONTH(created_at) AS MonthNo,
                                            sum(total) as TotalAmount,
                                            sum(if(status='ordered',total,0)) AS TotalOrderedAmount,
                                            sum(if(status='delivered',total,0)) AS TotalDeliveredAmount,
                                            sum(if(status='canceled',total,0)) AS TotalCanceledAmount
                                            FROM Orders WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
                                            ORDER BY MONTH(created_at)) D ON D.MonthNo=M.id");

        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $OrderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');

        return view('admin.index', compact('orders', 'dashboardDatas','AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
    }


    /********************************************************************************************************************
     ********************************************* BRAND ACTIVITY ******************************************************
     *******************************************************************************************************************/

    /**
     * Function helps to redirect to barnds page
     * @return \Illuminate\Contracts\View\View
     */
    public function brands()
    {
        $brands = Brands::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    /**
     * Function help to add Brands
     * @return \Illuminate\Contracts\View\View
     */
    public function add_brand()
    {
        return view('admin.brand-add');
    }

    /**
     * Function helps to store the brands in database
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = new Brands();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateBrandsThumbnailsImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brands has beed added successfully!');

    }

    /**
     * Function to help edit brands
     * @param mixed $id
     * @return \Illuminate\Contracts\View\View
     */
    public function brand_edit($id)
    {
        $brand = Brands::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    /**
     * Function helps to update the editable brands
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = Brands::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandsThumbnailsImage($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brands has beed Updated successfully!');

    }

    /**
     * Function helps to delte the brands
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function brand_delete($id)
    {
        $brand = Brands::find($id);
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect(route('admin.brands'))->with('status', 'Brands has beed deleted successfully!');
    }

    /**
     * Function helps to display the brnads thumbnails images
     * @param mixed $image
     * @param mixed $imageName
     * @return void
     */
    public function GenerateBrandsThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRation();
        })->save($destinationPath . '/' . $imageName);
    }

    /********************************************************************************************************************
     ********************************************* /BRAND ACTIVITY ******************************************************
     *******************************************************************************************************************/

    /********************************************************************************************************************
     ********************************************* CATEGOTY ACTIVITY ****************************************************
     *******************************************************************************************************************/
    
    public function categories() 
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.catogories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = new Category();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateCategoriesThumbnailsImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.categories')->with('status', 'Category has beed added successfully!');
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandsThumbnailsImage($image, $file_name);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has beed Updated successfully!');

    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }
        $category->delete();
        return redirect(route('admin.categories'))->with('status', 'Category has beed deleted successfully!');
    }
    public function GenerateCategoriesThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRation();
        })->save($destinationPath . '/' . $imageName);
    }

    /********************************************************************************************************************
     ********************************************* /CATEGORY ACTIVITY ******************************************************
     *******************************************************************************************************************/

    /********************************************************************************************************************
     ********************************************* PRODUCT ACTIVITY ****************************************************
     *******************************************************************************************************************/

    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brands::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }

    public function product_store( Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpeg,jpg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required',
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
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if( $request->hasFile('image') )
        {
            $image = $request->file('image');
            $imageName = $current_timestamp .'.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if( $request->hasFile('images'))
        {
            $allowedFileExtension = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach( $files as $file ) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);
                if( $gcheck )
                {
                    $gfileName = $current_timestamp . '-' . $counter . '.' . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfileName );
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Prodcut has been added successfully!');
    }

    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thubmnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRation();
        })->save($destinationPath . '/' . $imageName);

        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRation();
        })->save($destinationPathThumbnail . '/' . $imageName);
    }

    public function product_edit( $id )
    {
        $product = Product::find( $id );
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brands::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,'.$request->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:png,jpeg,jpg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required',
        ]);

        $product = Product::find( $request->id);
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
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if( $request->hasFile('image') )
        {
            if( File::exists(public_path('uploads/products').'/'.$product->image))
            {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }

            if( File::exists(public_path('uploads/products/thubmnails').'/'.$product->image))
            {
                File::delete(public_path('uploads/products/thubmnails').'/'.$product->image);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp .'.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if( $request->hasFile('images'))
        {
            foreach(explode(',', $product->images) as $ofile ) 
            {
                if( File::exists(public_path('uploads/products').'/'.$ofile))
            {
                File::delete(public_path('uploads/products').'/'.$ofile);
            }

            if( File::exists(public_path('uploads/products/thubmnails').'/'.$ofile))
            {
                File::delete(public_path('uploads/products/thubmnails').'/'.$ofile);
            }
            }
            $allowedFileExtension = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach( $files as $file ) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);
                if( $gcheck )
                {
                    $gfileName = $current_timestamp . '-' . $counter . '.' . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfileName );
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
            $product->images = $gallery_images;
        }
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product has been update successfully!');
    }

    public function product_delete( $id )
    {
        $product = Product::find($id);
        if( File::exists(public_path('uploads/products').'/'.$product->image))
        {
            File::delete(public_path('uploads/products').'/'.$product->image);
        }

        if( File::exists(public_path('uploads/products/thubmnails').'/'.$product->image))
        {
            File::delete(public_path('uploads/products/thubmnails').'/'.$product->image);
        }

        foreach(explode(',', $product->images) as $ofile ) 
        {
            if( File::exists(public_path('uploads/products').'/'.$ofile))
            {
                File::delete(public_path('uploads/products').'/'.$ofile);
            }

            if( File::exists(public_path('uploads/products/thubmnails').'/'.$ofile))
            {
                File::delete(public_path('uploads/products/thubmnails').'/'.$ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product has been deleted successfully!');
    }

    public function coupons()
    {
        $coupons = Coupon::orderby("expiry_date", "DESC")->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {
        return view('admin.coupon-add');
    }

    public function coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status', 'Coupons has been added successfully!!');
    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }

    public function coupon_update(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status', 'Coupons has been updated successfully!!');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->back()->with('status', 'Coupon has been deleted successfully!');
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if($request->order_status == 'delivered')
        {
            $order->delivered_date = Carbon::now();
        }
        else if($request->order_status == 'canceled')
        {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        if($request->order_status == 'delivered')
        {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with('status', 'Status changed successfully!');
    }

    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }

    public function slide_add()
    {
        return view('admin.slides-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'status' => 'required',
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extension;
        $this->GenerateSlideThumbnailImage($image, $file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slides added successfully!');
    }

    public function GenerateSlideThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400, 690, "top");
        $img->resize(400, 690, function($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slides-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'status' => 'required',
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if( $request->hasFile('image'))
        {
            if( File::exists(public_path('uploads/assets'). '/' . $request->image))
            {
                File::delete(public_path('uploads/assets'). '/' . $request->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extension;
            $this->GenerateSlideThumbnailImage($image, $file_name);
            $slide->image = $file_name;
        }
        
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slides updated successfully!');
    }

    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if( File::exists(public_path('uploads/slides')). '/' . $slide->image)
        {
            File::delete(public_path('uploads/slides'). '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide deleted successfully!');
    }

    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }

    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with('status', 'Message has been deleted successfully!');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }

    public function settings()
    {
        $user = User::where('id', Auth::user()->id)->first();
        return view('admin.settings', compact('user'));
    }

    public function update_settings(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'old_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'new_password.confirmed' => 'New password and confirm password do not match.',
            'old_password.required_with' => 'Old password is required when setting a new password.',
        ]);

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('mobile')) {
            $user->mobile = $request->mobile;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        // Handle password update
        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return back()->with('error', 'Old password is incorrect.');
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}
