<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Recording;
use App\Models\UploadedBlob;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Models\ShortURL;

class RecordingController extends Controller
{

    const TITLE = 'My Library';

    const VIEW = 'recording';

    const URL = 'recordings';

    const PATH = 'recordings';

    const TABLE = 'recordings';

    const ROUTE = 'recordings';

    protected $skip = ['id', 'created_at', 'updated_at'];

    protected $actions = [
        'edit' => true,
        'destroy' => true,
        'status' => true,
        'loginwith' => true,
    ];

    protected $validation = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required',
    ];

    public function __construct()
    {

        view()->share([
            'url' => url(self::URL),
            'title' => self::TITLE,
            'path' => self::PATH,
            'view' => self::VIEW,
        ]);
    }

    public function index()
    {
        $userId = login_id();
        $user = auth()->user();
        //$recordings = Recording::where('user_id', $userId)->latest()->paginate(getPaginate());
        $fields = getVariables();
        $tableFields = getTableColumns('share_logs', ['id', 'created_at', 'updated_at', 'deleted_at', 'user_id']);
        $tableFields = array_merge($tableFields, ['action' => 'Action']);
        // dd($tableFields);
        return view('recording.index', get_defined_vars());
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user has reached recording limit
        if ($user->plan->limit <= $user->recordings->count()) {
            return response()->json(['success' => false, 'message' => 'You have reached your maximum recording limit.']);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'video' => 'nullable',
            'poster' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        // Create a new recording
        $recording = new Recording();
        $recording->fill([
            'user_id' => $user->id,
            // 'title' => Carbon::now()->format('Y-m-d H:i:s'),
            'title' => $request->title ?? 'untitled',
            'description' => $request->description,
            // 'file' =>  $request->video,
            'file_url' => $request->videoUrl,
            // 'poster' => $request->poster,
            'poster_url' => $request->posterUrl,
            'status' => $request->status,
            'duration' => $request->duration,
            'size' => $request->size,
            'type' => $request->type,
            'make_it_private' => $request->privacy,
            'share' => $request->share,
            'embed' => $request->embed,
        ]);

        // Generate short URL
        try {
            $builder = new Builder();
            $shortURLObject = $builder->destinationUrl($request->videoUrl)->trackVisits()->trackIPAddress()->make();
            $recording->short_url = $shortURLObject->default_short_url;
        } catch (\Throwable $th) {
            report($th);
            $recording->short_url = null;
        }

        // Save the recording
        if ($recording->save()) {
            return response()->json(['success' => true, 'message' => 'Recording created successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Error occurred while creating recording']);
    }

    public function show($id)
    {
        try {
            $id = decrypt($id);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
        }

        $recording = Recording::findOrFail($id);
        return view('recording.show', get_defined_vars());
    }

    public function getData()
    {
        $user = Auth::user();
        $limit = $user->plan->limit ?? 0;

        if ($limit == 0) {
            $limit = 'Unlimited';
        }
        $recordings = Recording::where('user_id', $user->id)->latest()->paginate(getPaginate());

        return response()->json([
            'success' => true,
            'data' => $recordings,
            'user' => [
                'limit' => $limit,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            try {
                $id = decrypt($id);
            } catch (\Throwable $th) {
                // $id = $id;
                info($th->getMessage());
                return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
            }

            $recording = Recording::findOrFail($id);

            $user = Auth::user();
            if ($recording->user_id != $user->id) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to update this recording.']);
            }

            $recording->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
            return response()->json(['success' => true, 'message' => 'Recording updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error Occured while updating recording.']);
        }
    }

    public function destroy($id)
    {
        try {
            $id = decrypt($id);
        } catch (\Throwable $th) {
            // $id = $id;
            info($th->getMessage());
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
        }
        $recording = Recording::findOrFail($id);
        $recording->delete();
        return response()->json(['success' => true, 'message' => 'Recording deleted successfully.']);
    }

    public function showRecord($id)
    {
        $url = env('APP_URL') . '/' . config('short-url.prefix') . '/' . $id;
        $recording = Recording::where('short_url',  $url)->firstOrFail();

        if (!auth()->user() && $recording->status == 'draft') {
            return abort(404, 'You are not allowed to see this recording');
        }

        return view('recording.show', compact('recording'));
    }

    // public function uploadChunks(Request $request)
    // {
    //     // Retrieve the uploaded file chunk from the request
    //     $fileChunk = $request->file('videoChunk');
    //     $originalFilename = time() . '_' . $fileChunk->getClientOriginalName();
    //     $chunkIndex = $request->input('chunkIndex');

    //     // Define the directory to store the file chunks
    //     $directory = 'video_chunks/' . $originalFilename;

    //     // Ensure the directory exists
    //     Storage::makeDirectory($directory);

    //     // Store the chunk
    //     $chunkPath = $fileChunk->storeAs($directory, "{$chunkIndex}.part");

    //     // Check if this is the last chunk
    //     $lastChunkIndex = $request->input('lastChunkIndex');
    //     // dd($chunkPath);
    //     if ($chunkIndex == $lastChunkIndex) {
    //         // All chunks have been uploaded, concatenate them into a single file
    //         $filePath = 'videos/' . $originalFilename;
    //         $chunkFiles = Storage::files($directory);

    //         Log::info('Chunk files: ' . json_encode($chunkFiles));
    //         // Open the destination file
    //         $destination = fopen(storage_path('app/' . $filePath), 'wb');

    //         // Concatenate the chunk files
    //         foreach ($chunkFiles as $chunkFile) {
    //             Log::info('Chunk file: ' . $chunkFile);
    //             Log::info('Destination: ' . $destination);
    //             fwrite($destination, file_get_contents(storage_path('app/' . $chunkFile)));
    //         }

    //         // Close the destination file
    //         fclose($destination);

    //         // Delete the chunk directory
    //         Storage::deleteDirectory($directory);

    //         // Return success response
    //         return response()->json(['success' => true, 'file_path' => $filePath]);
    //     }

    //     // Return success response for the chunk
    //     return response()->json(['success' => true, 'chunk_path' => $chunkPath]);
    // }

    // public function chunksUploads($service, $request, $parentFolder)
    // {
    //     $file = $request->file('chunk');
    //     $fileName = $request->get('name');
    //     $chunkNumber = $request->get('chunkNumber');
    //     $totalChunks = $request->get('totalChunks');

    //     $filePath = storage_path('app/temp/' . $fileName . '_' . $chunkNumber);
    //     $file->move(storage_path('app/temp'), $filePath);

    //     if ($chunkNumber == $totalChunks) {
    //         $outputFilePath = storage_path('app' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $fileName);
    //         // Merge the chunks into a single file
    //         $upimage = $this->mergeChunks($fileName, $outputFilePath, $totalChunks);
    //         $this->saveToGDrive11($service, $fileName, $upimage, $parentFolder, $request->desc ?? '');
    //         // Clear temporary chunks
    //         $this->clearTempChunks($fileName, $totalChunks);
    //     }
    // }

    public function uploadChunks(Request $request)
    {
        try {
            $file = $request->file('videoChunk');
            if (!$file) {
                return response()->json(['success' => false, 'message' => 'No file uploaded.']);
                // throw new \Exception('No file uploaded.');
            }

            $fileName = 'blob'; // Unique file name for the blob
            $chunkNumber = $request->get('chunkIndex');
            $totalChunks = $request->get('lastChunkIndex');
            $randomFolder = $request->get('randomFolder');

            // Create a directory for storing chunk files if it doesn't exist
            $tempDirectory = storage_path('app/temp/' . $randomFolder);
            if (!file_exists($tempDirectory) && !mkdir($tempDirectory, 0777, true)) {
                return response()->json(['success' => false, 'message' => 'Failed to create temp directory.']);
                // throw new \Exception('Failed to create temp directory.');
            }

            // Move the uploaded chunk file to the temporary directory
            if (!$file->move($tempDirectory, $fileName . '_' . $chunkNumber)) {
                return response()->json(['success' => false, 'message' => 'Failed to move uploaded file to temp directory.']);
                // throw new \Exception('Failed to move uploaded file to temp directory.');
            }

            // Check if all chunks have been uploaded
            if ($chunkNumber == $totalChunks) {
                // Path for storing the complete blob file
                $outputFilePath = storage_path('app/temp/' . $randomFolder . '/' . $fileName);

                // Merge the chunks into a single file
                // $url =  $this->mergeBlobChunks($fileName, $outputFilePath, $totalChunks, $randomFolder);

                // Clear temporary chunk files
                $this->clearTempChunks($fileName, $totalChunks, $randomFolder);

                return response()->json(['success' => true, 'status' => "sent", 'message' => 'File uploaded successfully', 'data' => $url]);
            } else {
                return response()->json(['status' => "sending", 'message' => 'Chunk uploaded successfully']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function mergeBlobChunks($fileName, $outputFilePath, $totalChunks, $randomFolder)
    {
        $outputFile = fopen($outputFilePath, 'ab');

        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkData = file_get_contents(storage_path("app/temp/{$randomFolder}/{$fileName}_{$i}"));
            fwrite($outputFile, $chunkData);
        }

        fclose($outputFile);

        $fileName = time();
        // Move the merged file to the public/uploads/recording directory
        $newFilePath = public_path("uploads/recording/" . $fileName);
        rename($outputFilePath, $newFilePath);

        // Clear temporary chunks
        // $this->clearTempBlobChunks($fileName, $totalChunks, $randomFolder);
        return  env('APP_URL') . '/uploads/recording/' . $fileName;
    }

    // Clear temporary chunks
    private function clearTempChunks($fileName, $totalChunks, $randomFolder)
    {
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkFilePath = storage_path("app/temp/{$randomFolder}/{$fileName}_{$i}");
            if (file_exists($chunkFilePath)) {
                unlink($chunkFilePath); // Delete the chunk file
            }
        }
        // Remove the temporary folder
        rmdir(storage_path("app/temp/{$randomFolder}"));
    }

    public function uploadPoster(Request $request)
    {
        $file = $request->file('poster');
        $fileName = time() . '_' . $file->getClientOriginalName();

        $folder = public_path('uploads/poster');

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $file->move($folder, $fileName);
        return response()->json([
            'success' => true,
            'message' => 'Poster uploaded successfully',
            'data' => env('APP_URL') . '/uploads/poster/' . $fileName
        ]);
    }

    public function publish(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $id = decrypt($id);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
        }

        $recording = Recording::findOrFail($id);
        $recording->status = $request->status;
        $recording->save();
        return response()->json(['success' => true, 'message' => 'Recording published successfully!',  'data' => $recording]);
    }
}
