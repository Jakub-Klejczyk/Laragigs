<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    public function index() {
        return view('listings.index', [
            'heading' => 'Latest listings',
            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)
        ]);
    }

    public function show($id) {
        $listing = Listing::find($id);

        if($listing) {
            return view('listings.show', [
                'heading' => 'Listing ' . $id,
                'listing' => $listing
            ]);
        } else {
            abort('404');
        }
    }

    public function create() {
        return view('listings.create');
    }

    public function store(Request $request) {
    $formFields = $request->validate([
        'title' => 'required',
        'company' => ['required', Rule::unique('listings', 'company')],
        'location' => 'required',
        'website' => 'required',
        'email' => ['required', 'email'],
        'tags' => 'required',
        'description' => 'required'
    ]);

    if($request->hasFile('logo')) {
        $formFields['logo'] = $request->file('logo')->store('logos', 'public');
    }

    $formFields['user_id'] = auth()->id();

    Listing::create($formFields);
        

    return redirect('/')->with('message', 'Listing created successfully!');
    }

    public function edit(Listing $listing) {
        return view('listings.edit', ['listing' => $listing]);
    }

    public function update(Request $request, Listing $listing) {

        if($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Acrion'); 
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
    
        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }
    
    
        $listing->update($formFields);
            
    
        return back()->with('message', 'Listing updated successfully!');
    }

    public function destroy(Listing $listing) {
        
        if($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Acrion'); 
        }

        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted succesfully!');
    }

    public function manage() {
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
