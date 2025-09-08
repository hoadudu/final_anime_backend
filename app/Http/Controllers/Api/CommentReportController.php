<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentReportController extends Controller
{
    /**
     * Report a comment.
     */
    public function store(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->is_hidden) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|in:spam,inappropriate,harassment,other',
            'description' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if user already reported this comment
        $existingReport = CommentReport::where('user_id', Auth::id())
            ->where('comment_id', $comment->id)
            ->first();
            
        if ($existingReport) {
            return response()->json([
                'message' => 'You have already reported this comment'
            ], 409);
        }
        
        $report = CommentReport::create([
            'user_id' => Auth::id(),
            'comment_id' => $comment->id,
            'reason' => $request->reason,
            'description' => $request->description,
        ]);
        
        return response()->json([
            'message' => 'Comment reported successfully. Thank you for helping keep our community safe.',
            'report_id' => $report->id,
        ], 201);
    }
}
