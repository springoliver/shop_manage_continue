<?php

namespace App\Http\Employee\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HolidayRequest;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class HolidayRequestController extends Controller
{
    /**
     * Display a listing of the holiday requests for the authenticated employee.
     */
    public function index(): View
    {
        $employee = Auth::guard('employee')->user();
        
        // Get all holiday requests for this employee
        $holidayRequests = HolidayRequest::with('employee')
            ->where('employeeid', $employee->employeeid)
            ->orderBy('requestid', 'DESC')
            ->get()
            ->map(function ($request) {
                return [
                    'requestid' => $request->requestid,
                    'firstname' => $request->employee->firstname ?? '',
                    'lastname' => $request->employee->lastname ?? '',
                    'from_date' => $request->from_date,
                    'to_date' => $request->to_date,
                    'subject' => $request->subject,
                    'status' => $request->status,
                ];
            });
        
        return view('employee.holidayrequest.index', compact('holidayRequests'));
    }

    /**
     * Show the form for creating a new holiday request.
     */
    public function create(): View
    {
        return view('employee.holidayrequest.create');
    }

    /**
     * Store a newly created holiday request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate date format as dd-mm-yyyy
        $request->validate([
            'from_date' => ['required', 'regex:/^\d{2}-\d{2}-\d{4}$/'],
            'to_date' => ['required', 'regex:/^\d{2}-\d{2}-\d{4}$/'],
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        $validated = $request->all();
        
        // Validate to_date is after or equal to from_date
        $fromDateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['from_date']);
        $toDateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['to_date']);
        
        if ($toDateObj->lt($fromDateObj)) {
            return redirect()->back()
                ->withErrors(['to_date' => 'The to date must be after or equal to the from date.'])
                ->withInput();
        }
        
        $employee = Auth::guard('employee')->user();
        
        // Parse dates from dd-mm-yyyy format to Y-m-d H:i:s (matching CI's date('Y-m-d H:i:s', strtotime()))
        // Note: CI uses strtotime() which works with dd-mm-yyyy, but Carbon needs explicit format
        $fromDate = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['from_date'])->format('Y-m-d') . ' 00:00:00';
        $toDate = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['to_date'])->format('Y-m-d') . ' 00:00:00';
        
        HolidayRequest::create([
            'storeid' => $employee->storeid,
            'employeeid' => $employee->employeeid,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => 'Pending',
            'insertdatetime' => now(),
            'insertip' => $request->ip(),
        ]);

        // Match CI behavior: notify both store owner and store manager on new request.
        try {
            $store = Store::with('storeOwner')->find($employee->storeid);

            if ($store) {
                $recipientEmails = collect([
                    $store->storeOwner->emailid ?? null,
                    $store->manager_email ?? null,
                ])
                    ->filter(fn ($email) => !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($recipientEmails)) {
                    $this->sendHolidayRequestNotification($store, $employee, $validated, $recipientEmails);
                }
            }
        } catch (\Throwable $e) {
            // Do not block the user flow if mail transport has issues.
            Log::error('Failed to send holiday request notification email', [
                'employeeid' => $employee->employeeid,
                'storeid' => $employee->storeid,
                'error' => $e->getMessage(),
            ]);
        }
        
        return redirect()->route('employee.holidayrequest.index')
            ->with('success', 'Time of request inserted successfully');
    }

    /**
     * Display the specified holiday request.
     */
    public function show(string $requestid): View|RedirectResponse
    {
        $requestid = base64_decode($requestid);
        $employee = Auth::guard('employee')->user();
        
        $holidayRequest = HolidayRequest::with('employee')
            ->where('requestid', $requestid)
            ->where('employeeid', $employee->employeeid)
            ->firstOrFail();
        
        return view('employee.holidayrequest.view', compact('holidayRequest'));
    }

    /**
     * Show the form for editing the specified holiday request.
     */
    public function edit(string $requestid): View|RedirectResponse
    {
        $requestid = base64_decode($requestid);
        $employee = Auth::guard('employee')->user();
        
        $holidayRequest = HolidayRequest::where('requestid', $requestid)
            ->where('employeeid', $employee->employeeid)
            ->where('status', 'Pending') // Only pending requests can be edited
            ->firstOrFail();
        
        return view('employee.holidayrequest.edit', compact('holidayRequest'));
    }

    /**
     * Update the specified holiday request.
     */
    public function update(Request $request, string $requestid): RedirectResponse
    {
        $requestid = base64_decode($requestid);
        $employee = Auth::guard('employee')->user();
        
        $holidayRequest = HolidayRequest::where('requestid', $requestid)
            ->where('employeeid', $employee->employeeid)
            ->where('status', 'Pending') // Only pending requests can be updated
            ->firstOrFail();
        
        // Validate date format as dd-mm-yyyy
        $request->validate([
            'from_date' => ['required', 'regex:/^\d{2}-\d{2}-\d{4}$/'],
            'to_date' => ['required', 'regex:/^\d{2}-\d{2}-\d{4}$/'],
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        
        $validated = $request->all();
        
        // Validate to_date is after or equal to from_date
        $fromDateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['from_date']);
        $toDateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['to_date']);
        
        if ($toDateObj->lt($fromDateObj)) {
            return redirect()->back()
                ->withErrors(['to_date' => 'The to date must be after or equal to the from date.'])
                ->withInput();
        }
        
        // Parse dates from dd-mm-yyyy format to Y-m-d H:i:s (matching CI's date('Y-m-d H:i:s', strtotime()))
        $fromDate = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['from_date'])->format('Y-m-d') . ' 00:00:00';
        $toDate = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['to_date'])->format('Y-m-d') . ' 00:00:00';
        
        $holidayRequest->update([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'editdatetime' => now(),
            'editip' => $request->ip(),
        ]);
        
        return redirect()->route('employee.holidayrequest.index')
            ->with('success', 'Request details updated successfully.');
    }

    /**
     * Remove the specified holiday request.
     */
    public function destroy(string $requestid): RedirectResponse
    {
        $requestid = base64_decode($requestid);
        $employee = Auth::guard('employee')->user();
        
        $holidayRequest = HolidayRequest::where('requestid', $requestid)
            ->where('employeeid', $employee->employeeid)
            ->where('status', 'Pending') // Only pending requests can be deleted
            ->firstOrFail();
        
        $holidayRequest->delete();
        
        return redirect()->route('employee.holidayrequest.index')
            ->with('success', 'Request has been deleted successfully');
    }

    /**
     * Calendar view of holiday requests.
     */
    public function calendarView(): View
    {
        return view('employee.holidayrequest.calenderview');
    }

    /**
     * Get requests for calendar view (AJAX).
     */
    public function getRequests(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        // Get all holiday requests for this employee
        $holidayRequests = HolidayRequest::with('employee')
            ->where('employeeid', $employee->employeeid)
            ->get();
        
        $colors = [
            'Approved' => 'green',
            'Declined' => 'red',
            'Pending' => 'orange',
        ];
        
        $dataRequests = [];
        foreach ($holidayRequests as $r) {
            if ($r->employee) {
                $dataRequests[] = [
                    'title' => $r->employee->firstname . ' ' . $r->employee->lastname,
                    'color' => $colors[$r->status] ?? 'blue',
                    'end' => $r->to_date->format('Y-m-d'),
                    'start' => $r->from_date->format('Y-m-d'),
                ];
            }
        }
        
        return response()->json(['events' => $dataRequests]);
    }

    /**
     * Send request notification (Laravel mailer first, PHPMailer fallback).
     */
    private function sendHolidayRequestNotification(Store $store, $employee, array $validated, array $recipientEmails): void
    {
        $storeName = $store->store_name ?? config('app.name');
        $employeeName = trim(($employee->firstname ?? '') . ' ' . ($employee->lastname ?? ''));
        $subjectLine = $storeName . ' - Employee Holiday Request - ';
        $requestedOn = Carbon::now()->format('Y-m-d');
        $mailBody = $this->buildHolidayRequestMailBody($requestedOn, $employeeName, $validated);

        $fromAddress = $store->store_email ?: config('mail.from.address');
        $fromName = $store->store_name ?: config('app.name');

        try {
            Mail::html($mailBody, function ($message) use ($recipientEmails, $subjectLine, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName)
                    ->to($recipientEmails)
                    ->subject($subjectLine);
            });
            return;
        } catch (\Throwable $mailError) {
            Log::warning('Laravel mail failed, trying PHPMailer fallback', [
                'storeid' => $store->storeid,
                'error' => $mailError->getMessage(),
            ]);
        }

        if (empty($store->store_email) || empty($store->store_email_pass)) {
            throw new \RuntimeException('Store SMTP credentials are missing for PHPMailer fallback.');
        }

        $phpMailer = new PHPMailer(true);
        try {
            $phpMailer->isSMTP();
            $phpMailer->Host = 'smtp.gmail.com';
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $store->store_email;
            $phpMailer->Password = $store->store_email_pass;
            $phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $phpMailer->Port = 465;
            $phpMailer->SMTPDebug = false;

            $phpMailer->setFrom($store->store_email, $storeName);
            foreach ($recipientEmails as $recipientEmail) {
                $phpMailer->addAddress($recipientEmail);
            }

            $phpMailer->isHTML(true);
            $phpMailer->Subject = $subjectLine;
            $phpMailer->Body = $mailBody;
            $phpMailer->send();
        } catch (PHPMailerException $e) {
            throw new \RuntimeException('PHPMailer fallback failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function buildHolidayRequestMailBody(string $requestedOn, string $employeeName, array $validated): string
    {
        $mailBody = '<html><body><table width="100%" border="1" style="border-collapse: collapse; border: 1px solid black; padding: 1%; margin: 1%; border-spacing: 0; width: 100%;">';
        $mailBody .= '<tr><td style="padding: 1%; margin: 1%;">Requested on:</td><td style="padding: 1%; margin: 1%;">' . e($requestedOn) . '</td></tr>';
        $mailBody .= '<tr><td style="padding: 1%; margin: 1%;">Employee Name:</td><td style="padding: 1%; margin: 1%;">' . e($employeeName) . '</td></tr>';
        $mailBody .= '<tr><td style="padding: 1%; margin: 1%;">From:</td><td style="padding: 1%; margin: 1%;">' . e($validated['from_date']) . '</td></tr>';
        $mailBody .= '<tr><td style="padding: 1%; margin: 1%;">To:</td><td style="padding: 1%; margin: 1%;">' . e($validated['to_date']) . '</td></tr>';
        $mailBody .= '<tr><td style="padding: 1%; margin: 1%;">Subject:</td><td style="padding: 1%; margin: 1%;">' . e($validated['subject']) . '</td></tr>';
        $mailBody .= '<tr><td style="padding: 1%; margin: 1%;">Description:</td><td style="padding: 1%; margin: 1%;">' . nl2br(e($validated['description'])) . '</td></tr>';
        $mailBody .= '</table></body></html>';
        return $mailBody;
    }
}

