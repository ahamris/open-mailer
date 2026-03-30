<?php
namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Suppression;
use App\Models\SubscriptionForm;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    // Public: POST /subscribe/{formId}
    public function subscribe(Request $request, string $formId, MailService $mailService)
    {
        $form = SubscriptionForm::where('active', true)->findOrFail($formId);

        $validated = $request->validate([
            'email' => 'required|email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        if (Suppression::isSuppressed($validated['email'])) {
            return $form->redirect_url ? redirect($form->redirect_url) : response()->json(['message' => 'Subscribed']);
        }

        $contact = Contact::firstOrCreate(
            ['email' => strtolower($validated['email'])],
            ['first_name' => $validated['first_name'] ?? null, 'last_name' => $validated['last_name'] ?? null, 'confirmed' => !$form->double_opt_in]
        );

        $contact->audiences()->syncWithoutDetaching([$form->audience_id]);
        $form->increment('submissions_count');

        if ($form->double_opt_in && !$contact->confirmed) {
            $token = Str::random(64);
            $contact->update(['confirmation_token' => $token]);
            $confirmUrl = url("/subscribe/confirm/{$token}");
            $html = $form->confirmation_html ?? "<p>Please confirm your subscription by clicking the link below:</p><p><a href=\"{$confirmUrl}\">Confirm subscription</a></p>";
            $html = str_replace('{{confirm_url}}', $confirmUrl, $html);

            $mailService->send([
                'from' => config('mail.from.address'),
                'to' => $contact->email,
                'subject' => $form->confirmation_subject,
                'html' => $html,
            ]);
        }

        return $form->redirect_url ? redirect($form->redirect_url) : response()->json(['message' => 'Subscribed successfully']);
    }

    // Public: GET /subscribe/confirm/{token}
    public function confirm(string $token)
    {
        $contact = Contact::where('confirmation_token', $token)->firstOrFail();
        $contact->update(['confirmed' => true, 'confirmed_at' => now(), 'confirmation_token' => null]);
        return response('<h1>Subscription confirmed!</h1><p>You have been added to our mailing list.</p>', 200)->header('Content-Type', 'text/html');
    }

    // Public: GET /unsubscribe/{emailId}
    public function unsubscribe(string $emailId)
    {
        $email = \App\Models\Email::find($emailId);
        if ($email) {
            foreach ($email->to_addresses as $to) {
                Contact::where('email', $to)->update(['unsubscribed' => true]);
            }
        }
        return response('<h1>Unsubscribed</h1><p>You have been removed from our mailing list.</p>', 200)->header('Content-Type', 'text/html');
    }
}
