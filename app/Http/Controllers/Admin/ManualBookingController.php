<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Provider;
use App\Models\Service;
use App\Notifications\BookingCreatedManually;
use Illuminate\Http\Request;

class ManualBookingController extends Controller
{
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $providers = Provider::where('active', true)->orderBy('name')->get();
        $services = Service::where('approved', true)->orderBy('name')->get();

        return view('admin.bookings.manual-create', compact('customers', 'providers', 'services'));
    }

    public function store(Request $request)
    {
        // Handle new customer creation
        if ($request->customer_id === 'new') {
            $request->validate([
                'new_customer_name' => 'required|string|max:255',
                'new_customer_email' => 'required|email|unique:customers,email',
                'new_customer_phone' => 'nullable|string',
            ]);

            $customer = Customer::create([
                'name' => $request->new_customer_name,
                'email' => $request->new_customer_email,
                'phone' => $request->new_customer_phone,
            ]);

            $customerId = $customer->id;
        } else {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
            ]);
            $customerId = $request->customer_id;
        }

        // Validate the rest of the booking data
        $validated = $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'payment_option' => 'required|in:full,deposit',
            'deposit_amount' => 'required_if:payment_option,deposit|numeric|min:0',
            'notes' => 'nullable|string',
            'send_email' => 'boolean',
        ]);

        // Create booking - set service_id to null since we use many-to-many
        $booking = Booking::create([
            'customer_id' => $customerId,
            'provider_id' => $validated['provider_id'],
            'service_id' => null, // Set to null, we use many-to-many relationship
            'date' => $validated['date'],
            'time' => $validated['time'],
            'payment_option' => $validated['payment_option'],
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'notes' => $validated['notes'],
            'status' => 'pending', // Use 'pending' status
            'reference' => 'MAN-' . strtoupper(uniqid()),
        ]);

        // Attach services via pivot table
        $booking->services()->attach($validated['services']);

        // Send email notification if requested
        if ($request->boolean('send_email', true)) {
            $customer = Customer::find($customerId);
            try {
                $customer->notify(new BookingCreatedManually($booking));
                $successMessage = 'Booking created successfully! Confirmation email sent to customer.';
            } catch (\Exception $e) {
                $successMessage = 'Booking created successfully! However, email could not be sent: ' . $e->getMessage();
            }
        } else {
            $successMessage = 'Booking created successfully!';
        }

        return redirect()->route('dashboard.admin.bookings')->with('success', $successMessage);
    }
}
