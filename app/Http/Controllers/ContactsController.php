<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactsController extends Controller
{
    public function getContacts(){
        $contacts = User::where('id', '!=', auth()->id())->get();

        $unreadIds = Message::select(\DB::raw('`from` as sender_id, count(`from`) as messages_count'))
            ->where('to', auth()->id())
            ->where('read', false)
            ->groupBy('from')
            ->get();

        $contacts = $contacts->map(function($contacts) use ($unreadIds) {
            $contactUnread = $unreadIds->where('sender_id', $contacts->id)->first();

            $contacts->unread = $contactUnread ? $contactUnread->messages_count : 0;

            return $contacts;
        });

        return response()->json($contacts);
    }

    public function getMessageFor($id){

       // Message::where('from', $id)->where('to', auth()->id())->update(['read' => true]);

        $message = Message::where(function($q) use ($id){
            $q->where('from', auth()->id());
            $q->where('to', $id);
        })->orWhere(function($q) use ($id) {
            $q->where('from', $id);
            $q->where('to', auth()->id());
        })->get();

        return response()->json($message);
    }

    public function send(Request $request){

        $message = Message::create([
            'from' => auth()->id(),
            'to' => $request->contact_id,
            'text' => $request->text
        ]);

        broadcast(new NewMessage($message));

        return response()->json($message);
    }
}
