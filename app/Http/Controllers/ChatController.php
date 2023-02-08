<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chat;
use App\ChatLog;
use App\User;
use App\Announcement;
use Auth;
use App\Mail\AnnouncementEmail;
use Validator;
use Pusher\Laravel\Facades\Pusher;
use Illuminate\Support\Facades\Mail;

class ChatController extends Controller
{
    // begin listing view
    public function index()
    {
        $getUsers   =   User::where('id', '!=', Auth::user()->id);
        if (Auth::user()->role_id    ==  3   ||  Auth::user()->role_id    ==  4) {
            $getUsers   =   $getUsers->where('role_id', 2);
        }
        $getUsers   =   $getUsers->get();
        foreach ($getUsers   as  $users) {
            $countUnreadMessages    =   ChatLog::where('sent_to', Auth::user()->id)->where('sent_from', $users->id)->where('is_read', 0)->count();
            $users->total_unread_messages   =   $countUnreadMessages;
        }
        return view('admin.chats.index', compact('getUsers'));
    }
    // end listing view

    // begin get single user chat
    public function getSingleUserChat(Request $request, $id)
    {
        $id             =   $id;
        $AuthUserId     =   Auth::user()->id;
        $otherUserId    =   $id;
        $getChats       =   Chat::where(function ($query) use ($otherUserId) {
            $query->where('sent_to', $otherUserId)
                ->orWhere('sent_from', $otherUserId);
        })->where(function ($query) use ($AuthUserId) {
            $query->where('sent_to', $AuthUserId)
                ->orWhere('sent_from', $AuthUserId);
        })->orderBy('id', 'desc')->get();
        $getUsers   =   User::where('id', '!=', Auth::user()->id);
        if (Auth::user()->role_id    ==  3   ||  Auth::user()->role_id    ==  4) {
            $getUsers   =   $getUsers->where('role_id', 2);
        }
        $getUsers   =   $getUsers->get();
        foreach ($getUsers   as  $users) {
            $countUnreadMessages    =   ChatLog::where('sent_to', Auth::user()->id)->where('sent_from', $users->id)->where('is_read', 0)->count();
            $users->total_unread_messages   =   $countUnreadMessages;
        }
        ChatLog::where('sent_to', Auth::user()->id)->where('sent_from', $id)->update(['is_read' => 1]);
        return view('admin.chats.singleUserChat', compact('getChats', 'getUsers', 'id'));
    }
    // end get single user chat

    // begin send message

    //tayyba

    public function sendMessage(Request $request)
    {

        $getUsers   =   User::where('role_id', 2)->orWhere('role_id', 3)->get();
        $id_array = session()->get('getId');
        $message_save = "";
        foreach ($id_array as $g) {
            $sendMessage = new Chat();
            $sendMessage->message = $request->message;
            //  $sendMessage->sent_to = $request->otherUser;
            $sendMessage->sent_to = $g;


            $sendMessage->sent_from = Auth::user()->id;

            $message_save = $sendMessage->save();
            dd($message_save);

            $announcement = new Announcement();
            $announcement->message = $request->message;
            // $announcement->sent_to = $g;


            // $announcement->sent_from = Auth::user()->id;
            $announcement_save = $announcement->save();

            $msg = $request->message;


            // if($sendMessage->save())
        }  //end foreach


        if ($message_save) {

            foreach ($id_array as $g) {
                $sendChatLog = new ChatLog();
                $sendChatLog->is_read       = 0;
                //    $sendChatLog->sent_to       = $request->otherUser;
                $sendChatLog->sent_to       = $g;

                $sendChatLog->sent_from     = Auth::user()->id;
                $sendChatLog->save();

                // dd($g->email);
                $userEmail   =   User::where('id', $g)->value('email');
                Mail::to($userEmail)->send(new AnnouncementEmail($msg));
            }

            // yaha se................
            // $sendChatLog1 = new ChatLog();
            // $sendChatLog1->is_read      = 1;
            // $sendChatLog1->sent_to      = Auth::user()->id;
            // $sendChatLog1->sent_from    = $request->otherUser;

            // $sendChatLog1->save();

            session()->put('getId', []);

            pusher::trigger('my-channel', 'chat-event', ['message' => $request->message]);
            return response()->json(['success' => $request->message]);
        } else {
            return response()->json(['error' => 'Something went wrong']);
        }



        // if($user->save())
        // {
        //     $getUserId  =   $user->id;
        // }

        // return redirect()->route('AdminOperations.index');
    }

    //tayyba
    // end send message

    public function sendMessageSelectedUser(Request $request)
    {
        // uper masla ha shaid .. waha bhi changes kiye hain..wait m dekhta hun..aja ... nae
        $array = [];
        //  session()->put('getId',[]);

        //  array_push($array,$request->id);
        session()->push('getId', $request->id);
    }

    // begin send file
    public function sendFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,png,jpg,gif,doc,docx,pdf,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        if ($request->hasFile('file')) {
            $image                      =   $request->file('file');
            $path                       =   public_path() . '/chat_images';
            $filename                   =   time() . $image->getClientOriginalName();
            if ($image->move($path, $filename)) {
                $sendMessage                        =   new Chat();
                $sendMessage->sent_to               =   $request->otherUser;
                $sendMessage->sent_from             =   Auth::user()->id;
                $sendMessage->file                  =   $filename;
                if ($image->getClientOriginalExtension() ==  'jpeg' || $image->getClientOriginalExtension() ==  'png' || $image->getClientOriginalExtension() ==  'jpg' || $image->getClientOriginalExtension() ==  'gif' || $image->getClientOriginalExtension() ==  'JPEG' || $image->getClientOriginalExtension() ==  'PNG' || $image->getClientOriginalExtension() ==  'JPG' || $image->getClientOriginalExtension() ==  'GIF') {
                    $sendMessage->type                  =   'Image';
                } else {
                    $sendMessage->type                  =   'Document';
                }
                if ($sendMessage->save()) {
                    $sendChatLog = new ChatLog();
                    $sendChatLog->is_read       = 0;
                    $sendChatLog->sent_to       = $request->otherUser;
                    $sendChatLog->sent_from     = Auth::user()->id;
                    $sendChatLog->save();

                    $sendChatLog1 = new ChatLog();
                    $sendChatLog1->is_read      = 1;
                    $sendChatLog1->sent_to      = Auth::user()->id;
                    $sendChatLog1->sent_from    = $request->otherUser;
                    $sendChatLog1->save();


                    pusher::trigger('my-channel', 'chat-event', ['message' => $request->message]);
                    // return response()->json(['success'=>$request->message]);
                    return redirect()->back()->with('success', $request->message);
                } else {
                    return redirect()->back()->with('error', 'Something went wrong');
                }
            } else {
                return redirect()->back()->with('error', 'Something went wrong');
            }
        } else {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }
    // end send file

    // begin get chat
    public function getUserChat(Request $request)
    {
        $otherUserId    =   $request->otherUser;
        $getOtherUserDetails    =   User::where('id', $otherUserId)->first();
        $AuthUserId     =   Auth::user()->id;
        $getUserChat = Chat::where(function ($query) use ($otherUserId) {
            $query->where('sent_to', $otherUserId)
                ->orWhere('sent_from', $otherUserId);
        })->where(function ($query) use ($AuthUserId) {
            $query->where('sent_to', $AuthUserId)
                ->orWhere('sent_from', $AuthUserId);
        })->orderBy('id', 'asc')
            ->get();
        $getChatMessages    =   '';
        foreach ($getUserChat    as  $userChat) {
            if (!empty($userChat->file)) {
                $getChatFile    =   $userChat->message . "<a href='" . asset('public/chat_images/' . $userChat->file) . "'>" . $userChat->file . "</a>";
            } else {
                $getChatFile    =   $userChat->message;
            }
            $userChat['auth_image']     =   asset('public/user_images/' . Auth::user()->image);
            $userChat['user_image']     =   asset('public/user_images/' . $getOtherUserDetails['image']);
            if (Auth::user()->id ==  $userChat->sent_from) {


                $getChatMessages    .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12' style='float: left;'>"
                    . "<div class='form-group'>"
                    . "<img src='" . $userChat['auth_image'] . "' alt='' class='img-circle' width='30px'>"
                    . "<span style='background-color: #e6e6e6;padding: 5px 10px;border: 1px solid transparent;border-radius: 20px;'>" . $getChatFile . "</span>"
                    . "</div>"
                    . "</div>";
                // "<div class='incoming_msg'>"
                //                     ."<div class='incoming_msg_img'> <img src='".$userChat['auth_image']."' alt='sunil'> </div>"
                //                     ."<div class='received_msg'>"
                //                     ."<div class='received_withd_msg'>"
                //                     ."<p>".$userChat->message."</p>"
                //                     ."<span class='time_date'> 11:01 AM    |    June 9</span></div>"
                //                     ."</div>"
                //                     ."<div><br></div>"
                //                     ."</div>"
                //                     ."<div class='outgoing_msg'>";
            } else {
                $getChatMessages    .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12' style='float: right;width: auto;'>"
                    . "<div class='form-group'>"
                    . "<span style='background-color: #e6e6e6;padding: 5px 10px;border: 1px solid transparent;border-radius: 20px;'>" . $getChatFile . "</span>"
                    . "<img src='" . $userChat['user_image'] . "' alt='' class='img-circle' width='30px'>"
                    . "</div>"
                    . "</div>";
            }
        }
        if (!empty($getUserChat)) {
            return response()->json(['data' => $getChatMessages]);
        } else {
            return response()->json(['error' => 'No record found']);
        }

        // return redirect()->route('AdminOperations.index');
    }
    // end get chat

    // begin get count unread messages
    public function getCountUnreadMessages(Request $request)
    {
        $countUnreadMessages    =   ChatLog::where('sent_to', Auth::user()->id)->where('is_read', 0)->count();
        return response()->json(['data' => $countUnreadMessages]);
    }
    // end get count unread messages




    // for subdomain






}
