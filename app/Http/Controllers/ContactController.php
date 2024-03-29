<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Contact;
use App\Models\ShareLog;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{

    public function contacts(Request $req, $ret = '')
    {
        $page = $req->page ?? 1;
        $query = $req->q  ?? $req->term ?? '';

        $login_id = login_id();
        $key = 'contacts' . $login_id;
        $contactsCache = cache()->get($key) ?? [];

        try {
            if (is_connected() == false) {
                // return response()->json(['error' => true, 'message' => "Please Connect to CRM"]);
            }
            $apiUrl = "contacts/?limit=100&query=$query&page=$page";
            $nextReq = false;
            $data = [];
            $total = 0;
            $contacts = ghl_api_call($apiUrl); // Make the API call
            if (is_string($contacts) && $contacts == 'No token') {
                $data[] = [
                    'id' => '-',
                    'text' => $contacts
                ];
            } else {
                if ($contacts) {
                    $total = $contacts->meta->total ?? 0;
                    if (property_exists($contacts, 'contacts') && count($contacts->contacts) > 0) {

                        foreach ($contacts->contacts as $contact) {
                            $name = $contact->contactName ?? "";
                            if (empty($name)) {
                                $name = "No Name - " . $contact->id;
                            }

                            $contactsCache[$contact->id] = $name;
                            $data[] = [
                                'id' => $contact->id,
                                'text' => $name,
                            ];
                        }

                        cache()->put($key, $contactsCache, 50);
                        if (property_exists($contacts, 'meta') && property_exists($contacts->meta, 'nextPageUrl') && property_exists($contacts->meta, 'nextPage') && !is_null($contacts->meta->nextPage) && !empty($contacts->meta->nextPageUrl)) {
                            $apiUrl = $contacts->meta->nextPageUrl;
                            $nextReq = true;
                        }
                    }
                }
            }
            if (count($data) == 0) {
                $nextReq = false;
            }
            return response()->json(['results' => $data, 'pagination' => ['more' => $nextReq], 'total_count' => $total]);
        } catch (Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching contacts ' . $th->getMessage()]);
        }
    }

    public function tags()
    {
        $tags = [];
        try {
            $response = ghl_api_call('tags', 'get');

            if ($response && property_exists($response, 'tags') && count($response->tags) > 0) {
                $tags = $response->tags;
            }
            return response()->json(['success' => true, 'message' => 'Contacts data', 'data' => $tags]);
        } catch (Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching contacts']);
        }
    }

    function convFinder($contactid, $location, $data)
    {
        $res = ghl_api_call('conversations/search?contactId=' . $contactid, 'GET', '', [], false, true);
        $actsend = 0;
        $conversationid = '';
        $type = strtolower($data['type']) ?? 'email';
        // $type = strtolower($type);
        //'WhatsApp'=>$smsTemplate,
        Log::info('Type: ' . $type);
        $types = ['email' => ['type' => 'html', 'value' => 'Email'], 'sms' => ['type' => 'message', 'value' => 'SMS']];
        $msg = '';
        $contactName = $data['contactName'] ?? '';
        $retry_status = 0;
        try {
            if ($res && property_exists($res, 'total')) {
                if ($res->total == 0) {
                    $gh_res = ghl_api_call('conversations/', 'POST', [
                        'form_data' => [
                            'locationId' => $location,
                            'contactId' => $contactid,
                        ]
                    ]);

                    if ($gh_res && property_exists($gh_res, 'conversation')) {
                        $conv = $gh_res->conversation->id;
                    } else {
                        return '';
                    }
                } else {
                    $conv = $res->conversations[0]->id;
                }

                $typeAct = $types[$type];
                $mt = [
                    'conversationId' => $conv,
                    'contactId' => $contactid,
                    'type' => $typeAct['value'],
                    'subject' => $data['subject'] ?? 'Recording', // convert into one time function
                    $typeAct['type'] => $data['body'],
                ];

                if (!empty($senderemail)) {
                    $mt['emailFrom'] = $senderemail;
                }
                $res = ghl_api_call('conversations/messages', 'POST', [
                    'form_data' => $mt
                ]);

                if ($res && property_exists($res, 'conversationId')) {
                    $actsend = 1;
                    $conversationid = $res->conversationId;
                }

                Log::info('Message Sent: ' . $res);

                $msg = $res->msg ?? $res->message ?? json_encode($res);
                $retry_status = $res->status ?? 0;
            }
        } catch (Throwable $th) {
            $msg = $th->getMessage();
        }

        $is_saved = $data['log_id'] ?? '';
        // $shareLog = false;
        try {
            $msg = substr($msg, 0, 255);
            $tags = (string) $data['all_tags'] ?? "";

            if ($tags && strlen($tags) > 255) {
                $tags = substr(($tags), 0, 255);
            }

            if ($is_saved && !empty($is_saved)) {
                $shareLog = ShareLog::find($is_saved);
                Log::info('Existing ShareLog: ' . $shareLog);
                $shareLog->conversation_id = (string) $conversationid;
                $shareLog->message = (string) $msg;
            } else {
                // $shareLog = ShareLog::where(['contact_id' => $contactid, 'recording_id' => $data['recording_id'], 'type' => $type])->first();
                $shareLog = new ShareLog();
                Log::info('new ShareLog: ' . $shareLog);
                $subject = $data['subject'] ?? "";
                // $shareLog = new ShareLog();
                $shareLog->user_id = $data['login_id']; //foreign user
                $shareLog->contact_id = (string) $contactid;
                $shareLog->contact_name = (string) $contactName;
                $shareLog->type = (string) $type;
                $shareLog->body = (string) $data['body'];
                $shareLog->recording_id = (string) $data['recording_id']; //foreign recording
                // if ($subject != '') {
                $shareLog->subject = (string) $subject;
                // }
                // if ($tags != '') {
                // $shareLog->all_tags = (string) $data['all_tags'] ?? "";
                $shareLog->all_tags = $tags;
                // }
                $shareLog->status = $actsend;
                $shareLog->conversation_id = (string) $conversationid;
                $shareLog->message = (string) $msg;
            }
            $shareLog->save();
            return [
                'retry_status' => $retry_status,
                'success' => true
            ];
        } catch (Throwable $th) {
            return false;
        }
    }

    public function retryLog(Request $request, $id)
    {
        if ($request->ajax()) {
            $log = ShareLog::with('user')->find($id);
            if ($log) {
                $data = [
                    'type' => $log->type,
                    'body' => $log->body,
                    'subject' => $log->subject,
                    'login_id' => $log->user_id,
                    'log_id' => (int) $log->id,
                    'recording_id' => $log->recording_id,
                    'all_tags' => $log->all_tags,
                ];
                $res = $this->convFinder($log->contact_id, $log->user->location_id, $data);
                Log::info('Retry: ' . $res);
                if ($res && $res['retry_status']) {
                    $log->status = 1;
                    $log->save();
                    return response()->json(['success' => true, 'message' => 'Resent successfully']); // Return JSON response for AJAX request
                } else {
                    return response()->json(['error' => 'Retry Failed'], 500); // Return JSON response for AJAX request
                }
            } else {
                return response()->json(['error' => 'Record not found'], 404); // Return JSON response for AJAX request
            }
        } else {
            dd('direct');
            $log = ShareLog::with('user')->find($id);
            if ($log) {
                $data = [
                    'type' => $log->type,
                    'body' => $log->body,
                    'subject' => $log->subject,
                    'login_id' => $log->user_id
                ];
                $res = $this->convFinder($log->contact_id, $log->user->location_id, $data);
                return redirect()->back()->with('success', 'Retry Processed');
            } else {
                return redirect()->back()->with('error', 'Log not found');
            }
        }
    }

    function processConv(Request $req)
    {
        // dd($req->all());
        $contacts = $req->contacts ?? '';
        $user = auth()->user();
        $contactCache = cache()->get('contacts' . $user->id) ?? [];
        $location = $user->location_id;
        $contacts = explode(',', $contacts) ?? [];
        $tags = $req->tags ?? '';
        $data = [
            'type' => $req->type ?? 'Email',
            'body' => $req->body,
            'subject' => $req->subject ?? 'Recording',
            'recording_id'  => $req->recording_id ?? '',
            'all_tags' => $tags,
            'login_id' => $user->id,
        ];

        $tags = explode(',', $tags) ?? [];

        // $sharewith = $req->share ?? 'contact';
        // if ($sharewith == 'tags') {
        //     $contacts = [];
        //     foreach ($tags as $t) {
        //         $req->merge(['q' => $t]);
        //         $data = self::contacts($req, 'tag');
        //     }
        // }

        foreach ($contacts as $contactid) {
            if (empty($contactid)) {
                continue;
            }
            $data['contactName'] = $contactCache[$contactid] ?? '';
            self::convFinder($contactid, $location, $data);
        }
        return response()->json(['success' => true, 'message' => 'Process Completed']);
    }
}
