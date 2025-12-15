<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;

class WebmailController extends Controller
{
    public function index()
    {
        try {
            $client = Client::account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            
            // Pagination implementation for IMAP is tricky, but the library supports query pagination
            // fetching messages sorted by date desc
            $messages = $folder->query()->leaveUnseen()->setFetchOrder('desc')->paginate(15);

            return view('admin.webmail.index', compact('messages'));
        } catch (\Exception $e) {
            return back()->with('error', 'Could not connect to email server: ' . $e->getMessage());
        }
    }

    public function show($uid)
    {
        try {
            $client = Client::account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if (!$message) {
                return redirect()->route('admin.webmail.index')->with('error', 'Message not found.');
            }

            // Mark as read
            $message->setFlag(['Seen']);

            return view('admin.webmail.show', compact('message'));
        } catch (\Exception $e) {
             return redirect()->route('admin.webmail.index')->with('error', 'Error loading message: ' . $e->getMessage());
        }
    }

    public function reply(Request $request, $uid)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        try {
            $client = Client::account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if (!$message) {
                 return back()->with('error', 'Original message not found.');
            }
            
            $recipient = $message->getFrom()[0]->mail;
            $subject = 'Re: ' . $message->getSubject();
            
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

            return back()->with('success', 'Reply sent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reply: ' . $e->getMessage());
        }
    }

    public function destroy($uid)
    {
        try {
            $client = Client::account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessageByUid($uid);

            if ($message) {
                $message->delete();
            }

            return redirect()->route('admin.webmail.index')->with('success', 'Message deleted.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete message: ' . $e->getMessage());
        }
    }
}
