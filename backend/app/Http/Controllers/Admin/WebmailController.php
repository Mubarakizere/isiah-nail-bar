<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WebmailController extends Controller
{
    public function index(Request $request)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        try {
            /** @var \Webklex\PHPIMAP\Client $client */
            $client = app('imap')->account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            
            // Pagination implementation for IMAP is tricky, but the library supports query pagination
            // fetching messages sorted by date desc
            $messages = $folder->query()->leaveUnseen()->setFetchOrder('desc')->paginate(15);
            $data = collect($messages->items())->map(fn ($message) => $this->serializeMessage($message, false));

            return response()->json([
                'success' => true,
                'data' => $data->values(),
                'meta' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Could not connect to the admin webmail inbox.', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Could not connect to the email server. Check the IMAP connection settings and try again.',
            ], 503);
        }
    }

    public function show(Request $request, $uid)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        try {
            $client = app('imap')->account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if (!$message) {
                return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
            }

            // Mark as read
            $message->setFlag(['Seen']);

            return response()->json([
                'success' => true,
                'data' => $this->serializeMessage($message, true),
            ]);
        } catch (\Exception $e) {
            Log::error('Could not load an admin webmail message.', ['uid' => $uid, 'exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Could not load this email. Check the mailbox connection and try again.',
            ], 503);
        }
    }

    public function reply(Request $request, $uid)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        $request->validate([
            'body' => 'required|string|max:50000',
        ]);

        try {
            $client = app('imap')->account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if (!$message) {
                return response()->json(['success' => false, 'message' => 'Original message not found.'], 404);
            }

            $sender = $message->getFrom()->first();
            if (!$sender || empty($sender->mail)) {
                return response()->json(['success' => false, 'message' => 'This email does not have a valid reply address.'], 422);
            }

            $recipient = $sender->mail;
            $originalSubject = trim((string) $message->getSubject());
            $subject = str_starts_with(strtolower($originalSubject), 're:') ? $originalSubject : 'Re: ' . $originalSubject;
            
            // Send email using Laravel Mail
            Mail::raw($request->body, function ($mail) use ($recipient, $subject, $message) {
                $mail->to($recipient)
                     ->subject($subject)
                     ->replyTo(config('mail.from.address'), config('mail.from.name'));
                     
                // In a real generic string reply, we don't have In-Reply-To easily accessible via Mail::raw
                // But this is sufficient for basic reply.
            });

            // Mark as answered in IMAP
            $message->setFlag(['Answered']);

            return response()->json(['success' => true, 'message' => 'Reply sent successfully.']);

        } catch (\Exception $e) {
            Log::error('Could not send admin webmail reply.', ['uid' => $uid, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to send the reply. Check the mail connection and try again.'], 503);
        }
    }

    public function destroy(Request $request, $uid)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        try {
            $client = app('imap')->account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if (!$message) {
                return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
            }

            $message->delete();

            return response()->json(['success' => true, 'message' => 'Email deleted.']);
        } catch (\Exception $e) {
            Log::error('Could not delete an admin webmail message.', ['uid' => $uid, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to delete the email. Check the mailbox connection and try again.'], 503);
        }
    }

    public function downloadAttachment(Request $request, $uid, $attachmentId)
    {
        if ($response = $this->authorizeAdmin($request)) {
            return $response;
        }

        try {
            $client = app('imap')->account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if (!$message) {
                return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
            }

            $attachment = $message->getAttachments()->first(
                fn ($item) => hash_equals((string) $item->getId(), (string) $attachmentId)
            );

            if (!$attachment) {
                return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);
            }

            $filename = $attachment->getName() ?: 'attachment';
            $contentType = $attachment->getContentType() ?: 'application/octet-stream';

            return response()->streamDownload(
                function () use ($attachment) {
                    echo $attachment->getContent();
                },
                $filename,
                ['Content-Type' => $contentType]
            );
        } catch (\Exception $e) {
            Log::error('Could not download an admin webmail attachment.', ['uid' => $uid, 'attachment_id' => $attachmentId, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to download the attachment.'], 503);
        }
    }

    private function authorizeAdmin(Request $request)
    {
        $user = ApiToken::resolve($request->bearerToken() ?: $request->cookie('isaiah_api_token')) ?? Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication is required.'], 401);
        }

        if (!$user->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Administrator access is required.'], 403);
        }

        return null;
    }

    private function serializeMessage($message, bool $includeBody): array
    {
        $sender = $message->getFrom()->first();
        $textBody = trim((string) $message->getTextBody());

        if ($textBody === '' && $message->hasHTMLBody()) {
            $textBody = html_entity_decode(strip_tags((string) $message->getHTMLBody()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $serialized = [
            'uid' => (string) $message->getUid(),
            'sender_name' => $sender?->personal ?: ($sender?->mail ?? 'Unknown sender'),
            'sender_email' => $sender?->mail ?? '',
            'subject' => trim((string) $message->getSubject()) ?: '(No subject)',
            'preview' => mb_substr(preg_replace('/\s+/u', ' ', $textBody) ?: 'No message preview available.', 0, 180),
            'date' => $message->getDate()?->toIso8601String(),
            'seen' => $message->getFlags()->has('seen'),
            'answered' => $message->getFlags()->has('answered'),
            'attachments' => $includeBody ? collect($message->getAttachments())->map(fn ($attachment) => [
                'id' => (string) $attachment->getId(),
                'name' => $attachment->getName() ?: 'Attachment',
                'size' => (int) ($attachment->getSize() ?? 0),
                'content_type' => $attachment->getContentType(),
            ])->values() : [],
        ];

        if ($includeBody) {
            // Return plain text only: email HTML is untrusted content and must not be rendered as markup.
            $serialized['body'] = $textBody;
        }

        return $serialized;
    }
}
