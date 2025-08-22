<?php

namespace DevsFort\Pigeon\Chat\Http\Controllers;

use DevsFort\Pigeon\Chat\Events\PrivateMessageEvent;
use DevsFort\Pigeon\Chat\Events\UserStatusEvent;
use DevsFort\Pigeon\Chat\Events\TypingIndicatorEvent;
use DevsFort\Pigeon\Chat\Events\MessageSeenEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use DevsFort\Pigeon\Chat\Models\Message;
use DevsFort\Pigeon\Chat\Models\Favorite;
use DevsFort\Pigeon\Chat\Models\Group;
use DevsFort\Pigeon\Chat\Models\GroupMember;
use DevsFort\Pigeon\Chat\Facade\Chat as DevsFort;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class MessagesController extends Controller
{
  

    /**
     * Returning the view of the app with the required data.
     *
     * @param int $id
     * @return void
     */
    public function index($id = null)
    {

        $route = (in_array(\Request::route()->getName(), ['user', config('devschat.path')]))
            ? 'user'
            : \Request::route()->getName();

        // Use customizable service to get users
        $chatService = app(config('devschat.customization.services.chat_service', 'DevsFort\Pigeon\Chat\Library\DevsFortChat'));
        $users = $chatService->getUsersForChat(true);
        
        // Get user's groups using customizable service
        $groups = $chatService->getUserGroups(auth()->id());
        
        // prepare id
        return view('DevsFort::pages.app', [
            'id' => ($id == null) ? 0 : $route . '_' . $id,
            'route' => $route,
            'users' => $users,
            'groups' => $groups,
            'type' => 'user',
            'messengerColor' => Auth::user()->messenger_color,
            'dark_mode' => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
        ]);
    }


    /**
     * Fetch data by id for (user/group)
     *
     * @param Request $request
     * @return collection
     */
    public function idFetchData(Request $request)
    {
        $type = $request['type'] ?? 'user';
        $id = $request['id'];
        
        if ($type == 'user') {
            // User data
            $fetch = User::where('id', $id)->first();
            $favorite = DevsFort::inFavorite($id);
            $user_avatar = asset('/storage/' . config('devschat.user_avatar.folder') . '/' . $fetch->avatar);
        } elseif ($type == 'group') {
            // Group data
            $fetch = Group::with(['members', 'creator'])->find($id);
            $favorite = DevsFort::inGroupFavorite($id);
            $user_avatar = $fetch->avatar_url ?? asset('/storage/' . config('devschat.group_avatar.folder', 'group_avatars') . '/default.png');
        } else {
            return Response::json([
                'error' => 'Invalid type specified'
            ], 400);
        }

        // send the response
        return Response::json([
            'favorite' => $favorite,
            'fetch' => $fetch,
            'user_avatar' => $user_avatar,
        ]);
    }

    /**
     * This method to make a links for the attachments
     * to be downloadable.
     *
     * @param string $fileName
     * @return void
     */
    public function download($fileName)
    {
        $path = storage_path() . '/app/public/' . config('devschat.attachments.folder') . '/' . $fileName;
        if (file_exists($path)) {
            return Response::download($path, $fileName);
        } else {
            return abort(404, "Sorry, File does not exist in our server or may have been deleted!");
        }
    }

    /**
     * Send a message to database
     *
     * @param Request $request
     * @return JSON response
     */
    public function send(Request $request)
    {
        // Check if user can send message using customizable service
        $chatService = app(config('devschat.customization.services.chat_service', 'DevsFort\Pigeon\Chat\Library\DevsFortChat'));
        
        if (!$chatService->canUserMessage(auth()->id(), $request['id'])) {
            return Response::json([
                'status' => '403',
                'error' => 1,
                'error_msg' => 'You are not allowed to send messages to this user.',
            ], 403);
        }
        
        // Validate message using customizable validator
        $customValidator = config('devschat.customization.message_validator');
        if ($customValidator && class_exists($customValidator)) {
            $validator = new $customValidator();
            $validation = $validator->validate($request->all());
            if (!$validation['valid']) {
                return Response::json([
                    'status' => '422',
                    'error' => 1,
                    'error_msg' => implode(', ', $validation['errors']),
                ], 422);
            }
        }
        
        // default variables
        $error_msg = $attachment = $attachment_title = null;

        // if there is attachment [file]
        if ($request->hasFile('file')) {
            // allowed extensions
            $allowed_images = DevsFort::getAllowedImages();
            $allowed_files  = DevsFort::getAllowedFiles();
            $allowed        = array_merge($allowed_images, $allowed_files);

            $file = $request->file('file');
            // if size less than 150MB
            if ($file->getSize() < 150000000) {
                if (in_array($file->getClientOriginalExtension(), $allowed)) {
                    // get attachment name
                    $attachment_title = $file->getClientOriginalName();
                    // upload attachment and store the new name
                    $attachment = Str::uuid() . "." . $file->getClientOriginalExtension();
                    
                    // Use the configured storage disk and folder
                    $disk = config('devschat.attachments.disk', 'public');
                    $folder = config('devschat.attachments.folder', 'attachments');
                    
                    // Store file using the correct disk
                    $file->storeAs($folder, $attachment, $disk);
                } else {
                    $error_msg = "File extension not allowed!";
                }
            } else {
                $error_msg = "File size is too long!";
            }
        }

        if (!$error_msg) {
            // send to database
            $messageID = mt_rand(9, 999999999) + time();
            DevsFort::newMessage([
                'id' => $messageID,
                'type' => $request['type'],
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'body' => encrypt(trim(htmlentities($request['message']))),
                'attachment' => ($attachment) ? $attachment . ',' . $attachment_title : null,
            ]);

            // fetch message to send it with the response
            $messageData = DevsFort::fetchMessage($messageID);

            $data = [
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'message' => DevsFort::messageCard($messageData, 'default')
            ];
            // send to user using events with proper message type
            $messageType = $request['type'] === 'group' ? 'group' : 'user';
            
            \Log::info('Broadcasting message event:', [
                'messageType' => $messageType,
                'data' => $data,
                'channel' => $messageType === 'group' ? 'group-chat' : 'user-chat'
            ]);
            
            try {
                event(new PrivateMessageEvent($data, $messageType));
                \Log::info('Message event fired successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to fire message event: ' . $e->getMessage());
            }


        }

        // send the response
        return Response::json([
            'status' => '200',
            'error' => $error_msg ? 1 : 0,
            'error_msg' => $error_msg,
            'message' => DevsFort::messageCard(@$messageData),
            'tempID' => $request['temporaryMsgId'],
        ]);
    }

    /**
     * fetch [user/group] messages from database
     *
     * @param Request $request
     * @return JSON response
     */
    public function fetch(Request $request)
    {
        // messages variable
        $allMessages = null;

        // fetch messages
        $messageType = $request['type'] ?? 'user';
        $query = DevsFort::fetchMessagesQuery($request['id'], $messageType)->orderBy('created_at', 'asc');
        $messages = $query->get();

        // if there is a messages
        if ($query->count() > 0) {
            foreach ($messages as $message) {
                $allMessages .= DevsFort::messageCard(
                    DevsFort::fetchMessage($message->id)
                );
            }
            // send the response
            return Response::json([
                'count' => $query->count(),
                'messages' => $allMessages,
            ]);
        }
        // send the response
        return Response::json([
            'count' => $query->count(),
            'messages' => '<p class="message-hint"><span>Say \'hi\' and start messaging</span></p>',
        ]);
    }

    /**
     * Make messages as seen
     *
     * @param Request $request
     * @return void
     */
    public function seen(Request $request)
    {
        $messageType = $request['type'] ?? 'user';
        
        // make as seen
        $seen = DevsFort::makeSeen($request['id'], $messageType);
        
        // Broadcast seen event
        if ($seen) {
            if ($messageType === 'user') {
                // For individual messages, we need to find who sent the messages that were marked as seen
                // Look for the most recent message from this user to understand the relationship
                $recentMessage = Message::where('to_id', Auth::user()->id)
                    ->where('from_id', $request['id'])
                    ->where('type', 'user')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                \Log::info('MessageSeenEvent Debug:', [
                    'recentMessage' => $recentMessage,
                    'request_id' => $request['id'],
                    'auth_user_id' => Auth::user()->id,
                    'messageType' => $messageType
                ]);
                
                if ($recentMessage) {
                    $data = [
                        'from_id' => $recentMessage->from_id,  // The person who sent the message
                        'to_id' => Auth::user()->id,           // The person who marked it as seen
                        'seen' => true
                    ];
                    \Log::info('Broadcasting MessageSeenEvent:', $data);
                    event(new MessageSeenEvent($data, $messageType));
                } else {
                    \Log::warning('No recent message found for seen event');
                }
            } else {
                // For group messages
                $data = [
                    'from_id' => Auth::user()->id,  // The person who marked it as seen
                    'to_id' => $request['id'],      // The group ID
                    'seen' => true
                ];
                \Log::info('Broadcasting Group MessageSeenEvent:', $data);
                event(new MessageSeenEvent($data, $messageType));
            }
        }
        
        // send the response
        return Response::json([
            'status' => $seen,
            'type' => $messageType,
        ], 200);
    }

    /**
     * Get contacts list
     *
     * @param Request $request
     * @return JSON response
     */
    public function getContacts(Request $request)
    {
        // get all users that received/sent message from/to [Auth user]
        $users = Message::join('users',  function ($join) {
            $join->on('messages.from_id', '=', 'users.id')
                ->orOn('messages.to_id', '=', 'users.id');
        })
            ->where('messages.from_id', Auth::user()->id)
            ->orWhere('messages.to_id', Auth::user()->id)
            ->orderBy('messages.created_at', 'desc')
            ->get()
            ->unique('id');

        if ($users->count() > 0) {
            // fetch contacts
            $contacts = null;
            foreach ($users as $user) {
                if ($user->id != Auth::user()->id) {
                    // Get user data
                    $userCollection = User::where('id', $user->id)->first();
                    $contacts .= DevsFort::getContactItem($request['messenger_id'], $userCollection);
                }
            }
        }

        // send the response
        return Response::json([
            'contacts' => $users->count() > 0 ? $contacts : '<br><p class="message-hint"><span>Your contatct list is empty</span></p>',
        ], 200);
    }

    /**
     * Update user's list item data
     *
     * @param Request $request
     * @return JSON response
     */
    public function updateContactItem(Request $request)
    {
        // Get user data
        $userCollection = User::where('id', $request['user_id'])->first();
        $contactItem = DevsFort::getContactItem($request['messenger_id'], $userCollection);

        // send the response
        return Response::json([
            'contactItem' => $contactItem,
        ], 200);
    }

    /**
     * Put a user in the favorites list
     *
     * @param Request $request
     * @return void
     */
    public function favorite(Request $request)
    {
        // check action [star/unstar]
        if (DevsFort::inFavorite($request['user_id'])) {
            // UnStar
            DevsFort::makeInFavorite($request['user_id'], 0);
            $status = 0;
        } else {
            // Star
            DevsFort::makeInFavorite($request['user_id'], 1);
            $status = 1;
        }

        // send the response
        return Response::json([
            'status' => @$status,
        ], 200);
    }

    /**
     * Get favorites list
     *
     * @param Request $request
     * @return void
     */
    public function getFavorites(Request $request)
    {
        $favoritesList = null;
        $favorites = Favorite::where('user_id', Auth::user()->id);
        foreach ($favorites->get() as $favorite) {
            // get user data
            $user = User::where('id', $favorite->favorite_id)->first();
            $favoritesList .= view('DevsFort::layouts.favorite', [
                'user' => $user,
            ]);
        }
        // send the response
        return Response::json([
            'favorites' => $favorites->count() > 0
                ? $favoritesList
                : '<p class="message-hint"><span>Your favorite list is empty</span></p>',
        ], 200);
    }

    /**
     * Search in messenger
     *
     * @param Request $request
     * @return void
     */
    public function search(Request $request)
    {
        $getRecords = null;
        $input = trim(filter_var($request['input'], FILTER_SANITIZE_STRING));
        $records = User::where('id','<>',\auth()->id())
            ->where('name', 'LIKE', "%{$input}%");
        foreach ($records->get() as $record) {
            $getRecords .= view('DevsFort::layouts.listItem', [
                'get' => 'search_item',
                'type' => 'user',
                'user' => $record,
            ])->render();
        }
        // send the response
        return Response::json([
            'records' => $records->count() > 0
                ? $getRecords
                : '<p class="message-hint"><span>Nothing to show.</span></p>',
            'addData' => 'html'
        ], 200);
    }

    /**
     * Get shared photos
     *
     * @param Request $request
     * @return void
     */
    public function sharedPhotos(Request $request)
    {
        $shared = DevsFort::getSharedPhotos($request['user_id']);
        $sharedPhotos = null;

        // shared with its template
        for ($i = 0; $i < count($shared); $i++) {
            $sharedPhotos .= view('DevsFort::layouts.listItem', [
                'get' => 'sharedPhoto',
                'image' => asset('storage/attachments/' . $shared[$i]),
            ])->render();
        }
        // send the response
        return Response::json([
            'shared' => count($shared) > 0 ? $sharedPhotos : '<p class="message-hint"><span>Nothing shared yet</span></p>',
        ], 200);
    }

    /**
     * Delete conversation
     *
     * @param Request $request
     * @return void
     */
    public function deleteConversation(Request $request)
    {
        // delete
        $delete = DevsFort::deleteConversation($request['id']);

        // send the response
        return Response::json([
            'deleted' => $delete ? 1 : 0,
        ], 200);
    }

    public function updateSettings(Request $request)
    {
        $msg = null;
        $error = $success = 0;

        // dark mode
        if ($request['dark_mode']) {
            $request['dark_mode'] == "dark"
                ? User::where('id', Auth::user()->id)->update(['dark_mode' => 1])  // Make Dark
                : User::where('id', Auth::user()->id)->update(['dark_mode' => 0]); // Make Light
        }

        // If messenger color selected
        if ($request['messengerColor']) {

            $messenger_color = explode('-', trim(filter_var($request['messengerColor'], FILTER_SANITIZE_STRING)));
            $messenger_color = DevsFort::getMessengerColors()[$messenger_color[1]];
            User::where('id', Auth::user()->id)
                ->update(['messenger_color' => $messenger_color]);
        }
        // if there is a [file]
        if ($request->hasFile('avatar')) {
            // allowed extensions
            $allowed_images = DevsFort::getAllowedImages();

            $file = $request->file('avatar');
            // if size less than 150MB
            if ($file->getSize() < 150000000) {
                if (in_array($file->getClientOriginalExtension(), $allowed_images)) {
                    // delete the older one
                    if (Auth::user()->avatar != config('devschat.user_avatar.default')) {
                        $path = storage_path('app/public/' . config('devschat.user_avatar.folder') . '/' . Auth::user()->avatar);
                        if (file_exists($path)) {
                            @unlink($path);
                        }
                    }
                    // upload
                    $avatar = Str::uuid() . "." . $file->getClientOriginalExtension();
                    $update = User::where('id', Auth::user()->id)->update(['avatar' => $avatar]);
                    $file->storeAs("public/" . config('devschat.user_avatar.folder'), $avatar);
                    $success = $update ? 1 : 0;
                } else {
                    $msg = "File extension not allowed!";
                    $error = 1;
                }
            } else {
                $msg = "File extension not allowed!";
                $error = 1;
            }
        }

        // send the response
        return Response::json([
            'status' => $success ? 1 : 0,
            'error' => $error ? 1 : 0,
            'message' => $error ? $msg : 0,
        ], 200);
    }

    /**
     * Set user's active status
     *
     * @param Request $request
     * @return void
     */
    public function setActiveStatus(Request $request)
    {
        $update = $request['status'] > 0
            ? User::where('id', $request['user_id'])->update(['active_status' => 1])
            : User::where('id', $request['user_id'])->update(['active_status' => 0]);
        
        // Broadcast status event
        if ($update) {
            $data = [
                'user_id' => $request['user_id'],
                'status' => $request['status'] > 0 ? 'online' : 'offline'
            ];
            event(new UserStatusEvent($data, $data['status']));
        }
        
        // send the response
        return Response::json([
            'status' => $update,
        ], 200);
    }

    /**
     * Create a new group
     *
     * @param Request $request
     * @return JSON response
     */
    public function createGroup(Request $request)
    {
        // Check if user can create groups using customizable service
        $chatService = app(config('devschat.customization.services.chat_service', 'DevsFort\Pigeon\Chat\Library\DevsFortChat'));
        
        if (!$chatService->canUserCreateGroups(auth()->id())) {
            return Response::json([
                'status' => '403',
                'error' => 1,
                'error_msg' => 'You are not allowed to create groups.',
            ], 403);
        }
        
        // Validate group using customizable validator
        $customValidator = config('devschat.customization.group_validator');
        if ($customValidator && class_exists($customValidator)) {
            $validator = new $customValidator();
            $validation = $validator->validate($request->all());
            if (!$validation['valid']) {
                return Response::json([
                    'status' => '422',
                    'error' => 1,
                    'error_msg' => implode(', ', $validation['errors']),
                ], 422);
            }
        } else {
            // Default validation
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'members' => 'required|array|min:1',
                'members.*' => 'exists:users,id'
            ]);
        }

        try {
            // Create group
            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'is_private' => $request->is_private ?? false
            ]);

            // Add creator as admin
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => Auth::user()->id,
                'role' => 'admin'
            ]);

            // Add other members
            foreach ($request->members as $memberId) {
                if ($memberId != Auth::user()->id) {
                    GroupMember::create([
                        'group_id' => $group->id,
                        'user_id' => $memberId,
                        'role' => 'member'
                    ]);
                }
            }

            return Response::json([
                'success' => true,
                'group' => $group->load('members'),
                'message' => 'Group created successfully!'
            ], 201);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to create group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get group info
     *
     * @param Request $request
     * @return JSON response
     */
    public function getGroupInfo(Request $request)
    {
        $group = Group::with(['members', 'creator'])
            ->where('id', $request->group_id)
            ->first();

        if (!$group) {
            return Response::json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is member
        if (!$group->isMember(Auth::user()->id)) {
            return Response::json([
                'success' => false,
                'message' => 'You are not a member of this group'
            ], 403);
        }

        return Response::json([
            'success' => true,
            'group' => $group
        ], 200);
    }

    /**
     * Add member to group
     *
     * @param Request $request
     * @return JSON response
     */
    public function addGroupMember(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $group = Group::find($request->group_id);
        
        // Check if user is admin
        if (!$group->isAdmin(Auth::user()->id)) {
            return Response::json([
                'success' => false,
                'message' => 'Only admins can add members'
            ], 403);
        }

        // Check if user is already a member
        if ($group->isMember($request->user_id)) {
            return Response::json([
                'success' => false,
                'message' => 'User is already a member'
            ], 400);
        }

        GroupMember::create([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
            'role' => 'member'
        ]);

        return Response::json([
            'success' => true,
            'message' => 'Member added successfully!'
        ], 200);
    }

    /**
     * Remove member from group
     *
     * @param Request $request
     * @return JSON response
     */
    public function removeGroupMember(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $group = Group::find($request->group_id);
        
        // Check if user is admin or removing themselves
        if (!$group->isAdmin(Auth::user()->id) && Auth::user()->id != $request->user_id) {
            return Response::json([
                'success' => false,
                'message' => 'Only admins can remove other members'
            ], 403);
        }

        GroupMember::where('group_id', $request->group_id)
            ->where('user_id', $request->user_id)
            ->delete();

        return Response::json([
            'success' => true,
            'message' => 'Member removed successfully!'
        ], 200);
    }

    /**
     * Show group chat interface
     *
     * @param int $id
     * @return view
     */
    public function groupChat($id)
    {
        $group = Group::with(['members', 'creator'])->findOrFail($id);
        
        // Check if user is member of the group
        if (!$group->isMember(Auth::user()->id)) {
            abort(403, 'You are not a member of this group.');
        }

        return view('DevsFort::pages.app', [
            'id' => 'group_' . $id,
            'route' => 'group',
            'group' => $group,
            'users' => $group->members->pluck('user'),
            'groups' => collect([$group]), // Show only this group
            'type' => 'group',
            'messengerColor' => Auth::user()->messenger_color,
            'dark_mode' => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
            'selectedGroup' => $group, // Pass the selected group
        ]);
    }
}
