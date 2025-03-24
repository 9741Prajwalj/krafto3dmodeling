<?php

namespace App\Http\Controllers;

use App\Models\BoxConfiguration;
use App\Models\BoxFace;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BoxConfigurationController extends Controller
{
    public function save(Request $request)
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'boxData' => 'required|json',
                'model' => 'required|file|mimetypes:model/gltf-binary,application/octet-stream|max:10240', // For .glb files
                'image' => 'required|file|mimes:png,jpg,jpeg|max:2048', // Max 2MB for images
            ]);

            // Debug incoming file details
            $modelFile = $request->file('model');
            Log::info('Model file details:', [
                'name' => $modelFile->getClientOriginalName(),
                'mime' => $modelFile->getClientMimeType(),
                'size' => $modelFile->getSize(),
                'extension' => $modelFile->getClientOriginalExtension(),
            ]);

            // Parse boxData
            $boxData = json_decode($request->input('boxData'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in boxData: ' . json_last_error_msg());
            }

            // Save the 3D model file
            $modelFileName = $modelFile->getClientOriginalName();
            $modelPath = $modelFile->storeAs('3dboximage', $modelFileName, 'public');

            // Save the screenshot
            $imageFile = $request->file('image');
            $imageFileName = $imageFile->getClientOriginalName();
            $imagePath = $imageFile->storeAs('3dboximage', $imageFileName, 'public');

            // Create box configuration
            $boxConfig = BoxConfiguration::create([
                'breadth' => $boxData['parameters']['breadth'],
                'length' => $boxData['parameters']['length'],
                'height' => $boxData['parameters']['height'],
                'thickness' => $boxData['parameters']['thickness'],
                'flute_freq' => $boxData['parameters']['fluteFreq'],
                'model_path' => $modelPath,
                'image_path' => $imagePath,
            ]);

            // Create faces and attachments
            foreach ($boxData['faces'] as $faceName => $faceData) {
                $boxFace = BoxFace::create([
                    'box_configuration_id' => $boxConfig->id,
                    'face_name' => $faceName,
                    'has_attachment' => $faceData['hasAttachment'] ?? false,
                ]);

                if ($boxFace->has_attachment && isset($faceData['attachment'])) {
                    $attachmentData = $faceData['attachment'];
                    Attachment::create([
                        'box_face_id' => $boxFace->id,
                        'type' => $attachmentData['type'] ?? null,
                        'content' => $attachmentData['content'] ?? null,
                        'font_style' => $attachmentData['fontStyle'] ?? null,
                        'font_size' => $attachmentData['fontSize'] ?? null,
                        'position_x' => $attachmentData['position']['x'] ?? 0,
                        'position_y' => $attachmentData['position']['y'] ?? 0,
                        'position_z' => $attachmentData['position']['z'] ?? 0,
                        'rotation_x' => $attachmentData['rotation']['x'] ?? 0,
                        'rotation_y' => $attachmentData['rotation']['y'] ?? 0,
                        'rotation_z' => $attachmentData['rotation']['z'] ?? 0,
                        'scale_x' => $attachmentData['scale']['x'] ?? 1,
                        'scale_y' => $attachmentData['scale']['y'] ?? 1,
                        'scale_z' => $attachmentData['scale']['z'] ?? 1,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Box configuration and 3D model saved successfully',
                'id' => $boxConfig->id,
                'model_path' => Storage::url($modelPath),
                'image_path' => Storage::url($imagePath),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->allFiles(),
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving box configuration:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to save configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}